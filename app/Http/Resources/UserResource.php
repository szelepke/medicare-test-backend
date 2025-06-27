<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'friends' => $this->whenLoaded('friends', function () {
                return $this->friends->map(function ($friend) {
                    return [
                        'id' => $friend->id,
                        'name' => $friend->name,
                        'email' => $friend->email,
                    ];
                });
            }),
        ];
    }
}