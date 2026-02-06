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
                //Erre a meccsre ezt a széket ne lehessen újra lefoglalni
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
            'user_id.required' => 'The user ID is required.',
            'user_id.exists'   => 'The selected user does not exist.',

            'game_id.required' => 'The game ID is required.',
            'game_id.exists'   => 'The selected game does not exist.',

            'seat_id.required' => 'The seat ID is required.',
            'seat_id.exists'   => 'The selected seat is invalid.',
            'seat_id.unique'   => 'This seat has already been booked for this game!',

            'status.required'  => 'The ticket status is required.',
            'status.in'        => 'The status must be either reserved, paid, or cancelled.',
        ];
    }
}
