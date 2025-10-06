<?php

namespace App\JsonApi\V2\Tenants;

use Illuminate\Validation\Rule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;

class TenantRequest extends ResourceRequest
{
    /**
     * Get the validation rules for the resource.
     *
     * @return array
     */
    public function rules(): array
    {
        $tenantId = $this->route('tenant');

        return [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'fiscal_code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('tenants', 'fiscal_code')->ignore($tenantId)->whereNull('deleted_at')
            ],
            'description' => ['nullable', 'string'],
            'contact_data' => ['nullable', 'array'],
            'contact_data.phone' => ['nullable', 'string', 'max:20'],
            'contact_data.email' => ['nullable', 'email', 'max:255'],
            'contact_data.person' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Numele este obligatoriu.',
            'fiscal_code.unique' => 'Acest CUI este deja înregistrat.',
            'contact_data.email.email' => 'Adresa de email nu este validă.',
        ];
    }
}

