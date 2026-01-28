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
            'team_home_id' => [
                'required',
                'exists:teams,id',
                'different:team_away_id', // Ne lehessen önmaga ellen játszani
            ],
            'team_away_id' => [
                'required',
                'exists:teams,id',
            ],
            'game_date' => [
                'required',
                'date',
                // Egyedi szabály: ne legyen meccs ugyanazzal a két csapattal ugyanabban az időpontban
                Rule::unique('games')->where(function ($query) {
                    return $query->where('team_home_id', $this->team_home_id)
                                 ->where('team_away_id', $this->team_away_id)
                                 ->where('game_date', $this->game_date);
                }),
            ],
        ];
    }
}
