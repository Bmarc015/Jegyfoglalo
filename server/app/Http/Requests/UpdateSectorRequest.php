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
        $sectorId = $this->route('id');

        return [
            'sector_name' => [
                'required',
                'string',
                'max:10',
                // Ellenőrzi az egyediséget, de figyelmen kívül hagyja a jelenlegi szektort
                Rule::unique('sectors', 'sector_name')->ignore($sectorId),
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
            'sector_name.required' => 'The sector name is required.',
            'sector_name.string'   => 'The sector name must be a string.',
            'sector_name.max'      => 'The sector name may not be longer than 10 characters.',
            'sector_name.unique'   => 'This sector name is already assigned to another sector.',

            'sector_price.required'  => 'The sector price is required.',
            'sector_price.numeric'   => 'The price must be a numeric value.',
            'sector_price.min'       => 'The price cannot be a negative number.',
        ];
    }
}
