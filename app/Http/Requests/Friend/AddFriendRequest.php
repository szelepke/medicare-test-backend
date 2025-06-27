<?php

namespace App\Http\Requests\Friend;

use Illuminate\Foundation\Http\FormRequest;

class AddFriendRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }

    public function prepareForValidation(): void
    {
    }
}