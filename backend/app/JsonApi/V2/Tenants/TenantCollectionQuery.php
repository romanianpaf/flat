<?php

namespace App\JsonApi\V2\Tenants;

use LaravelJsonApi\Laravel\Http\Requests\ResourceQuery;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class TenantCollectionQuery extends ResourceQuery
{
    /**
     * Get the validation rules that apply to the request query parameters.
     *
     * @return array
     */
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
            ],
            'filter.name' => [
                'nullable',
                'string',
            ],
            'filter.fiscal_code' => [
                'nullable',
                'string',
            ],
            'page' => [
                'nullable',
                'array',
            ],
            'page.number' => [
                'integer',
                'min:1',
            ],
            'page.size' => [
                'integer',
                'between:1,100',
            ],
            'sort' => [
                'nullable',
                'string',
                JsonApiRule::sort(),
            ],
        ];
    }
}

