<?php

namespace App\JsonApi\V2\Automations;

use App\Models\Automation;
use Carbon\Carbon;
use LaravelJsonApi\Eloquent\Contracts\Paginator;
use LaravelJsonApi\Eloquent\Fields\Boolean;
use LaravelJsonApi\Eloquent\Fields\DateTime;
use LaravelJsonApi\Eloquent\Fields\ID;
use LaravelJsonApi\Eloquent\Fields\Number;
use LaravelJsonApi\Eloquent\Fields\Relations\BelongsTo;
use LaravelJsonApi\Eloquent\Fields\Str;
use LaravelJsonApi\Eloquent\Filters\Scope;
use LaravelJsonApi\Eloquent\Filters\WhereIdIn;
use LaravelJsonApi\Eloquent\Pagination\PagePagination;
use LaravelJsonApi\Eloquent\Schema;

class AutomationSchema extends Schema
{
    /**
     * The model the schema corresponds to.
     *
     * @var string
     */
    public static string $model = Automation::class;

    /**
     * Get the resource fields.
     *
     * @return array
     */
    public function fields(): array
    {
        return [
            ID::make(),
            Str::make('name')->sortable(),
            Str::make('description'),
            Str::make('device_type', 'type')->sortable(), // Alias pentru câmpul "type" din baza de date
            
            // MQTT Broker Configuration
            Str::make('mqtt_broker_host'),
            Number::make('mqtt_broker_port'),
            Str::make('mqtt_broker_username'),
            Str::make('mqtt_broker_password')->hidden(), // Hidden for security
            
            // MQTT Topic & Payload
            Str::make('mqtt_topic'),
            Str::make('mqtt_payload_on'),
            Str::make('mqtt_payload_off'),
            Number::make('mqtt_qos'),
            
            // Status & Relations
            Boolean::make('is_active')->sortable(),
            Number::make('tenant_id')->sortable(),
            BelongsTo::make('tenant')->type('tenants')->readOnly(),
            
            DateTime::make('created_at')
                ->serializeUsing(static fn(?Carbon $value) => $value?->format('Y-m-d H:i:s'))
                ->sortable()
                ->readOnly(),
            DateTime::make('updated_at')
                ->serializeUsing(static fn(?Carbon $value) => $value?->format('Y-m-d H:i:s'))
                ->readOnly(),
        ];
    }

    /**
     * Get the resource filters.
     *
     * @return array
     */
    public function filters(): array
    {
        return [
            WhereIdIn::make($this),
            Scope::make('name'),
            Scope::make('device_type', 'type'), // Filtrare pe câmpul "type" din baza de date
        ];
    }

    /**
     * Get the resource paginator.
     *
     * @return Paginator|null
     */
    public function pagination(): ?Paginator
    {
        return PagePagination::make();
    }
}

