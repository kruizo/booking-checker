<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserPermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // User can only update their own permission
        $targetUserId = (int) $this->route('id');
        return $this->user()->id === $targetUserId;
    }

    public function rules(): array
    {
        return [
            'is_admin' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'is_admin.required' => 'The admin status is required.',
            'is_admin.boolean' => 'The admin status must be true or false.',
        ];
    }
}
