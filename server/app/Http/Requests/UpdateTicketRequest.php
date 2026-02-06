<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketRequest extends FormRequest
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
        $ticketId = $this->route('ticket');

        return [
            'user_id' => 'required|exists:users,id',
            'game_id' => 'required|exists:games,id',
            'seat_id' => [
                'required',
                'exists:seats,id',
                // Egyediség ellenőrzése, kihagyva a jelenlegi jegyet
                Rule::unique('tickets')->where(function ($query) {
                    return $query->where('game_id', $this->game_id)
                        ->where('seat_id', $this->seat_id);
                })->ignore($ticketId),
            ],
            'status' => ['required', Rule::in(['reserved', 'paid', 'cancelled'])],
        ];
    }
    public function messages(): array
    {
        return [
            'user_id.required' => 'A user must be assigned to this ticket.',
            'user_id.exists'   => 'The selected user is invalid.',

            'game_id.required' => 'The match must be specified.',
            'game_id.exists'   => 'The selected match does not exist.',

            'seat_id.required' => 'A seat must be selected.',
            'seat_id.exists'   => 'The selected seat is invalid.',
            'seat_id.unique'   => 'This seat is already booked for this match by another customer.',

            'status.required'  => 'The ticket status is required.',
            'status.in'        => 'The status must be one of the following: reserved, paid, or cancelled.',
        ];
    }
}
