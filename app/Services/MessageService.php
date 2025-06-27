<?php

namespace App\Services;

use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MessageService
{
    public function sendMessage(User $sender, array $data): array
    {
        $receiver = User::findOrFail($data['receiver_id']);

        if (!$this->areFriends($sender, $receiver)) {
            return [
                'success' => false,
                'message' => __('messages.can_only_message_friends'),
                'status' => 403
            ];
        }

        if ($this->isBlocked($sender, $receiver)) {
            return [
                'success' => false,
                'message' => __('messages.user_blocked'),
                'status' => 403
            ];
        }

        try {
            $message = Message::create([
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'message' => trim($data['message']),
                'is_read' => false,
            ]);

            $this->clearUnreadCountCache($receiver->id);

            return [
                'success' => true,
                'message' => $message
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => __('messages.send_failed'),
                'status' => 500
            ];
        }
    }

    public function getConversation(User $user, User $otherUser, array $params = []): Collection
    {
        if (!$this->areFriends($user, $otherUser)) {
            return collect();
        }

        $query = Message::where(function($q) use ($user, $otherUser) {
                $q->where('sender_id', $user->id)->where('receiver_id', $otherUser->id);
            })
            ->orWhere(function($q) use ($user, $otherUser) {
                $q->where('sender_id', $otherUser->id)->where('receiver_id', $user->id);
            })
            ->with(['sender:id,name', 'receiver:id,name']);

        if (isset($params['before'])) {
            $query->where('created_at', '<', $params['before']);
        }

        if (isset($params['after'])) {
            $query->where('created_at', '>', $params['after']);
        }

        $limit = $params['limit'] ?? 50;
        
        return $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();
    }

    public function getConversations(User $user, int $limit = 20): Collection
    {
        $conversations = DB::table('messages as m1')
            ->select([
                'other_user_id' => DB::raw('CASE WHEN m1.sender_id = ? THEN m1.receiver_id ELSE m1.sender_id END'),
                'last_message' => 'm1.message',
                'last_message_at' => 'm1.created_at',
                'is_sender' => DB::raw('m1.sender_id = ?'),
                'unread_count' => DB::raw('(SELECT COUNT(*) FROM messages WHERE sender_id = CASE WHEN m1.sender_id = ? THEN m1.receiver_id ELSE m1.sender_id END AND receiver_id = ? AND is_read = false)')
            ])
            ->whereRaw('m1.id = (
                SELECT MAX(id) FROM messages m2 
                WHERE (m2.sender_id = ? AND m2.receiver_id = CASE WHEN m1.sender_id = ? THEN m1.receiver_id ELSE m1.sender_id END)
                   OR (m2.receiver_id = ? AND m2.sender_id = CASE WHEN m1.sender_id = ? THEN m1.receiver_id ELSE m1.sender_id END)
            )')
            ->where(function($q) use ($user) {
                $q->where('m1.sender_id', $user->id)
                  ->orWhere('m1.receiver_id', $user->id);
            })
            ->setBindings([
                $user->id, $user->id, $user->id, $user->id,
                $user->id, $user->id, $user->id, $user->id
            ])
            ->orderBy('last_message_at', 'desc')
            ->limit($limit)
            ->get();

        $userIds = $conversations->pluck('other_user_id');
        $users = User::whereIn('id', $userIds)->get()->keyBy('id');

        return $conversations->map(function($conversation) use ($users) {
            $conversation->other_user = $users->get($conversation->other_user_id);
            return $conversation;
        });
    }

    public function markConversationAsRead(User $user, User $otherUser): int
    {
        $count = Message::where('sender_id', $otherUser->id)
            ->where('receiver_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        $this->clearUnreadCountCache($user->id);

        return $count;
    }

    public function deleteMessage(User $user, int $messageId): array
    {
        $message = Message::find($messageId);

        if (!$message) {
            return [
                'message' => __('messages.not_found'),
                'status' => 404
            ];
        }

        if ($message->sender_id !== $user->id) {
            return [
                'message' => __('messages.cannot_delete'),
                'status' => 403
            ];
        }

        try {
            $message->delete();
            return [
                'message' => __('messages.deleted_successfully'),
                'status' => 200
            ];
        } catch (\Exception $e) {
            return [
                'message' => __('messages.delete_failed'),
                'status' => 500
            ];
        }
    }

    public function getUnreadCount(User $user): int
    {
        return Cache::remember(
            "unread_messages_count_{$user->id}",
            300,
            function () use ($user) {
                return Message::where('receiver_id', $user->id)
                    ->where('is_read', false)
                    ->count();
            }
        );
    }

    public function searchMessages(User $user, string $query, ?int $userId = null, int $limit = 20): Collection
    {
        $searchQuery = Message::where(function($q) use ($user, $userId) {
                if ($userId) {
                    $q->where(function($subQ) use ($user, $userId) {
                        $subQ->where('sender_id', $user->id)->where('receiver_id', $userId);
                    })->orWhere(function($subQ) use ($user, $userId) {
                        $subQ->where('sender_id', $userId)->where('receiver_id', $user->id);
                    });
                } else {
                    $q->where('sender_id', $user->id)->orWhere('receiver_id', $user->id);
                }
            })
            ->where('message', 'LIKE', "%{$query}%")
            ->with(['sender:id,name', 'receiver:id,name']);

        return $searchQuery->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    private function areFriends(User $user1, User $user2): bool
    {
        return $user1->friends()->where('users.id', $user2->id)->exists();
    }

    private function isBlocked(User $sender, User $receiver): bool
    {
        return false;
    }

    private function clearUnreadCountCache(int $userId): void
    {
        Cache::forget("unread_messages_count_{$userId}");
    }
}