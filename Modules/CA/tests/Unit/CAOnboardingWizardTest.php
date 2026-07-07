<?php

namespace Modules\CA\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\CA\Livewire\ClientOnboardingWizard;
use App\Models\User;
use App\Models\Company;
use Modules\CA\Models\CABusinessType;

class CAOnboardingWizardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'Modules\CA\Database\Seeders\CADatabaseSeeder']);
    }

    public function test_wizard_step_progression()
    {
        $company = Company::create(['name' => 'Test Firm', 'slug' => 'test-firm', 'primary_email' => 'test@firm.com']);
        $user = User::factory()->create(['company_id' => $company->id]);
        $businessType = CABusinessType::first();

        Livewire::actingAs($user)
            ->test(ClientOnboardingWizard::class)
            ->set('client_name', 'Tech Innovators')
            ->set('phone', '9999999999')
            ->call('nextStep')
            ->assertSet('step', 2)
            ->set('business_type_id', $businessType->id)
            ->call('nextStep')
            ->assertSet('step', 3);
    }
}
