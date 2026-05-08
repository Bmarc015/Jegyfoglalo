<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSectorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'string', 'max:10', 'unique:sectors,id'],
            'sector_name' => ['required', 'string', 'max:10', 'unique:sectors,sector_name'],
            'sector_price' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => 'The sector ID is required.',
            'id.string' => 'The sector ID must be text.',
            'id.max' => 'The sector ID may not exceed 10 characters.',
            'id.unique' => 'This sector ID has already been registered.',

            'sector_name.required' => 'The sector name is required.',
            'sector_name.string' => 'The sector name must be text.',
            'sector_name.max' => 'The sector name may not exceed 10 characters.',
            'sector_name.unique' => 'This sector name has already been registered.',

            'sector_price.required' => 'The sector price is required.',
            'sector_price.numeric' => 'The price must be a valid number.',
            'sector_price.min' => 'The price cannot be a negative value.',
        ];
    }
}
