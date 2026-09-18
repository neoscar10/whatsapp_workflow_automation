<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use App\Models\Contact\Contact;
use App\Services\Contact\ContactExportService;
use App\Services\Contact\ContactImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ContactExcelAndCsvExportImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_service_generates_valid_csv_and_xlsx(): void
    {
        $company = Company::factory()->create();
        Contact::create([
            'company_id' => $company->id,
            'name' => 'Alice Exporter',
            'phone' => '+15551234567',
            'normalized_phone' => '15551234567',
            'status' => 'active',
        ]);

        $exportService = app(ContactExportService::class);

        // CSV Test
        $csvCallback = $exportService->exportContacts($company->id, 'csv');
        ob_start();
        $csvCallback();
        $csvOutput = ob_get_clean();

        $this->assertStringContainsString('Alice Exporter', $csvOutput);
        $this->assertStringContainsString('+15551234567', $csvOutput);

        // XLSX Test
        $xlsxCallback = $exportService->exportContacts($company->id, 'xlsx');
        ob_start();
        $xlsxCallback();
        $xlsxOutput = ob_get_clean();

        $this->assertNotEmpty($xlsxOutput);
        $this->assertStringStartsWith('PK', $xlsxOutput); // Zip signature for XLSX
    }

    public function test_import_service_imports_contacts_from_excel_file(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);

        // Create temporary Excel file
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'phone');
        $sheet->setCellValue('B1', 'name');
        $sheet->setCellValue('C1', 'tags');

        $sheet->setCellValue('A2', '+19998887777');
        $sheet->setCellValue('B2', 'Excel Imported User');
        $sheet->setCellValue('C2', 'ExcelTag1,ExcelTag2');

        $tempPath = sys_get_temp_dir() . '/test_contacts_' . uniqid() . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        $file = new UploadedFile($tempPath, 'test_contacts.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $importService = app(ContactImportService::class);
        $stats = $importService->importFromFile($user, $file);

        $this->assertEquals(1, $stats['created']);
        $this->assertDatabaseHas('contacts', [
            'company_id' => $company->id,
            'name' => 'Excel Imported User',
            'phone' => '19998887777',
        ]);

        @unlink($tempPath);
    }

    public function test_export_formats_numeric_phone_names_explicitly_as_string(): void
    {
        $company = Company::factory()->create();
        Contact::create([
            'company_id' => $company->id,
            'name' => '919211999874',
            'phone' => '+919211999874',
            'normalized_phone' => '919211999874',
            'status' => 'active',
        ]);
        Contact::create([
            'company_id' => $company->id,
            'name' => '+917703954128',
            'phone' => '+917703954128',
            'normalized_phone' => '917703954128',
            'status' => 'active',
        ]);

        $exportService = app(ContactExportService::class);

        // CSV Test
        $csvCallback = $exportService->exportContacts($company->id, 'csv');
        ob_start();
        $csvCallback();
        $csvOutput = ob_get_clean();

        $this->assertStringContainsString('+919211999874', $csvOutput);
        $this->assertStringContainsString('+917703954128', $csvOutput);
        $this->assertStringNotContainsString('++', $csvOutput);

        // XLSX Test
        $xlsxCallback = $exportService->exportContacts($company->id, 'xlsx');
        ob_start();
        $xlsxCallback();
        $xlsxOutput = ob_get_clean();

        $tempPath = sys_get_temp_dir() . '/test_export_' . uniqid() . '.xlsx';
        file_put_contents($tempPath, $xlsxOutput);

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tempPath);
        $sheet = $spreadsheet->getActiveSheet();

        // Row 2 & Row 3 data rows
        $nameCell1 = $sheet->getCell('A2');
        $nameCell2 = $sheet->getCell('A3');
        $this->assertNotEquals('++', substr($nameCell1->getValue(), 0, 2));
        $this->assertNotEquals('++', substr($nameCell2->getValue(), 0, 2));
        $this->assertEquals(\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING, $nameCell1->getDataType());
        $this->assertEquals(\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING, $nameCell2->getDataType());

        @unlink($tempPath);
    }
}
