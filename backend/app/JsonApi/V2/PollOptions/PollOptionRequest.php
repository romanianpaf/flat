<?php

namespace App\JsonApi\V2\PollOptions;

use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;

class PollOptionRequest extends ResourceRequest
{
    public function rules(): array
    {
        if ($this->model()) {
            return [
                'poll_id' => ['sometimes', 'integer', 'exists:polls,id'],
                'option_text' => ['sometimes', 'string', 'max:255'],
                'order' => ['sometimes', 'integer'],
                'votes_count' => ['sometimes', 'integer', 'min:0'],
            ];
        }

        return [
            'poll_id' => ['required', 'integer', 'exists:polls,id'],
            'option_text' => ['required', 'string', 'max:255'],
            'order' => ['nullable', 'integer'],
            'votes_count' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'poll_id.required' => 'ID-ul sondajului este obligatoriu.',
            'poll_id.exists' => 'Sondajul selectat nu există.',
            'option_text.required' => 'Textul opțiunii este obligatoriu.',
            'option_text.max' => 'Textul opțiunii nu poate depăși 255 caractere.',
            'votes_count.min' => 'Numărul de voturi nu poate fi negativ.',
        ];
    }
}

