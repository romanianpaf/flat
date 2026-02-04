<?php

namespace App\JsonApi\V2\Automations;

use App\Models\Automation;
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
        $triggerTypes = implode(',', array_keys(Automation::getTriggerTypes()));
        $actionTypes = implode(',', array_keys(Automation::getActionTypes()));
        
        /** @var \App\Models\Automation|null $model */
        if ($model = $this->model()) {
            return [
                'name' => ['sometimes', 'string', 'max:255'],
                'description' => ['sometimes', 'nullable', 'string'],
                'device_type' => ['sometimes', 'string', 'in:switch,sensor,actuator,light,lock,test'],
                'trigger_type' => ['sometimes', 'string', "in:{$triggerTypes}"],
                'action_type' => ['sometimes', 'string', "in:{$actionTypes}"],
                'schedule_cron' => ['sometimes', 'nullable', 'string', 'max:100'],
                'mqtt_subscribe_topic' => ['sometimes', 'nullable', 'string', 'max:255'],
                'mqtt_subscribe_payload_match' => ['sometimes', 'nullable', 'string', 'max:255'],
                'mqtt_topic' => ['sometimes', 'string', 'max:255'],
                'mqtt_payload_on' => ['sometimes', 'nullable', 'string'],
                'mqtt_payload_off' => ['sometimes', 'nullable', 'string'],
                'mqtt_qos' => ['sometimes', 'integer', 'in:0,1,2'],
                'mqtt_retain' => ['sometimes', 'boolean'],
                'cooldown_ms' => ['sometimes', 'integer', 'min:0'],
                'is_active' => ['sometimes', 'boolean'],
                'tenant_id' => ['sometimes', 'nullable', 'exists:tenants,id'],
            ];
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'device_type' => ['required', 'string', 'in:switch,sensor,actuator,light,lock,test'],
            'trigger_type' => ['required', 'string', "in:{$triggerTypes}"],
            'action_type' => ['required', 'string', "in:{$actionTypes}"],
            'schedule_cron' => ['nullable', 'string', 'max:100', 'required_if:trigger_type,scheduled'],
            'mqtt_subscribe_topic' => ['nullable', 'string', 'max:255', 'required_if:trigger_type,mqtt_event'],
            'mqtt_subscribe_payload_match' => ['nullable', 'string', 'max:255'],
            'mqtt_topic' => ['required_if:action_type,mqtt_publish', 'nullable', 'string', 'max:255'],
            'mqtt_payload_on' => ['nullable', 'string'],
            'mqtt_payload_off' => ['nullable', 'string'],
            'mqtt_qos' => ['required_if:action_type,mqtt_publish', 'nullable', 'integer', 'in:0,1,2'],
            'mqtt_retain' => ['nullable', 'boolean'],
            'cooldown_ms' => ['nullable', 'integer', 'min:0'],
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
            'device_type.required' => 'Tipul dispozitivului este obligatoriu.',
            'device_type.in' => 'Tipul dispozitivului trebuie să fie: switch, sensor, actuator, light, lock sau test.',
            'trigger_type.required' => 'Tipul de declanșare este obligatoriu.',
            'trigger_type.in' => 'Tipul de declanșare trebuie să fie: manual, scheduled sau mqtt_event.',
            'action_type.required' => 'Tipul de acțiune este obligatoriu.',
            'action_type.in' => 'Tipul de acțiune trebuie să fie: mqtt_publish, webhook sau notification.',
            'schedule_cron.required_if' => 'Expresia cron este obligatorie pentru trigger-ul programat.',
            'mqtt_subscribe_topic.required_if' => 'Topic-ul de subscribe este obligatoriu pentru trigger-ul event MQTT.',
            'mqtt_topic.required_if' => 'Topic-ul MQTT este obligatoriu pentru acțiunea mqtt_publish.',
            'mqtt_qos.required_if' => 'QoS MQTT este obligatoriu pentru acțiunea mqtt_publish.',
            'mqtt_qos.in' => 'QoS MQTT trebuie să fie 0, 1 sau 2.',
            'cooldown_ms.min' => 'Cooldown-ul trebuie să fie minim 0 ms.',
            'is_active.required' => 'Statusul este obligatoriu.',
            'tenant_id.exists' => 'Beneficiarul selectat nu există.',
        ];
    }
}

