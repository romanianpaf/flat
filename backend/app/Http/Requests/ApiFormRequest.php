<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class ApiFormRequest extends FormRequest
{
    protected function failedValidation(Validator $validator): void
    {
        $errors = [];
        foreach ($validator->errors()->toArray() as $field => $messages) {
            foreach ($messages as $message) {
                $errors[] = [
                    'field' => $field,
                    'message' => $message,
                ];
            }
        }

        throw new HttpResponseException(response()->json([
            'success' => false,
            'data' => null,
            'errors' => $errors,
        ], 422));
    }
}

