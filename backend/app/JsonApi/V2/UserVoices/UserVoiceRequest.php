<?php

namespace App\JsonApi\V2\UserVoices;

use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;

class UserVoiceRequest extends ResourceRequest
{
    public function rules(): array
    {
        if ($this->model()) {
            return [
                'suggestion' => ['sometimes', 'string'],
                'user_id' => ['sometimes', 'exists:users,id'],
                'tenant_id' => ['sometimes', 'nullable', 'exists:tenants,id'],
                'votes_up' => ['sometimes', 'integer', 'min:0'],
                'votes_down' => ['sometimes', 'integer', 'min:0'],
                'is_active' => ['sometimes', 'boolean'],
            ];
        }

        return [
            'suggestion' => ['required', 'string', 'min:10'],
            'user_id' => ['sometimes', 'exists:users,id'],
            'tenant_id' => ['nullable', 'exists:tenants,id'],
            'votes_up' => ['sometimes', 'integer', 'min:0'],
            'votes_down' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'suggestion.required' => 'Sugestia este obligatorie.',
            'suggestion.min' => 'Sugestia trebuie să aibă cel puțin 10 caractere.',
        ];
    }
}

