<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'other_user' => [
                'id' => $this->other_user->id,
                'name' => $this->other_user->name,
            ],
            'last_message' => $this->last_message,
            'last_message_at' => $this->last_message_at,
            'unread_count' => $this->unread_count,
            'is_sender' => $this->is_sender,
        ];
    }
}