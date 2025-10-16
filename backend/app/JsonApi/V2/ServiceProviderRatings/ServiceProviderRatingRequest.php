<?php

namespace App\JsonApi\V2\ServiceProviderRatings;

use Illuminate\Validation\Rule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class ServiceProviderRatingRequest extends ResourceRequest
{

    /**
     * Get the validation rules for the resource.
     *
     * @return array
     */
    public function rules(): array
    {
        $rules = [
            'service_provider_id' => ['required', Rule::exists('service_providers', 'id')],
            'rating' => ['required', 'integer', 'min:0', 'max:5'],
            'comment' => ['nullable', 'string'],
        ];

        if ($this->isMethod('PATCH')) {
            $rules['service_provider_id'][0] = 'sometimes';
            $rules['rating'][0] = 'sometimes';
            $rules['comment'][0] = 'sometimes';
        }

        return $rules;
    }

}
