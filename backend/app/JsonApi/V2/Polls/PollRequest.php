<?php

namespace App\JsonApi\V2\Polls;

use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class PollRequest extends ResourceRequest
{
    public function rules(): array
    {
        if ($this->model()) {
            return [
                'title' => ['sometimes', 'string', 'max:255'],
                'description' => ['sometimes', 'nullable', 'string'],
                'is_active' => ['sometimes', 'boolean'],
                'allow_multiple_votes' => ['sometimes', 'boolean'],
                'start_date' => ['sometimes', 'nullable', 'date'],
                'end_date' => ['sometimes', 'nullable', 'date', 'after:start_date'],
                'tenant_id' => ['sometimes', 'nullable', 'exists:tenants,id'],
                'options' => ['sometimes', JsonApiRule::toMany()],
            ];
        }

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
            'allow_multiple_votes' => ['required', 'boolean'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'tenant_id' => ['nullable', 'exists:tenants,id'],
            'options' => ['nullable', JsonApiRule::toMany()], // Opțiunile se pot adăuga separat
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Titlul sondajului este obligatoriu.',
            'is_active.required' => 'Statusul este obligatoriu.',
            'allow_multiple_votes.required' => 'Trebuie să specifici dacă se pot vota multiple opțiuni.',
            'end_date.after' => 'Data de încheiere trebuie să fie după data de start.',
            'options.required' => 'Trebuie să adaugi cel puțin 2 opțiuni de vot.',
            'options.min' => 'Trebuie să adaugi cel puțin 2 opțiuni de vot.',
            'tenant_id.exists' => 'Beneficiarul selectat nu există.',
        ];
    }
}
