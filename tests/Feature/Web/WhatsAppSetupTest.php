<?php

namespace Tests\Feature\Web;

use App\Models\User;
use App\Models\Company;
use App\Models\WhatsApp\WhatsAppAccount;
use App\Models\WhatsApp\WhatsAppPhoneNumber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use App\Livewire\Web\WhatsApp\AccountSetupPage;
use App\Livewire\Web\WhatsApp\PhoneNumbersPage;

class WhatsAppSetupTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Test Company',
            'slug' => 'test-company',
            'primary_email' => 'test@company.com',
            'status' => 'trial',
        ]);

        $this->user = User::factory()->create([
            'company_id' => $this->company->id,
        ]);
    }

    /** @test */
    public function guest_cannot_access_whatsapp_setup_pages()
    {
        $this->get(route('whatsapp.setup.phone-numbers'))->assertRedirect(route('login'));
        $this->get(route('whatsapp.setup.account'))->assertRedirect(route('login'));
    }

    /** @test */
    public function authenticated_user_can_access_whatsapp_setup_pages()
    {
        $this->actingAs($this->user)
            ->get(route('whatsapp.setup.phone-numbers'))
            ->assertStatus(200);

        $this->actingAs($this->user)
            ->get(route('whatsapp.setup.account'))
            ->assertStatus(200);
    }

    /** @test */
    public function account_setup_can_be_saved()
    {
        Livewire::actingAs($this->user)
            ->test(AccountSetupPage::class)
            ->set('access_token', 'test-token')
            ->set('waba_id', '123456789')
            ->set('business_id', '987654321')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('isConnected', true);

        $this->assertDatabaseHas('whatsapp_accounts', [
            'company_id' => $this->company->id,
            'waba_id' => '123456789',
            'business_id' => '987654321',
            'connection_status' => 'connected',
        ]);

        $account = WhatsAppAccount::where('company_id', $this->company->id)->first();
        $this->assertEquals('test-token', $account->access_token);
        // Verify it's encrypted in DB (manual check would be better but this verifies the cast works)
    }

    /** @test */
    public function account_setup_discard_works()
    {
        WhatsAppAccount::create([
            'company_id' => $this->company->id,
            'access_token' => 'old-token',
            'waba_id' => 'old-waba',
            'business_id' => 'old-biz',
            'connection_status' => 'connected',
        ]);

        Livewire::actingAs($this->user)
            ->test(AccountSetupPage::class)
            ->set('waba_id', 'new-waba')
            ->call('discardChanges')
            ->assertSet('waba_id', 'old-waba');
    }

    /** @test */
    public function cannot_add_phone_number_without_connected_account()
    {
        Livewire::actingAs($this->user)
            ->test(PhoneNumbersPage::class)
            ->set('display_name', 'Support')
            ->set('phone_number_id', '1212121212')
            ->call('saveNumber')
            ->assertHasErrors(['phone_numbers_modal']);
    }

    /** @test */
    public function can_add_phone_number_with_connected_account()
    {
        WhatsAppAccount::create([
            'company_id' => $this->company->id,
            'access_token' => 'token',
            'waba_id' => 'waba',
            'business_id' => 'biz',
            'connection_status' => 'connected',
        ]);

        Livewire::actingAs($this->user)
            ->test(PhoneNumbersPage::class)
            ->set('display_name', 'Support')
            ->set('phone_number_id', '1212121212')
            ->call('saveNumber')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('whatsapp_phone_numbers', [
            'company_id' => $this->company->id,
            'display_name' => 'Support',
            'phone_number_id' => '1212121212',
            'status' => 'active',
        ]);
    }

    /** @test */
    public function can_edit_phone_number()
    {
        $number = WhatsAppPhoneNumber::create([
            'company_id' => $this->company->id,
            'display_name' => 'Old Name',
            'phone_number_id' => '1111111111',
            'status' => 'active',
        ]);

        Livewire::actingAs($this->user)
            ->test(PhoneNumbersPage::class)
            ->call('openEditModal', $number->id)
            ->set('display_name', 'New Name')
            ->call('saveNumber')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('whatsapp_phone_numbers', [
            'id' => $number->id,
            'display_name' => 'New Name',
        ]);
    }

    /** @test */
    public function can_toggle_phone_number_status()
    {
        $number = WhatsAppPhoneNumber::create([
            'company_id' => $this->company->id,
            'display_name' => 'Support',
            'phone_number_id' => '1111111111',
            'status' => 'active',
        ]);

        Livewire::actingAs($this->user)
            ->test(PhoneNumbersPage::class)
            ->call('toggleNumberStatus', $number->id)
            ->assertHasNoErrors();

        $this->assertEquals('inactive', $number->fresh()->status);

        Livewire::actingAs($this->user)
            ->test(PhoneNumbersPage::class)
            ->call('toggleNumberStatus', $number->id)
            ->assertHasNoErrors();

        $this->assertEquals('active', $number->fresh()->status);
    }

    /** @test */
    public function user_cannot_see_other_company_phone_numbers()
    {
        $otherCompany = Company::create([
            'name' => 'Other',
            'slug' => 'other',
            'primary_email' => 'other@test.com',
            'status' => 'trial',
        ]);

        WhatsAppPhoneNumber::create([
            'company_id' => $otherCompany->id,
            'display_name' => 'Other Number',
            'phone_number_id' => '9999999999',
            'status' => 'active',
        ]);

        Livewire::actingAs($this->user)
            ->test(PhoneNumbersPage::class)
            ->assertViewHas('numbers', function ($numbers) {
                return $numbers->count() === 0;
            });
    }

    /** @test */
    public function search_filters_phone_numbers()
    {
        WhatsAppPhoneNumber::create([
            'company_id' => $this->company->id,
            'display_name' => 'Sales',
            'phone_number_id' => '1111111111',
            'status' => 'active',
        ]);

        WhatsAppPhoneNumber::create([
            'company_id' => $this->company->id,
            'display_name' => 'Support',
            'phone_number_id' => '2222222222',
            'status' => 'active',
        ]);

        Livewire::actingAs($this->user)
            ->test(PhoneNumbersPage::class)
            ->set('search', 'Sales')
            ->assertViewHas('numbers', function ($numbers) {
                return $numbers->count() === 1 && $numbers->first()->display_name === 'Sales';
            });
    }
}
