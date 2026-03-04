<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserSelfRequest extends FormRequest
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
            'name' => [
                'sometimes',
                'required',
                'string',
                'required_without_all:email',
            ],
            'email' => [
                'sometimes',
                'required',
                'email',
                'required_without_all:name',
                Rule::unique('users')->ignore($this->user()->id),
            ],
            'billing_address' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],
            'billing_city' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
            ],
            'billing_zip' => [
                'sometimes',
                'nullable',
                'regex:/^[0-9]+$/',
                'max:20',
            ],
            'role' => 'prohibited',
            'password' => 'prohibited',
        ];
    }
}
