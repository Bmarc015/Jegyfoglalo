<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGameRequest extends FormRequest
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
            'team_home_id' => ['required', 'exists:teams,id', 'different:team_away_id',],
            'team_away_id' => ['required', 'exists:teams,id',],
            'game_date' => [
                'required',
                'date',
                // Egyedi szabály
                Rule::unique('games')->where(function ($query) {
                    return $query->where('team_home_id', $this->team_home_id)
                        ->where('team_away_id', $this->team_away_id)
                        ->where('game_date', $this->game_date);
                }),
            ],
        ];
    }
    public function messages(): array
    {
        return [
            'team_home_id.required' => 'Selecting a home team is required.',
            'team_home_id.exists'   => 'The selected home team does not exist in our records.',
            'team_home_id.different' => 'The home team and the away team cannot be the same.',

            'team_away_id.required' => 'Selecting an away team is required.',
            'team_away_id.exists'   => 'The selected away team does not exist in our records.',

            'game_date.required'    => 'The game date is required.',
            'game_date.date'        => 'The provided date format is invalid.',
            'game_date.unique'      => 'A match between these two teams has already been scheduled for this date.',
        ];
    }
}
