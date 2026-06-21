<?php

namespace App\Http\Requests;

use App\Support\SipeniPassword;
use Illuminate\Foundation\Http\FormRequest;

class PasswordUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'password' => SipeniPassword::requiredConfirmed(),
            'password_confirmation' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return array_merge(
            [
                'current_password.required' => 'Password lama wajib diisi.',
                'password_confirmation.required' => 'Konfirmasi password wajib diisi.',
            ],
            SipeniPassword::validationMessages('password'),
        );
    }
}
