<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSectorRequest extends FormRequest
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
            'sector_number' => ['required', 'string', 'max:10', 'unique:sectors,sector_number',],
            'sector_price' => ['required', 'numeric', 'min:0',],
        ];
    }
    public function messages(): array
    {
        return [
            'sector_number.required' => 'The sector number is required.',
            'sector_number.string'   => 'The sector number must be text.',
            'sector_number.max'      => 'The sector number may not exceed 10 characters.',
            'sector_number.unique'   => 'This sector number has already been registered.',

            'sector_price.required'  => 'The sector price is required.',
            'sector_price.numeric'   => 'The price must be a valid number.',
            'sector_price.min'       => 'The price cannot be a negative value.',
        ];
    }
}
