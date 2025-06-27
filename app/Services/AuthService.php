<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function register(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->sendEmailVerificationNotification();

        return $user;
    }

    public function login(array $credentials): array
    {
        if (!Auth::attempt($credentials)) {
            return [
                'success' => false,
                'message' => __('auth.failed'),
                'status' => 401
            ];
        }

        $user = User::where('email', $credentials['email'])->firstOrFail();

        if (!$user->hasVerifiedEmail()) {
            return [
                'success' => false,
                'message' => __('auth.email_not_verified'),
                'status' => 403
            ];
        }

        $token = $user->createToken(
            'auth_token',
            ['*'],
            now()->addMinutes(config('sanctum.expiration'))
        )->plainTextToken;

        return [
            'success' => true,
            'token' => $token,
            'user' => $user,
        ];
    }

    public function verifyEmail(int $userId, string $hash): array
    {
        $user = User::findOrFail($userId);

        if (!hash_equals($hash, sha1($user->getEmailForVerification()))) {
            return [
                'message' => __('auth.invalid_verification_link'),
                'status' => 403
            ];
        }

        if ($user->hasVerifiedEmail()) {
            return [
                'message' => __('auth.email_already_verified'),
                'status' => 200
            ];
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return [
            'message' => __('auth.email_verified_success'),
            'status' => 200
        ];
    }
}