<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Services\Contact\ContactImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

class ContactImportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ContactImportService $service;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ContactImportService::class);
        $company = Company::factory()->create();
        $this->user = User::factory()->create(['company_id' => $company->id]);
    }

    public function test_imports_csv_with_standard_headers()
    {
        $csvContent = "phone,name\n+2348012345678,John Doe\n+2348087654321,Jane Doe";
        $file = UploadedFile::fake()->createWithContent('contacts.csv', $csvContent);

        $result = $this->service->importFromCsv($this->user, $file);

        $this->assertEquals(2, $result['total_rows']);
        $this->assertEquals(2, $result['created']);
        $this->assertEquals(0, $result['failed']);
        $this->assertEquals(0, $result['skipped']);
    }

    public function test_imports_csv_with_utf8_bom_and_aliases()
    {
        $bom = "\xEF\xBB\xBF";
        $csvContent = $bom . "Phone Number,Full Name\n+2348012345678,John Bom\n+2348087654321,Jane Bom";
        $file = UploadedFile::fake()->createWithContent('contacts_bom.csv', $csvContent);

        $result = $this->service->importFromCsv($this->user, $file);

        $this->assertEquals(2, $result['total_rows']);
        $this->assertEquals(2, $result['created']);
        $this->assertEquals(0, $result['skipped']);
    }

    public function test_imports_csv_with_mobile_header_alias()
    {
        $csvContent = "Mobile,Name\n+2348011111111,Mobile User";
        $file = UploadedFile::fake()->createWithContent('mobile.csv', $csvContent);

        $result = $this->service->importFromCsv($this->user, $file);

        $this->assertEquals(1, $result['total_rows']);
        $this->assertEquals(1, $result['created']);
    }

    public function test_imports_csv_without_headers()
    {
        $csvContent = "+2348022222222,Headerless User\n+2348033333333,Headerless User 2";
        $file = UploadedFile::fake()->createWithContent('no_header.csv', $csvContent);

        $result = $this->service->importFromCsv($this->user, $file);

        $this->assertEquals(2, $result['total_rows']);
        $this->assertEquals(2, $result['created']);
    }
}
