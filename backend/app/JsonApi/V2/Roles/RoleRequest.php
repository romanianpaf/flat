<?php

namespace App\JsonApi\V2\Roles;

use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;

class RoleRequest extends ResourceRequest
{
    /**
     * Get the validation rules for the resource.
     *
     * @return array
     */
    public function rules(): array
    {
        $roleId = $this->route('role');
        $tenantId = session('current_tenant_id');

        if ($this->model()) {
            return [
                'name' => [
                    'sometimes',
                    'string',
                    function ($attribute, $value, $fail) use ($roleId, $tenantId) {
                        $query = \App\Models\Role::withoutGlobalScopes()
                            ->where('name', $value)
                            ->where('id', '!=', $roleId);
                        
                        // Verifică duplicate în același tenant
                        if ($tenantId) {
                            $query->where('tenant_id', $tenantId);
                        } else {
                            $query->whereNull('tenant_id');
                        }
                        
                        if ($query->exists()) {
                            $fail('Un rol cu acest nume există deja în acest context.');
                        }
                    },
                ],
                'tenant_id' => ['sometimes', 'nullable', 'exists:tenants,id'],
            ];
        }

        return [
            'name' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($tenantId) {
                    $query = \App\Models\Role::withoutGlobalScopes()
                        ->where('name', $value);
                    
                    // Verifică duplicate în același tenant
                    if ($tenantId) {
                        $query->where('tenant_id', $tenantId);
                    } else {
                        $query->whereNull('tenant_id');
                    }
                    
                    if ($query->exists()) {
                        $fail('Un rol cu acest nume există deja în acest context.');
                    }
                },
            ],
            'tenant_id' => ['nullable', 'exists:tenants,id'],
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
            'name.required' => 'Numele rolului este obligatoriu.',
            'tenant_id.exists' => 'Beneficiarul selectat nu există.',
        ];
    }
}
