<?php

namespace App\JsonApi\V2\ServiceSubcategories;

use Illuminate\Validation\Rule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class ServiceSubcategoryRequest extends ResourceRequest
{

    /**
     * Get the validation rules for the resource.
     *
     * @return array
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'service_category_id' => ['required', Rule::exists('service_categories', 'id')],
        ];

        if ($this->isMethod('PATCH')) {
            $rules['name'][0] = 'sometimes';
            $rules['description'][0] = 'sometimes';
            $rules['service_category_id'][0] = 'sometimes';
        }

        return $rules;
    }

}
