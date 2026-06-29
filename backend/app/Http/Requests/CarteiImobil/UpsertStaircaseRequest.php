<?php

namespace App\Http\Requests\CarteiImobil;

use App\Http\Requests\ApiFormRequest;

class UpsertStaircaseRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:20'],
            'name' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'floors' => ['nullable', 'array'],
            'floors.*' => ['string', 'max:20'],
        ];
    }

    public function attributes(): array
    {
        return [
            'code' => 'cod scară',
            'name' => 'nume scară',
            'sort_order' => 'ordine',
            'floors' => 'etaje disponibile',
        ];
    }
}

