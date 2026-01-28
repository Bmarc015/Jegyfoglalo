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
            'sector_id' => [
                'required',
                'exists:sectors,id'
            ],
            'seat_number' => [
                'required',
                'integer',
                'min:1',
                // Egyediség: Adott szektoron belül ne legyen két azonos seat_number
                Rule::unique('seats')->where(function ($query) {
                    return $query->where('sector_id', $this->sector_id);
                }),
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
                // Egyediség: Adott szektoron belül ugyanabban a sorban ne legyen két azonos oszlop
                Rule::unique('seats')->where(function ($query) {
                    return $query->where('sector_id', $this->sector_id)
                                 ->where('row', $this->row);
                }),
            ],
        ];
    }
 
}
