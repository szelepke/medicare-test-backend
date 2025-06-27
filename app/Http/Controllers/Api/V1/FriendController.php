<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Friend\AddFriendRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\FriendService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FriendController extends Controller
{
    public function __construct(
        private FriendService $friendService
    ) {}

    public function addFriend(AddFriendRequest $request, User $user): JsonResponse
    {
        $result = $this->friendService->addFriend($request->user(), $user);

        return response()->json([
            'message' => $result['message']
        ], $result['status']);
    }

    public function removeFriend(Request $request, User $user): JsonResponse
    {
        $result = $this->friendService->removeFriend($request->user(), $user);

        return response()->json([
            'message' => $result['message']
        ], $result['status']);
    }

    public function listFriends(Request $request): JsonResponse
    {
        $friends = $this->friendService->getFriends($request->user());

        return response()->json([
            'friends' => UserResource::collection($friends),
            'count' => $friends->count()
        ]);
    }

    public function checkFriendship(Request $request, User $user): JsonResponse
    {
        $isFriend = $this->friendService->areFriends($request->user(), $user);

        return response()->json([
            'are_friends' => $isFriend
        ]);
    }

    public function mutualFriends(Request $request, User $user): JsonResponse
    {
        $mutualFriends = $this->friendService->getMutualFriends($request->user(), $user);

        return response()->json([
            'mutual_friends' => UserResource::collection($mutualFriends),
            'count' => $mutualFriends->count()
        ]);
    }

    public function suggestFriends(Request $request): JsonResponse
    {
        $suggestions = $this->friendService->getSuggestedFriends($request->user());

        return response()->json([
            'suggestions' => UserResource::collection($suggestions),
            'count' => $suggestions->count()
        ]);
    }
}