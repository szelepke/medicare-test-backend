<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Message\SendMessageRequest;
use App\Http\Requests\Message\GetMessagesRequest;
use App\Http\Resources\MessageResource;
use App\Http\Resources\ConversationResource;
use App\Models\User;
use App\Services\MessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(
        private MessageService $messageService
    ) {}

    public function sendMessage(SendMessageRequest $request): JsonResponse
    {
        $result = $this->messageService->sendMessage(
            $request->user(),
            $request->validated()
        );

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], $result['status']);
        }

        return response()->json([
            'message' => __('messages.sent_successfully'),
            'data' => new MessageResource($result['message'])
        ], 201);
    }

    public function getConversation(GetMessagesRequest $request, User $user): JsonResponse
    {
        $messages = $this->messageService->getConversation(
            $request->user(),
            $user,
            $request->validated()
        );

        return response()->json([
            'messages' => MessageResource::collection($messages),
            'count' => $messages->count()
        ]);
    }

    public function getConversations(Request $request): JsonResponse
    {
        $conversations = $this->messageService->getConversations(
            $request->user(),
            $request->get('limit', 20)
        );

        return response()->json([
            'conversations' => ConversationResource::collection($conversations),
            'count' => $conversations->count()
        ]);
    }

    public function markAsRead(Request $request, User $user): JsonResponse
    {
        $count = $this->messageService->markConversationAsRead($request->user(), $user);

        return response()->json([
            'message' => __('messages.marked_as_read'),
            'marked_count' => $count
        ]);
    }

    public function deleteMessage(Request $request, int $messageId): JsonResponse
    {
        $result = $this->messageService->deleteMessage($request->user(), $messageId);

        return response()->json([
            'message' => $result['message']
        ], $result['status']);
    }

    public function getUnreadCount(Request $request): JsonResponse
    {
        $count = $this->messageService->getUnreadCount($request->user());

        return response()->json([
            'unread_count' => $count
        ]);
    }

    public function searchMessages(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2|max:100',
            'user_id' => 'nullable|exists:users,id',
            'limit' => 'nullable|integer|min:1|max:50'
        ]);

        $messages = $this->messageService->searchMessages(
            $request->user(),
            $request->input('query'),
            $request->input('user_id'),
            $request->get('limit', 20)
        );

        return response()->json([
            'messages' => MessageResource::collection($messages),
            'count' => $messages->count()
        ]);
    }
}