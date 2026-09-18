<?php

namespace App\Services\Contact;

use App\Models\Contact\Contact;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ContactExportService
{
    /**
     * Export contacts in specified format ('xlsx' or 'csv').
     */
    public function exportContacts(int $companyId, string $format = 'xlsx', array $filters = [])
    {
        return strtolower($format) === 'xlsx'
            ? $this->exportToXlsx($companyId, $filters)
            : $this->exportToCsv($companyId, $filters);
    }

    /**
     * Export contacts to Excel (.xlsx) format.
     */
    public function exportToXlsx(int $companyId, array $filters = [])
    {
        $query = Contact::forCompany($companyId)
            ->with(['tags', 'groups'])
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        $headers = [
            'Name', 'Phone', 'Normalized Phone', 'Status', 'Source', 
            'Opted In', 'Do Not Message', 'Tags', 'Groups', 'Last Interaction', 'Created At'
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Contacts');

        // Style Header Row
        $colIndex = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($colIndex . '1', $header);
            $colIndex++;
        }

        $headerRange = 'A1:K1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF4F46E5');
        $sheet->getStyle($headerRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(28);

        $rowIdx = 2;
        $query->chunk(200, function ($contacts) use ($sheet, &$rowIdx) {
            foreach ($contacts as $contact) {
                $rawName = trim((string)($contact->name ?? ''));
                $cleanNamePhone = \App\Support\PhoneNumberNormalizer::clean($rawName);

                if ($cleanNamePhone !== '' && (preg_match('/^\+?[0-9]{7,15}$/', $rawName) || preg_match('/^[0-9]{7,15}$/', $rawName))) {
                    $formattedName = '+' . $cleanNamePhone;
                } else {
                    $formattedName = $rawName;
                }

                $cleanPhone = \App\Support\PhoneNumberNormalizer::clean($contact->phone ?? '');
                $cleanNorm = \App\Support\PhoneNumberNormalizer::normalize($cleanPhone);

                $formattedPhone = preg_match('/^[0-9]+$/', $cleanPhone) ? '+' . $cleanPhone : $cleanPhone;
                $formattedNorm = '+' . $cleanNorm;

                // Explicitly set Name, Phone, and Normalized Phone as TYPE_STRING so Excel preserves exact text formatting
                $sheet->setCellValueExplicit('A' . $rowIdx, $formattedName, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('B' . $rowIdx, $formattedPhone, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('C' . $rowIdx, $formattedNorm, DataType::TYPE_STRING);
                $sheet->setCellValue('D' . $rowIdx, ucfirst($contact->status ?? 'active'));
                $sheet->setCellValue('E' . $rowIdx, ucfirst(str_replace('_', ' ', $contact->source ?? 'manual')));
                $sheet->setCellValue('F' . $rowIdx, $contact->has_opted_in ? 'Yes' : 'No');
                $sheet->setCellValue('G' . $rowIdx, $contact->do_not_message ? 'Yes' : 'No');
                $sheet->setCellValue('H' . $rowIdx, $contact->tags->pluck('name')->implode(', '));
                $sheet->setCellValue('I' . $rowIdx, $contact->groups->pluck('name')->implode(', '));
                $sheet->setCellValue('J' . $rowIdx, $contact->last_interaction_at?->toDateTimeString() ?? 'Never');
                $sheet->setCellValue('K' . $rowIdx, $contact->created_at->toDateTimeString());

                $rowIdx++;
            }
        });

        // Auto-fit column widths
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return function() use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        };
    }

    /**
     * Export contacts to CSV format with UTF-8 BOM.
     */
    public function exportToCsv(int $companyId, array $filters = [])
    {
        $query = Contact::forCompany($companyId)
            ->with(['tags', 'groups'])
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        $headers = [
            'Name', 'Phone', 'Normalized Phone', 'Status', 'Source', 
            'Opted In', 'Do Not Message', 'Tags', 'Groups', 'Last Interaction', 'Created At'
        ];

        return function() use ($query, $headers) {
            $file = fopen('php://output', 'w');
            // Write UTF-8 BOM for Excel compatibility
            fwrite($file, "\xEF\xBB\xBF");
            fputcsv($file, $headers);

            $query->chunk(200, function ($contacts) use ($file) {
                foreach ($contacts as $contact) {
                    $rawName = trim((string)($contact->name ?? ''));
                    $cleanNamePhone = \App\Support\PhoneNumberNormalizer::clean($rawName);

                    if ($cleanNamePhone !== '' && (preg_match('/^\+?[0-9]{7,15}$/', $rawName) || preg_match('/^[0-9]{7,15}$/', $rawName))) {
                        $formattedName = '+' . $cleanNamePhone;
                    } else {
                        $formattedName = $rawName;
                    }

                    $cleanPhone = \App\Support\PhoneNumberNormalizer::clean($contact->phone ?? '');
                    $cleanNorm = \App\Support\PhoneNumberNormalizer::normalize($cleanPhone);
                    
                    $formattedPhone = preg_match('/^[0-9]+$/', $cleanPhone) ? '+' . $cleanPhone : $cleanPhone;
                    $formattedNorm = '+' . $cleanNorm;

                    fputcsv($file, [
                        $formattedName,
                        $formattedPhone,
                        $formattedNorm,
                        $contact->status,
                        $contact->source,
                        $contact->has_opted_in ? 'Yes' : 'No',
                        $contact->do_not_message ? 'Yes' : 'No',
                        $contact->tags->pluck('name')->implode(', '),
                        $contact->groups->pluck('name')->implode(', '),
                        $contact->last_interaction_at?->toDateTimeString() ?? 'Never',
                        $contact->created_at->toDateTimeString(),
                    ]);
                }
            });

            fclose($file);
        };
    }

    /**
     * Get contact import sample template in specified format ('xlsx' or 'csv').
     */
    public function getImportTemplate(string $format = 'xlsx')
    {
        return strtolower($format) === 'xlsx'
            ? $this->getImportTemplateXlsx()
            : $this->getImportTemplateCsv();
    }

    /**
     * Get contact import sample template as Excel (.xlsx).
     */
    public function getImportTemplateXlsx()
    {
        $headers = ['phone', 'name', 'tags', 'groups', 'notes', 'has_opted_in'];
        $sampleData = [
            ['+12345678901', 'John Doe', 'Customer,VIP', 'Newsletter', 'Sample note for customer', 'true'],
            ['+98765432100', 'Jane Smith', 'Lead', 'Promotions', 'Another sample note', 'false']
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template');

        // Style Header
        $colIndex = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($colIndex . '1', $header);
            $colIndex++;
        }

        $headerRange = 'A1:F1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF2563EB'); // Royal Blue
        $sheet->getStyle($headerRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(26);

        // Fill Sample Data
        $rowIdx = 2;
        foreach ($sampleData as $row) {
            $sheet->setCellValueExplicit('A' . $rowIdx, $row[0], DataType::TYPE_STRING); // Phone as text string
            $sheet->setCellValue('B' . $rowIdx, $row[1]);
            $sheet->setCellValue('C' . $rowIdx, $row[2]);
            $sheet->setCellValue('D' . $rowIdx, $row[3]);
            $sheet->setCellValue('E' . $rowIdx, $row[4]);
            $sheet->setCellValue('F' . $rowIdx, $row[5]);
            $rowIdx++;
        }

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return function() use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        };
    }

    /**
     * Get contact import sample template as CSV (.csv).
     */
    public function getImportTemplateCsv()
    {
        $headers = ['phone', 'name', 'tags', 'groups', 'notes', 'has_opted_in'];
        $sampleData = [
            ['+12345678901', 'John Doe', 'Customer,VIP', 'Newsletter', 'Sample note for customer', 'true'],
            ['+98765432100', 'Jane Smith', 'Lead', 'Promotions', 'Another sample note', 'false']
        ];

        return function() use ($headers, $sampleData) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");
            fputcsv($file, $headers);

            foreach ($sampleData as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };
    }
}
