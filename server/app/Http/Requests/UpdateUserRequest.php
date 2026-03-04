<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'nullable|string',
            'email' => 'nullable|email',
            'password' => 'nullable',
            'role' => 'nullable',
            'billing_city' => 'nullable|string|max:100',
            'billing_zip' => ['nullable', 'regex:/^[0-9]+$/', 'max:20'],
            'billing_address' => 'nullable|string|max:255',
        ];
    }
}
