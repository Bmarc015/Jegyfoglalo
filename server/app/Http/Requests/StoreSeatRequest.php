<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSeatRequest extends FormRequest
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
            'sector_id' => ['required', 'exists:sectors,id'],
            'row' => ['required', 'integer', 'min:1',],
            'col' => [
                'required',
                'integer',
                'min:1',
                // Egyediség szabály
                Rule::unique('seats')->where(function ($query) {
                    return $query->where('sector_id', $this->sector_id)
                        ->where('row', $this->row);
                }),
            ],
        ];
    }
    public function messages(): array
{
    return [
        'sector_id.required'   => 'The sector selection is required.',
        'sector_id.exists'     => 'The selected sector is invalid.',

        'row.required'         => 'The row number is required.',
        'row.integer'        => 'The row must be a whole number.',
        'row.min'            => 'The row must be at least 1.',
        
        'col.required'         => 'The column number is required.',
        'col.integer'        => 'The column must be a whole number.',
        'col.min'            => 'The column must be at least 1.',
        'col.unique'         => 'A seat already exists at these coordinates in this sector.',
    ];
}
}
