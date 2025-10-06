<?php

namespace App\JsonApi\V2\PollOptions;

use App\Models\PollOption;
use Carbon\Carbon;
use LaravelJsonApi\Eloquent\Fields\DateTime;
use LaravelJsonApi\Eloquent\Fields\ID;
use LaravelJsonApi\Eloquent\Fields\Number;
use LaravelJsonApi\Eloquent\Fields\Relations\BelongsTo;
use LaravelJsonApi\Eloquent\Fields\Str;
use LaravelJsonApi\Eloquent\Schema;

class PollOptionSchema extends Schema
{
    public static string $model = PollOption::class;

    public function fields(): array
    {
        return [
            ID::make(),
            Number::make('poll_id'),
            Str::make('option_text'),
            Number::make('votes_count'),
            Number::make('order'),
            BelongsTo::make('poll')->type('polls')->readOnly(),
            DateTime::make('created_at')
                ->serializeUsing(static fn(?Carbon $value) => $value?->format('Y-m-d H:i:s'))
                ->readOnly(),
            DateTime::make('updated_at')
                ->serializeUsing(static fn(?Carbon $value) => $value?->format('Y-m-d H:i:s'))
                ->readOnly(),
        ];
    }
}
