<?php

namespace App\Livewire\Web\Auth;

use App\Services\Company\CompanyRegistrationService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.auth')]
#[Title('Register - WhatsApp Cloud Panel')]
class RegisterCompanyPage extends Component
{
    public string $company_name = '';
    public string $email = '';
    public string $password = '';
    public bool $agree_to_terms = false;

    protected function rules()
    {
        return [
            'company_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email'), Rule::unique('companies', 'primary_email')],
            'password' => ['required', 'string', 'min:8', 'max:255'],
            'agree_to_terms' => ['accepted'],
        ];
    }

    public function register(CompanyRegistrationService $registrationService)
    {
        $validatedData = $this->validate();

        $result = $registrationService->register($validatedData);
        $user = $result['user'];

        // Auto-login the user
        auth()->login($user);

        session()->flash('success', 'Company created successfully! Enjoy your 14-day free trial.');

        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.web.auth.register-company-page');
    }
}
