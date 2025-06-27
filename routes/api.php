<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\FriendController;
use App\Http\Controllers\Api\V1\MessageController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:5,1'])->prefix('v1')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verify'])->name('verification.verify');
    Route::post('/email/resend', [AuthController::class, 'resend'])->name('verification.send');
});

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/users', [UserController::class, 'index']);
    Route::get('/me', [UserController::class, 'me']);

    Route::prefix('friends')->group(function () {
        Route::get('/', [FriendController::class, 'listFriends']);
        Route::post('/{user}', [FriendController::class, 'addFriend']);
        Route::delete('/{user}', [FriendController::class, 'removeFriend']);
        Route::get('/check/{user}', [FriendController::class, 'checkFriendship']);
        Route::get('/mutual/{user}', [FriendController::class, 'mutualFriends']);
        Route::get('/suggestions', [FriendController::class, 'suggestFriends']);
    });

    Route::prefix('messages')->group(function () {
        Route::post('/', [MessageController::class, 'sendMessage']);
        Route::get('/conversations', [MessageController::class, 'getConversations']);
        Route::get('/conversation/{user}', [MessageController::class, 'getConversation']);
        Route::patch('/conversation/{user}/read', [MessageController::class, 'markAsRead']);
        Route::get('/unread-count', [MessageController::class, 'getUnreadCount']);
        Route::delete('/{messageId}', [MessageController::class, 'deleteMessage']);
        Route::get('/search', [MessageController::class, 'searchMessages']);
    });
    
    Route::middleware(['throttle:30,1'])->group(function () {
        Route::post('/messages', [MessageController::class, 'sendMessage']);
    });
});

Route::fallback(function () {
    return response()->json([
        'error' => 'API endpoint not found',
        'message' => 'The requested API endpoint does not exist'
    ], 404);
});