<?php

namespace App\Livewire\Web\Auth;

use App\Services\Auth\CompanyLoginService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.auth')]
#[Title('Login - WhatsApp Cloud Panel')]
class LoginPage extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;
    public bool $showPassword = false;

    public function togglePassword()
    {
        $this->showPassword = !$this->showPassword;
    }

    public function mount()
    {
        if (Auth::check()) {
            if (Auth::user()->role === 'super_admin') {
                return redirect()->route('superadmin.dashboard');
            }
            return redirect()->intended(route('chats.index'));
        }
    }

    protected function rules()
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'max:255'],
            'remember' => ['boolean'],
        ];
    }

    public function login(CompanyLoginService $loginService)
    {
        $this->validate();

        try {
            $loginService->login([
                'email' => $this->email,
                'password' => $this->password,
            ], $this->remember);

            if (Auth::user()->role === 'super_admin') {
                return redirect()->route('superadmin.dashboard');
            }

            return redirect()->intended(route('chats.index'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->addError('auth', $e->getMessage());
            // Also add to email field for standard display if desired
            $this->addError('email', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.web.auth.login-page');
    }
}
