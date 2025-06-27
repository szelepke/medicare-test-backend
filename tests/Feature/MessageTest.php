<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * User can send message to friend.
     */
    public function test_user_can_send_message_to_friend()
    {
        $sender = User::factory()->create(['email_verified_at' => now()]);
        $receiver = User::factory()->create(['email_verified_at' => now()]);
        
        $sender->friends()->attach($receiver->id);
        $receiver->friends()->attach($sender->id);
        $messageText = 'Szia, ez egy teszt üzenet!';

        $response = $this->actingAs($sender)
            ->postJson('/api/v1/messages', [
                'receiver_id' => $receiver->id,
                'message' => $messageText
            ]);

        $response->assertCreated()
            ->assertJson(['message' => 'Üzenet elküldve!']);
                
        $this->assertDatabaseHas('messages', [
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'message' => $messageText
        ]);
    }
} 