<?php

namespace App\Services\Contact;

use App\Models\Contact\Contact;
use Illuminate\Support\Collection;

class ContactExportService
{
    /**
     * Export contacts to CSV format.
     * Returns a generator to handle large datasets.
     */
    public function exportToCsv(int $companyId, array $filters = [])
    {
        $query = Contact::forCompany($companyId)
            ->with(['tags', 'groups'])
            ->orderBy('name');

        // Apply same filters as listForCompany if needed
        // For simplicity, we just export all company contacts for now
        
        $headers = [
            'Name', 'Phone', 'Normalized Phone', 'Status', 'Source', 
            'Opted In', 'Do Not Message', 'Tags', 'Groups', 'Last Interaction', 'Created At'
        ];

        $callback = function() use ($query, $headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);

            $query->chunk(100, function ($contacts) use ($file) {
                foreach ($contacts as $contact) {
                    fputcsv($file, [
                        $contact->name,
                        $contact->phone,
                        $contact->normalized_phone,
                        $contact->status,
                        $contact->source,
                        $contact->has_opted_in ? 'Yes' : 'No',
                        $contact->do_not_message ? 'Yes' : 'No',
                        $contact->tags->pluck('name')->implode(', '),
                        $contact->groups->pluck('name')->implode(', '),
                        $contact->last_interaction_at?->toDateTimeString(),
                        $contact->created_at->toDateTimeString(),
                    ]);
                }
            });

            fclose($file);
        };

        return $callback;
    }

    public function getImportTemplate()
    {
        $headers = ['phone', 'name', 'tags', 'groups', 'notes', 'has_opted_in'];
        $sampleData = [
            ['+1234567890', 'John Doe', 'Customer,Vip', 'Newsletter', 'Sample note', 'true'],
            ['+9876543210', 'Jane Smith', 'Lead', 'Promotions', 'Another sample note', 'false']
        ];

        return function() use ($headers, $sampleData) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);

            foreach ($sampleData as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };
    }
}
