<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserFriendTest extends TestCase
{
    use RefreshDatabase;

    /**
     * User friends relationship works.
     */
    public function test_user_friends_relationship_works()
    {
        $user1 = User::factory()->create(['email_verified_at' => now()]);
        $user2 = User::factory()->create(['email_verified_at' => now()]);

        $user1->friends()->attach($user2->id);
        $user2->friends()->attach($user1->id);

        $this->assertTrue($user1->friends->contains($user2));
        $this->assertTrue($user2->friends->contains($user1));
    }
}