<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSectorRequest extends FormRequest
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
        // Megszerezzük a szektor ID-ját a route-ból
        $sectorId = $this->route('sector');

        return [
            'sector_number' => [
                'required',
                'string',
                'max:10',
                // Ellenőrzi az egyediséget, de figyelmen kívül hagyja a jelenlegi szektort
                Rule::unique('sectors', 'sector_number')->ignore($sectorId),
            ],
            'sector_price' => [
                'required',
                'numeric',
                'min:0',
            ],
        ];
    }
    public function messages(): array
    {
        return [
            'sector_number.required' => 'The sector number is required.',
            'sector_number.string'   => 'The sector number must be a string.',
            'sector_number.max'      => 'The sector number may not be longer than 10 characters.',
            'sector_number.unique'   => 'This sector number is already assigned to another sector.',

            'sector_price.required'  => 'The sector price is required.',
            'sector_price.numeric'   => 'The price must be a numeric value.',
            'sector_price.min'       => 'The price cannot be a negative number.',
        ];
    }
}
