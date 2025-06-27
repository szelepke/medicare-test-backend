<?php

namespace App\Http\Requests\Message;

use Illuminate\Foundation\Http\FormRequest;

class GetMessagesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'limit' => 'nullable|integer|min:1|max:100',
            'before' => 'nullable|date',
            'after' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'limit.integer' => __('validation.integer', ['attribute' => 'limit']),
            'limit.min' => __('validation.min.numeric', ['attribute' => 'limit', 'min' => 1]),
            'limit.max' => __('validation.max.numeric', ['attribute' => 'limit', 'max' => 100]),
            'before.date' => __('validation.date', ['attribute' => 'before']),
            'after.date' => __('validation.date', ['attribute' => 'after']),
        ];
    }
}