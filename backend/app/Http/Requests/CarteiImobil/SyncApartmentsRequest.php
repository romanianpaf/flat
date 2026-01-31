<?php

namespace App\Http\Requests\CarteiImobil;

use App\Http\Requests\ApiFormRequest;

class SyncApartmentsRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'numbers' => ['required', 'array', 'min:1'],
            'numbers.*' => ['required', 'string', 'max:50'],
            'remove_missing' => ['sometimes', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'numbers' => 'numere apartamente',
        ];
    }
}

