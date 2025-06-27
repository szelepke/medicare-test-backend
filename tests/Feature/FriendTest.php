<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FriendTest extends TestCase
{
    use RefreshDatabase;

    /**
     * User can add friend.
     */
    public function test_user_can_add_friend()
    {
        $user1 = User::factory()->create(['email_verified_at' => now()]);
        $user2 = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user1)
            ->postJson("/api/v1/friends/{$user2->id}");

        $response->assertOk()
            ->assertJson(['message' => 'Ismerősnek jelölés sikeres!']);
                
        $this->assertTrue($user1->isFriendWith($user2));
        $this->assertTrue($user2->isFriendWith($user1));
    }
} 