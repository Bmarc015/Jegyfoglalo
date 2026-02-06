<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            'name' => 'required|string',
            'email' => 'required|email',
            'password' => 'required',
            
        ];
    }
    public function messages(): array
{
    return [
        'name.required'     => 'The name field is required.',
        'name.string'       => 'The name must be a valid text.',
        
        'email.required'    => 'An email address is required.',
        'email.email'       => 'Please provide a valid email address.',
        
        'password.required' => 'A password is required.',
    ];
}
}
