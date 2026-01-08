<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isEmpty() && !$this->checkCredentials()) {
                    $validator->errors()->add(
                        'email',
                        'The provided credentials are incorrect.'
                    );
                }
            }
        ];
    }

    protected function checkCredentials(): bool
    {
        return Auth::validate([
            'email' => $this->email,
            'password' => $this->password,
        ]);
    }
}
