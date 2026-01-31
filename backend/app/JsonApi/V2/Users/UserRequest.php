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
                'first_name'    => ['sometimes', 'string', 'max:255'],
                'last_name'     => ['sometimes', 'string', 'max:255'],
                'email'         => ['sometimes', 'email', Rule::unique('users')->ignore($model->id)],
                'phone'         => ['sometimes', 'nullable', 'string'],
                'apartment'     => ['sometimes', 'nullable', 'string'],
                'staircase'     => ['sometimes', 'nullable', 'string'],
                'floor'         => ['sometimes', 'nullable', 'string'],
                'profile_image' => ['sometimes', 'nullable', 'url'],
                'password'      => ['sometimes', 'confirmed', 'string', 'min:8'],
                'tenant_id'     => ['sometimes', 'nullable', 'exists:tenants,id'],
                'roles'         => ['sometimes', JsonApiRule::toMany()],
            ];
        }

        return [
            'first_name'    => ['required', 'string', 'max:255'],
            'last_name'     => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', Rule::unique('users')],
            'phone'         => ['nullable', 'string'],
            'apartment'     => ['nullable', 'string'],
            'staircase'     => ['nullable', 'string'],
            'floor'         => ['nullable', 'string'],
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
            'first_name.required' => 'Prenumele este obligatoriu.',
            'first_name.max' => 'Prenumele nu poate depăși 255 caractere.',
            'last_name.required' => 'Numele este obligatoriu.',
            'last_name.max' => 'Numele nu poate depăși 255 caractere.',
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
