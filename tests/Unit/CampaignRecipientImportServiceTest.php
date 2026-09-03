<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\Contact\Contact;
use App\Models\Campaign\Campaign;
use App\Services\Campaign\CampaignRecipientImportService;
use App\Services\Campaign\CampaignAudienceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

class CampaignRecipientImportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CampaignRecipientImportService $importService;
    protected CampaignAudienceService $audienceService;
    protected User $user;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->importService = app(CampaignRecipientImportService::class);
        $this->audienceService = app(CampaignAudienceService::class);

        $this->company = Company::create([
            'name' => 'Import Test Co',
            'slug' => 'import-test-co',
            'primary_email' => 'import@test.com',
        ]);

        $this->user = User::factory()->create(['company_id' => $this->company->id]);
    }

    public function test_csv_recipient_import_persists_audience_type_and_prevents_overwrite()
    {
        // Create 5 unrelated contacts in DB
        for ($i = 1; $i <= 5; $i++) {
            Contact::create([
                'company_id' => $this->company->id,
                'name' => "Existing Contact {$i}",
                'phone' => "+234809999000{$i}",
                'normalized_phone' => "+234809999000{$i}",
                'status' => 'active',
            ]);
        }

        // Create draft campaign
        $campaign = Campaign::create([
            'company_id' => $this->company->id,
            'created_by_user_id' => $this->user->id,
            'name' => 'CSV Test Campaign',
            'status' => 'draft',
            'type' => 'template',
            'audience_type' => 'selected_contacts',
        ]);

        // Create temporary CSV with 2 specific recipients
        $csvContent = "phone,name\n+2348011112222,Recipient One\n+2348033334444,Recipient Two";
        $tmpFile = tempnam(sys_get_temp_dir(), 'csv_import_test');
        file_put_contents($tmpFile, $csvContent);

        // 1. Import CSV
        $summary = $this->importService->importFromCsv($this->user, $campaign, $tmpFile);
        @unlink($tmpFile);

        $this->assertEquals(2, $summary['success']);
        $this->assertEquals('imported', $campaign->refresh()->audience_type);
        $this->assertEquals(2, $campaign->recipients()->count());

        // 2. Simulate wizard saveStep2 or step navigation syncing audience
        $this->audienceService->syncAudience($this->user, $campaign, [
            'type' => 'imported',
        ]);

        // 3. Verify recipients are preserved and NOT replaced by all 10 DB contacts
        $this->assertEquals(2, $campaign->refresh()->recipient_count);
        $this->assertEquals(2, $campaign->recipients()->count());
    }
}
