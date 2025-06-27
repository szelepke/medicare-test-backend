<?php

namespace App\Http\Requests\Message;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'receiver_id' => [
                'required',
                'integer',
                'exists:users,id',
                'different:' . $this->user()->id,
            ],
            'message' => [
                'required',
                'string',
                'min:1',
                'max:2000'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'receiver_id.required' => __('validation.required', ['attribute' => 'címzett']),
            'receiver_id.exists' => __('validation.exists', ['attribute' => 'címzett']),
            'receiver_id.different' => __('messages.cannot_message_yourself'),
            'message.required' => __('validation.required', ['attribute' => 'üzenet']),
            'message.min' => __('validation.min.string', ['attribute' => 'üzenet', 'min' => 1]),
            'message.max' => __('validation.max.string', ['attribute' => 'üzenet', 'max' => 2000]),
        ];
    }
}