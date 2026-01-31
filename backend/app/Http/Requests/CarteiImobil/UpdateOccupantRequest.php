<?php

namespace App\Http\Requests\CarteiImobil;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

class UpdateOccupantRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $requireCnp = (bool) config('carte_imobil.require_cnp', false);

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'cnp' => array_values(array_filter([
                $requireCnp ? 'required' : 'nullable',
                'string',
                'regex:/^\d{13}$/',
            ])),
            'id_series' => ['nullable', 'string', 'max:10'],
            'id_number' => ['nullable', 'string', 'max:20'],
            'domicile_address' => ['required', 'string'],

            'role_in_unit' => ['required', Rule::in(['owner', 'tenant', 'family', 'other'])],
            'other_role_text' => ['nullable', 'string', 'max:255', 'required_if:role_in_unit,other'],

            'move_in_date' => ['required', 'date'],
            'move_out_date' => ['nullable', 'date', 'after_or_equal:move_in_date'],

            'is_minor' => ['sometimes', 'boolean'],
            'legal_guardian_name' => ['nullable', 'string', 'max:255', 'required_if:is_minor,1,true'],

            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return (new StoreOccupantRequest())->attributes();
    }

    public function messages(): array
    {
        return (new StoreOccupantRequest())->messages();
    }
}

