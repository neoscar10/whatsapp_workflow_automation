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
     * Field aliases for flexible header mapping.
     */
    protected array $fieldAliases = [
        'phone' => [
            'phone', 'phone_number', 'phonenumber', 'phone_no', 'phoneno', 
            'mobile', 'mobile_number', 'mobilenumber', 'mobile_no', 
            'contact', 'contact_number', 'contactnumber', 'contact_no', 
            'telephone', 'tel', 'whatsapp', 'whatsapp_number', 'number', 'msisdn'
        ],
        'name' => [
            'name', 'full_name', 'fullname', 'contact_name', 'first_name', 
            'firstname', 'display_name', 'customer_name', 'user_name', 'username'
        ],
        'tags' => ['tags', 'tag', 'labels', 'label'],
        'groups' => ['groups', 'group', 'categories', 'category', 'list', 'lists'],
        'notes' => ['notes', 'note', 'description', 'remark', 'remarks', 'comment', 'comments'],
        'has_opted_in' => ['has_opted_in', 'opt_in', 'opted_in', 'consent', 'subscribed'],
    ];

    /**
     * Import contacts from a CSV file.
     */
    public function importFromCsv(User $actor, UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            throw new \Exception("Failed to open CSV file for reading.");
        }

        $stats = [
            'total_rows' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        $firstRow = fgetcsv($handle);
        if (!$firstRow) {
            fclose($handle);
            throw new \Exception("CSV file is empty.");
        }

        // Clean UTF-8 BOM and whitespace from the first row
        $firstRow = array_map([$this, 'sanitizeCell'], $firstRow);

        // Check if the first row is actually data (no header row present)
        $isFirstRowData = $this->looksLikePhoneNumber($firstRow[0] ?? '');
        
        $headerMap = [];
        $hasHeader = !$isFirstRowData;

        if ($hasHeader) {
            $headerMap = $this->buildHeaderMap($firstRow);
        }

        // If phone column wasn't mapped by header name, try fallback inspection
        if (!isset($headerMap['phone'])) {
            if ($isFirstRowData) {
                // Row 1 is data, column 0 is phone
                $headerMap['phone'] = 0;
                if (isset($firstRow[1])) {
                    $headerMap['name'] = 1;
                }
            } else {
                // Header exists but didn't match standard aliases; try to find phone column from first data row
                $secondRow = fgetcsv($handle);
                if ($secondRow !== false) {
                    $secondRow = array_map([$this, 'sanitizeCell'], $secondRow);
                    foreach ($secondRow as $colIdx => $val) {
                        if ($this->looksLikePhoneNumber($val)) {
                            $headerMap['phone'] = $colIdx;
                            break;
                        }
                    }
                    
                    // If still no phone column found, fallback to column 0
                    if (!isset($headerMap['phone'])) {
                        $headerMap['phone'] = 0;
                    }

                    // Process this second row as rowNum 2
                    $this->processRow($secondRow, 2, $headerMap, $actor, $stats);
                }
            }
        }

        // If row 1 is data, process row 1 as rowNum 1
        if ($isFirstRowData) {
            $this->processRow($firstRow, 1, $headerMap, $actor, $stats);
        }

        $rowNum = $hasHeader ? 1 : ($isFirstRowData ? 1 : 2);

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            $row = array_map([$this, 'sanitizeCell'], $row);
            $this->processRow($row, $rowNum, $headerMap, $actor, $stats);
        }

        fclose($handle);
        return $stats;
    }

    protected function processRow(array $row, int $rowNum, array $map, User $actor, array &$stats): void
    {
        $stats['total_rows']++;

        try {
            $data = $this->mapRowToData($row, $map);
            
            if (empty($data['phone'])) {
                $stats['skipped']++;
                $stats['errors'][] = "Row {$rowNum}: Missing phone number.";
                return;
            }

            if (!PhoneNumberNormalizer::isValid($data['phone'])) {
                $stats['failed']++;
                $stats['errors'][] = "Row {$rowNum}: Invalid phone number format [{$data['phone']}].";
                return;
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

    protected function mapRowToData(array $row, array $map): array
    {
        $data = [];
        $fields = ['phone', 'name', 'tags', 'groups', 'notes', 'has_opted_in'];
        
        foreach ($fields as $field) {
            if (isset($map[$field]) && isset($row[$map[$field]])) {
                $val = trim($row[$map[$field]]);
                if ($val !== '') {
                    $data[$field] = $val;
                }
            }
        }
        
        return $data;
    }

    protected function buildHeaderMap(array $headerRow): array
    {
        $map = [];

        foreach ($headerRow as $index => $rawCell) {
            $clean = strtolower($this->sanitizeCell((string) $rawCell));
            $normalized = str_replace([' ', '-'], '_', $clean);
            $compact = str_replace([' ', '-', '_'], '', $clean);

            foreach ($this->fieldAliases as $field => $aliases) {
                if (!isset($map[$field])) {
                    foreach ($aliases as $alias) {
                        $aliasCompact = str_replace([' ', '-', '_'], '', $alias);
                        if ($clean === $alias || $normalized === $alias || $compact === $aliasCompact) {
                            $map[$field] = $index;
                            break;
                        }
                    }
                }
            }
        }

        return $map;
    }

    protected function sanitizeCell(mixed $cell): string
    {
        if ($cell === null) {
            return '';
        }
        $cell = (string) $cell;
        // Strip UTF-8 BOM and non-printable characters
        $cell = preg_replace('/^[\x{EF}\x{BB}\x{BF}\x{FEFF}]/u', '', $cell);
        $cell = preg_replace('/^\xEF\xBB\xBF/', '', $cell);
        return trim($cell);
    }

    protected function looksLikePhoneNumber(string $val): bool
    {
        $cleaned = preg_replace('/[^\d+]/', '', $val);
        return strlen($cleaned) >= 7 && preg_match('/^\+?[0-9]{7,16}$/', $cleaned) === 1;
    }
}
