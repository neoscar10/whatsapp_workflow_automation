<?php

namespace App\Services\Contact;

use App\Models\User;
use App\Models\Contact\Contact;
use App\Support\PhoneNumberNormalizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContactImportService
{
    public function __construct(
        protected ContactService $contactService,
        protected ContactTagService $tagService,
        protected ContactGroupService $groupService
    ) {}

    /**
     * Import contacts from a CSV file.
     */
    public function importFromCsv(User $actor, UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle);
        
        $stats = [
            'total_rows' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        // Mapping helper (case-insensitive)
        $headerMap = array_flip(array_map('strtolower', $header));

        $rowNum = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            $stats['total_rows']++;

            try {
                $data = $this->mapRowToData($row, $headerMap);
                
                if (empty($data['phone'])) {
                    $stats['skipped']++;
                    $stats['errors'][] = "Row {$rowNum}: Missing phone number.";
                    continue;
                }

                if (!PhoneNumberNormalizer::isValid($data['phone'])) {
                    $stats['failed']++;
                    $stats['errors'][] = "Row {$rowNum}: Invalid phone number format [{$data['phone']}].";
                    continue;
                }

                $normalizedPhone = PhoneNumberNormalizer::normalize($data['phone']);

                DB::transaction(function () use ($actor, $data, $normalizedPhone, &$stats) {
                    $contact = Contact::where('company_id', $actor->company_id)
                        ->where('normalized_phone', $normalizedPhone)
                        ->first();

                    $isNew = !$contact;

                    $contactData = [
                        'company_id' => $actor->company_id,
                        'name' => $data['name'] ?? ($contact->name ?? null),
                        'phone' => $data['phone'],
                        'normalized_phone' => $normalizedPhone,
                        'notes' => $data['notes'] ?? ($contact->notes ?? null),
                        'source' => 'import',
                        'has_opted_in' => isset($data['has_opted_in']) ? filter_var($data['has_opted_in'], FILTER_VALIDATE_BOOLEAN) : ($contact->has_opted_in ?? false),
                    ];

                    if ($isNew) {
                        $contactData['created_by_user_id'] = $actor->id;
                        $contact = Contact::create($contactData);
                        $stats['created']++;
                    } else {
                        $contactData['updated_by_user_id'] = $actor->id;
                        $contact->update($contactData);
                        $stats['updated']++;
                    }

                    // Handle Tags
                    if (!empty($data['tags'])) {
                        $tagNames = explode(',', $data['tags']);
                        $tagIds = $this->tagService->ensureByNames($actor, $tagNames);
                        $contact->tags()->syncWithoutDetaching($tagIds);
                    }

                    // Handle Groups
                    if (!empty($data['groups'])) {
                        $groupNames = explode(',', $data['groups']);
                        $groupIds = $this->groupService->ensureByNames($actor, $groupNames);
                        $contact->groups()->syncWithoutDetaching($groupIds);
                    }
                });

            } catch (\Exception $e) {
                $stats['failed']++;
                $stats['errors'][] = "Row {$rowNum}: " . $e->getMessage();
                Log::error("Contact Import Row Error", ['row' => $rowNum, 'error' => $e->getMessage()]);
            }
        }

        fclose($handle);
        return $stats;
    }

    protected function mapRowToData(array $row, array $map): array
    {
        $data = [];
        $fields = ['phone', 'name', 'tags', 'groups', 'notes', 'has_opted_in'];
        
        foreach ($fields as $field) {
            if (isset($map[$field])) {
                $data[$field] = $row[$map[$field]] ?? null;
            }
        }
        
        return $data;
    }
}
