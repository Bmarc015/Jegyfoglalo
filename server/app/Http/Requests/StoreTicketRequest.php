<?php

namespace App\Http\Requests;
use Illuminate\Validation\Rule;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
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
            'user_id' => 'required|exists:users,id',
            'game_id' => 'required|exists:games,id',
            'seat_id' => [
                'required',
                'exists:seats,id',
                // Összetett egyediség: Erre a meccsre ezt a széket ne lehessen újra lefoglalni
                Rule::unique('tickets')->where(function ($query) {
                    return $query->where('game_id', $this->game_id)
                                 ->where('seat_id', $this->seat_id);
                }),
            ],
            'status' => ['required', Rule::in(['reserved', 'paid', 'cancelled'])],
        ];
    }
 
    public function messages(): array
{
    return [
        'status.in' => 'Invalid ticket status. Allowed values: paid, cancelled, reserved.',
        'status.required' => 'The ticket status is required.',
    ];
}

}
