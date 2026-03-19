<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSeatRequest extends FormRequest
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
        // Megszerezzük a szék ID-ját a route-ból (pl. /api/seats/{seat})
        $seatId = $this->route('seat');

        return [
            'sector_id' => [
                'required',
                'exists:sectors,id'
            ],
            'row' => [
                'required',
                'integer',
                'min:1',
            ],
            'col' => [
                'required',
                'integer',
                'min:1',
                // Egyediség: Adott szektoron belül ugyanabban a sorban ne legyen két azonos oszlop,
                // kivéve ezt a konkrét széket.
                Rule::unique('seats')->where(function ($query) {
                    return $query->where('sector_id', $this->sector_id)
                        ->where('row', $this->row);
                })->ignore($seatId),
            ],
        ];
    }
    public function messages(): array
    {
        return [
            'sector_id.required'   => 'The sector selection is required.',
            'sector_id.exists'     => 'The selected sector does not exist.',

            'row.required'         => 'The row number is required.',
            'row.integer'          => 'The row must be an integer.',
            'row.min'              => 'The row must be at least 1.',

            'col.required'         => 'The column number is required.',
            'col.integer'          => 'The column must be an integer.',
            'col.min'              => 'The column must be at least 1.',
            'col.unique'           => 'These coordinates (row and column) are already occupied by another seat in this sector.',
        ];
    }
}
