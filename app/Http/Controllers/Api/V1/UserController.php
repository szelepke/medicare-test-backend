<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()
            ->whereNotNull('email_verified_at')
            ->orderBy('name', 'asc');

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%$search%");
        }

        $users = $query->paginate(10);

        return UserResource::collection($users)
            ->additional(['meta' => ['message' => __('users.users_list')]]);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load('friends');

        return response()->json(new UserResource($user));
    }
}

