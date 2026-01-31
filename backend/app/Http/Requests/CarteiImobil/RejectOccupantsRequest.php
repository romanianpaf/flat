<?php

namespace App\Http\Requests\CarteiImobil;

use App\Http\Requests\ApiFormRequest;

class RejectOccupantsRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:5', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'reason' => 'motiv',
        ];
    }
}

