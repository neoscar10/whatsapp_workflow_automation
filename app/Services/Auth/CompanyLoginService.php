<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class CompanyLoginService
{
    /**
     * Authenticate a company user.
     *
     * @param array $credentials
     * @param bool $remember
     * @return bool
     * @throws ValidationException
     */
    public function login(array $credentials, bool $remember = false): bool
    {
        $email = strtolower(trim($credentials['email']));
        $password = $credentials['password'];

        if (!Auth::attempt(['email' => $email, 'password' => $password], $remember)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        // Verify company status if needed
        $user = Auth::user();
        if ($user->company && !in_array($user->company->status, ['trial', 'active'])) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => ['Your company account is currently ' . $user->company->status . '. Please contact support.'],
            ]);
        }

        if (request()->hasSession()) {
            request()->session()->regenerate();
        }

        return true;
    }
}
