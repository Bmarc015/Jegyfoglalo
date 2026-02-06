<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class UpdateGameRequest extends FormRequest
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
        // Megszerezzük a módosítani kívánt meccs ID-ját a route-ból
        $gameId = $this->route('game');

        return [
            'team_home_id' => [
                'required',
                'exists:teams,id',
                'different:team_away_id',
            ],
            'team_away_id' => [
                'required',
                'exists:teams,id',
            ],
            'game_date' => [
                'required',
                'date',
                // Egyedi szabály, de KIHAGYJA az aktuális meccset az ellenőrzésből
                Rule::unique('games')->where(function ($query) {
                    return $query->where('team_home_id', $this->team_home_id)
                        ->where('team_away_id', $this->team_away_id)
                        ->where('game_date', $this->game_date);
                })->ignore($gameId), // Ez a kulcsfontosságú rész!
            ],
        ];
    }
    public function messages(): array
    {
        return [
            'team_home_id.required' => 'The home team must be selected.',
            'team_home_id.exists'   => 'The selected home team is invalid.',
            'team_home_id.different' => 'The home team cannot be the same as the away team.',

            'team_away_id.required' => 'The away team must be selected.',
            'team_away_id.exists'   => 'The selected away team is invalid.',

            'game_date.required'    => 'Please provide the game date.',
            'game_date.date'        => 'The provided date format is incorrect.',
            'game_date.unique'      => 'A match with these teams on this specific date already exists (excluding this record).',
        ];
    }
}
