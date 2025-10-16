<?php

namespace App\JsonApi\V2\ServiceProviders;

use Illuminate\Validation\Rule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class ServiceProviderRequest extends ResourceRequest
{

    /**
     * Get the validation rules for the resource.
     *
     * @return array
     */
    public function rules(): array
    {
        $rules = [
            'type' => ['required', Rule::in(['private', 'company'])],
            'service_category_id' => ['required', Rule::exists('service_categories', 'id')],
            'service_subcategory_id' => ['nullable', Rule::exists('service_subcategories', 'id')],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email'],
            'photo_path' => ['nullable', 'string', 'max:255'],
            'service_description' => ['nullable', 'string'],
            'is_published' => ['nullable', 'boolean'],
        ];

        if ($this->isMethod('PATCH')) {
            foreach ($rules as $key => $value) {
                array_unshift($rules[$key], 'sometimes');
            }
        }

        return $rules;
    }

}
