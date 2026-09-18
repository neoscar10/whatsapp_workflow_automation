<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\User;
use App\Models\Contact\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ContactApiExportImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_contacts_index_returns_last_added_contact_first(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);

        $c1 = Contact::create([
            'company_id' => $company->id,
            'name' => 'First Old Contact',
            'phone' => '+11111111111',
            'normalized_phone' => '11111111111',
            'created_at' => now()->subDays(5),
        ]);

        $c2 = Contact::create([
            'company_id' => $company->id,
            'name' => 'Second Newest Contact',
            'phone' => '+12222222222',
            'normalized_phone' => '12222222222',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/contacts');

        $response->assertStatus(200);
        $data = $response->json('data.data');
        $this->assertEquals($c2->id, $data[0]['id']);
        $this->assertEquals('Second Newest Contact', $data[0]['name']);
    }

    public function test_api_export_supports_xlsx_and_csv_formats(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);

        Contact::create([
            'company_id' => $company->id,
            'name' => 'Export User',
            'phone' => '+13333333333',
            'normalized_phone' => '13333333333',
        ]);

        // XLSX Export
        $responseXlsx = $this->actingAs($user, 'sanctum')
            ->get('/api/v1/contacts/export?format=xlsx');

        $responseXlsx->assertStatus(200);
        $responseXlsx->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // CSV Export
        $responseCsv = $this->actingAs($user, 'sanctum')
            ->get('/api/v1/contacts/export?format=csv');

        $responseCsv->assertStatus(200);
        $responseCsv->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_api_import_template_supports_xlsx_and_csv_formats(): void
    {
        $user = User::factory()->create();

        // XLSX Template
        $responseXlsx = $this->actingAs($user, 'sanctum')
            ->get('/api/v1/contacts/import/template?format=xlsx');

        $responseXlsx->assertStatus(200);
        $responseXlsx->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // CSV Template
        $responseCsv = $this->actingAs($user, 'sanctum')
            ->get('/api/v1/contacts/import/template?format=csv');

        $responseCsv->assertStatus(200);
        $responseCsv->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_api_import_endpoint_accepts_excel_file(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);

        // Create temporary Excel file
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'phone');
        $sheet->setCellValue('B1', 'name');
        $sheet->setCellValue('C1', 'tags');

        $sheet->setCellValue('A2', '+14445556666');
        $sheet->setCellValue('B2', 'Api Excel Contact');
        $sheet->setCellValue('C2', 'VIP,Lead');

        $tempPath = sys_get_temp_dir() . '/api_import_' . uniqid() . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        $file = new UploadedFile($tempPath, 'contacts.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/contacts/import', [
                'file' => $file,
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.created', 1);

        $this->assertDatabaseHas('contacts', [
            'company_id' => $company->id,
            'name' => 'Api Excel Contact',
            'phone' => '14445556666',
        ]);

        @unlink($tempPath);
    }
}
