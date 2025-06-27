<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class FriendService
{
    public function addFriend(User $authUser, User $targetUser): array
    {
        if ($authUser->id === $targetUser->id) {
            return [
                'message' => __('friends.cannot_add_yourself'),
                'status' => 422
            ];
        }

        if (!$authUser->hasVerifiedEmail() || !$targetUser->hasVerifiedEmail()) {
            return [
                'message' => __('friends.both_must_be_verified'),
                'status' => 403
            ];
        }

        if ($this->areFriends($authUser, $targetUser)) {
            return [
                'message' => __('friends.already_friends'),
                'status' => 409
            ];
        }

        try {
            DB::transaction(function () use ($authUser, $targetUser) {
                DB::table('friends')->insert([
                    [
                        'user_id' => $authUser->id, 
                        'friend_id' => $targetUser->id, 
                        'created_at' => now(), 
                        'updated_at' => now()
                    ],
                    [
                        'user_id' => $targetUser->id, 
                        'friend_id' => $authUser->id, 
                        'created_at' => now(), 
                        'updated_at' => now()
                    ],
                ]);
            });

            return [
                'message' => __('friends.added_successfully'),
                'status' => 200
            ];
        } catch (\Exception $e) {
            return [
                'message' => __('friends.add_failed'),
                'status' => 500
            ];
        }
    }

    public function removeFriend(User $authUser, User $targetUser): array
    {
        if (!$this->areFriends($authUser, $targetUser)) {
            return [
                'message' => __('friends.not_friends'),
                'status' => 404
            ];
        }

        try {
            DB::transaction(function () use ($authUser, $targetUser) {
                DB::table('friends')
                    ->where(function ($query) use ($authUser, $targetUser) {
                        $query->where('user_id', $authUser->id)
                              ->where('friend_id', $targetUser->id);
                    })
                    ->orWhere(function ($query) use ($authUser, $targetUser) {
                        $query->where('user_id', $targetUser->id)
                              ->where('friend_id', $authUser->id);
                    })
                    ->delete();
            });

            return [
                'message' => __('friends.removed_successfully'),
                'status' => 200
            ];
        } catch (\Exception $e) {
            return [
                'message' => __('friends.remove_failed'),
                'status' => 500
            ];
        }
    }

    public function getFriends(User $user): Collection
    {
        return $user->friends()
            ->whereNotNull('email_verified_at')
            ->orderBy('name')
            ->get();
    }

    public function areFriends(User $user1, User $user2): bool
    {
        return DB::table('friends')
            ->where('user_id', $user1->id)
            ->where('friend_id', $user2->id)
            ->exists();
    }

    public function getMutualFriends(User $user1, User $user2): Collection
    {
        $user1Friends = $user1->friends()->pluck('id');
        
        return $user2->friends()
            ->whereIn('id', $user1Friends)
            ->whereNotNull('email_verified_at')
            ->orderBy('name')
            ->get();
    }

    public function getSuggestedFriends(User $user, int $limit = 10): Collection
    {
        $friendIds = $user->friends()->pluck('id');
        
        return User::whereNotIn('id', $friendIds)
            ->where('id', '!=', $user->id)
            ->whereNotNull('email_verified_at')
            ->whereHas('friends', function ($query) use ($friendIds) {
                $query->whereIn('friend_id', $friendIds);
            })
            ->withCount(['friends' => function ($query) use ($friendIds) {
                $query->whereIn('friend_id', $friendIds);
            }])
            ->orderByDesc('friends_count')
            ->limit($limit)
            ->get();
    }

    public function getFriendCount(User $user): int
    {
        return $user->friends()
            ->whereNotNull('email_verified_at')
            ->count();
    }
}