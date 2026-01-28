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
}
