<?php

namespace App\JsonApi\V2\UserVoices;

use LaravelJsonApi\Laravel\Http\Requests\ResourceQuery;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class UserVoiceCollectionQuery extends ResourceQuery
{
    public function rules(): array
    {
        return [
            'fields' => [
                'nullable',
                'array',
                JsonApiRule::fieldSets(),
            ],
            'filter' => [
                'nullable',
                'array',
                JsonApiRule::filter(),
            ],
            'include' => [
                'nullable',
                'string',
                JsonApiRule::includePaths(),
            ],
            'page' => [
                'nullable',
                'array',
                JsonApiRule::page(),
            ],
            'sort' => [
                'nullable',
                'string',
                JsonApiRule::sort(),
            ],
            'withCount' => [
                'nullable',
                'string',
                JsonApiRule::countable(),
            ],
            'filter.is_active' => ['filled', 'boolean'],
        ];
    }
}

