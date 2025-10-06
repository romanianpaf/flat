<?php

namespace App\JsonApi\V2\Automations;

use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class AutomationRequest extends ResourceRequest
{
    /**
     * Get the validation rules for the resource.
     *
     * @return array
     */
    public function rules(): array
    {
        /** @var \App\Models\Automation|null $model */
        if ($model = $this->model()) {
            return [
                'name' => ['sometimes', 'string', 'max:255'],
                'description' => ['sometimes', 'nullable', 'string'],
                'device_type' => ['sometimes', 'string', 'in:switch,sensor,actuator,light,lock'],
                'mqtt_broker_host' => ['sometimes', 'string', 'max:255'],
                'mqtt_broker_port' => ['sometimes', 'integer', 'min:1', 'max:65535'],
                'mqtt_broker_username' => ['sometimes', 'nullable', 'string', 'max:255'],
                'mqtt_broker_password' => ['sometimes', 'nullable', 'string', 'max:255'],
                'mqtt_topic' => ['sometimes', 'string', 'max:255'],
                'mqtt_payload_on' => ['sometimes', 'nullable', 'string'],
                'mqtt_payload_off' => ['sometimes', 'nullable', 'string'],
                'mqtt_qos' => ['sometimes', 'integer', 'in:0,1,2'],
                'is_active' => ['sometimes', 'boolean'],
                'tenant_id' => ['sometimes', 'nullable', 'exists:tenants,id'],
            ];
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'device_type' => ['required', 'string', 'in:switch,sensor,actuator,light,lock'],
            'mqtt_broker_host' => ['required', 'string', 'max:255'],
            'mqtt_broker_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'mqtt_broker_username' => ['nullable', 'string', 'max:255'],
            'mqtt_broker_password' => ['nullable', 'string', 'max:255'],
            'mqtt_topic' => ['required', 'string', 'max:255'],
            'mqtt_payload_on' => ['nullable', 'string'],
            'mqtt_payload_off' => ['nullable', 'string'],
            'mqtt_qos' => ['required', 'integer', 'in:0,1,2'],
            'is_active' => ['required', 'boolean'],
            'tenant_id' => ['nullable', 'exists:tenants,id'],
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
            'name.required' => 'Numele automatizării este obligatoriu.',
            'device_type.required' => 'Tipul automatizării este obligatoriu.',
            'device_type.in' => 'Tipul automatizării trebuie să fie: switch, sensor, actuator, light sau lock.',
            'mqtt_broker_host.required' => 'Host-ul broker-ului MQTT este obligatoriu.',
            'mqtt_broker_port.required' => 'Portul broker-ului MQTT este obligatoriu.',
            'mqtt_broker_port.integer' => 'Portul broker-ului MQTT trebuie să fie un număr.',
            'mqtt_broker_port.min' => 'Portul broker-ului MQTT trebuie să fie minim 1.',
            'mqtt_broker_port.max' => 'Portul broker-ului MQTT trebuie să fie maxim 65535.',
            'mqtt_topic.required' => 'Topicul MQTT este obligatoriu.',
            'mqtt_qos.required' => 'QoS MQTT este obligatoriu.',
            'mqtt_qos.in' => 'QoS MQTT trebuie să fie 0, 1 sau 2.',
            'is_active.required' => 'Statusul este obligatoriu.',
            'tenant_id.exists' => 'Beneficiarul selectat nu există.',
        ];
    }
}

