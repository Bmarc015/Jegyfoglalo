<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeamRequest extends FormRequest
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
        // Megszerezzük a csapat ID-ját a route-ból
        $teamId = $this->route('team');

        return [
            'team_name' => [
                'required',
                'string',
                'min:3',
                'max:100',
                // Egyediség ellenőrzése, kivéve a jelenlegi csapatot
                Rule::unique('teams', 'team_name')->ignore($teamId),
            ],
            'team_city' => [
                'required',
                'string',
                'min:2',
                'max:100',
            ],
        ];
    }
 
}
