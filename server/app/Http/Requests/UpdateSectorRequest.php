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
}
