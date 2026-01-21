<?php

namespace App\Http\Requests;

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
            //
            'user_id'  => 'required|exists:users,id',
            'game_id'  => 'required|exists:games,id',
            'seat_id'  => 'required|exists:seats,id',
            'status'   => 'required|string|in:paid,cancelled,reserved',

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
