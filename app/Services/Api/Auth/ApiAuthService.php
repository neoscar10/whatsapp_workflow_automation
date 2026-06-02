<?php

namespace App\Services\Api\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ApiAuthService
{
    /**
     * Authenticate an API user and return a token.
     *
     * @param array $credentials
     * @param string $deviceName
     * @return array
     * @throws ValidationException
     */
    public function login(array $credentials, string $deviceName = 'mobile_app'): array
    {
        $user = User::where('email', strtolower(trim($credentials['email'])))->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        // Verify company status
        if ($user->company && !in_array($user->company->status, ['active', 'demo'])) {
            throw ValidationException::withMessages([
                'email' => ['Your company account is currently ' . $user->company->status . '. Please contact support.'],
            ]);
        }

        $token = $user->createToken($deviceName)->plainTextToken;

        return [
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ];
    }

    /**
     * Revoke the user's current token.
     *
     * @param User $user
     * @return bool
     */
    public function logout(User $user): bool
    {
        return $user->currentAccessToken()->delete();
    }

    /**
     * Revoke all of the user's tokens.
     *
     * @param User $user
     * @return bool
     */
    public function logoutAll(User $user): bool
    {
        return $user->tokens()->delete();
    }
}
