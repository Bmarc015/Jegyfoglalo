<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeamRequest extends FormRequest
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
            'team_name' => ['required', 'string', 'min:3', 'max:100', 'unique:teams,team_name',],
            'team_city' => ['required', 'string', 'min:2', 'max:100',],
        ];
    }
    public function messages(): array
    {
        return [
            'team_name.required' => 'The team name is required.',
            'team_name.string'   => 'The team name must be text.',
            'team_name.min'      => 'The team name must be at least 3 characters.',
            'team_name.max'      => 'The team name may not exceed 100 characters.',
            'team_name.unique'   => 'This team name is already in use.',

            'team_city.required' => 'The city name is required.',
            'team_city.string'   => 'The city name must be text.',
            'team_city.min'      => 'The city name must be at least 2 characters.',
            'team_city.max'      => 'The city name may not exceed 100 characters.',
        ];
    }
}
