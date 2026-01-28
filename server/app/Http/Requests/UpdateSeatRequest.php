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
            'seat_number' => [
                'required',
                'integer',
                'min:1',
                // Egyediség: Adott szektoron belül ne legyen két azonos seat_number, 
                // de a jelenlegi székünket (ID alapján) hagyja figyelmen kívül.
                Rule::unique('seats')->where(function ($query) {
                    return $query->where('sector_id', $this->sector_id);
                })->ignore($seatId),
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
}
