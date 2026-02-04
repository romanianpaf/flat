<?php

namespace App\JsonApi\V2\Tenants;

use Illuminate\Validation\Rule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;

class TenantRequest extends ResourceRequest
{
    /**
     * Get the validation rules for the resource.
     *
     * @return array
     */
    public function rules(): array
    {
        $tenantId = $this->route('tenant');
        $user = $this->user();
        $isSysadmin = $user && $user->hasRole('sysadmin');

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'fiscal_code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('tenants', 'fiscal_code')->ignore($tenantId)->whereNull('deleted_at')
            ],
            'description' => ['nullable', 'string'],
            'contact_data' => ['nullable', 'array'],
            'contact_data.phone' => ['nullable', 'string', 'max:20'],
            'contact_data.email' => ['nullable', 'email', 'max:255'],
            'contact_data.person' => ['nullable', 'string', 'max:255'],
        ];

        // MQTT fields - only sysadmin can modify
        if ($isSysadmin) {
            $rules = array_merge($rules, [
                'mqtt_host' => ['nullable', 'string', 'max:255', 'ip'],
                'mqtt_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
                'mqtt_ca_path' => ['nullable', 'string', 'max:500', 'regex:/^\/etc\/mqtt\//'],
                'mqtt_client_cert_path' => ['nullable', 'string', 'max:500', 'regex:/^\/etc\/mqtt\//'],
                'mqtt_client_key_path' => ['nullable', 'string', 'max:500', 'regex:/^\/etc\/mqtt\//'],
                'mqtt_topic_prefix' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9_-]+$/i'],
            ]);
        }

        return $rules;
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
            'fiscal_code.unique' => 'Acest CUI este deja înregistrat.',
            'contact_data.email.email' => 'Adresa de email nu este validă.',
            'mqtt_host.ip' => 'Host-ul MQTT trebuie să fie o adresă IP validă.',
            'mqtt_port.min' => 'Portul MQTT trebuie să fie între 1 și 65535.',
            'mqtt_port.max' => 'Portul MQTT trebuie să fie între 1 și 65535.',
            'mqtt_ca_path.regex' => 'Calea CA trebuie să fie în /etc/mqtt/.',
            'mqtt_client_cert_path.regex' => 'Calea certificatului trebuie să fie în /etc/mqtt/.',
            'mqtt_client_key_path.regex' => 'Calea cheii trebuie să fie în /etc/mqtt/.',
            'mqtt_topic_prefix.regex' => 'Prefixul topicului poate conține doar litere, cifre, - și _.',
        ];
    }
}

