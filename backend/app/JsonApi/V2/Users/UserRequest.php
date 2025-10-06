<?php

namespace App\JsonApi\V2\Users;

use Illuminate\Validation\Rule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class UserRequest extends ResourceRequest
{

    /**
     * Get the validation rules for the resource.
     *
     * @return array
     */
    public function rules(): array
    {
        /** @var \App\Models\User|null $model */
        if ($model = $this->model()) {
            return [
                'name'          => ['sometimes', 'string'],
                'email'         => ['sometimes', 'email', Rule::unique('users')->ignore($model->id)],
                'profile_image' => ['sometimes', 'nullable', 'url'],
                'password'      => ['sometimes', 'confirmed', 'string', 'min:8'],
                'tenant_id'     => ['sometimes', 'nullable', 'exists:tenants,id'],
                'roles'         => ['sometimes', JsonApiRule::toMany()],
            ];
        }

        return [
            'name'          => ['required', 'string'],
            'email'         => ['required', 'email', Rule::unique('users')],
            'profile_image' => ['nullable', 'url'],
            'password'      => ['required', 'confirmed', 'string', 'min:8'],
            'tenant_id'     => ['nullable', 'exists:tenants,id'],
            'roles'         => ['required', JsonApiRule::toMany()],
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
            'email.required' => 'Email-ul este obligatoriu.',
            'email.email' => 'Email-ul trebuie să fie valid.',
            'email.unique' => 'Acest email este deja folosit.',
            'password.required' => 'Parola este obligatorie.',
            'password.confirmed' => 'Confirmarea parolei nu corespunde.',
            'password.min' => 'Parola trebuie să aibă minim 8 caractere.',
            'tenant_id.exists' => 'Beneficiarul selectat nu există.',
        ];
    }
}
