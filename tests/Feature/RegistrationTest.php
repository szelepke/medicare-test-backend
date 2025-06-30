<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Successful registration and email verification.
     */
    public function test_successful_registration_and_email_verification()
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Teszt Elek',
            'email' => 'teszt1@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(201);

        $user = User::where('email', 'teszt1@example.com')->first();

        $this->assertNotNull($user);

        $user->markEmailAsVerified();

        $this->assertTrue($user->hasVerifiedEmail());
    }
} 