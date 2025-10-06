<?php

namespace App\JsonApi\V2\Polls;

use App\Models\Poll;
use Carbon\Carbon;
use LaravelJsonApi\Eloquent\Contracts\Paginator;
use LaravelJsonApi\Eloquent\Fields\Boolean;
use LaravelJsonApi\Eloquent\Fields\DateTime;
use LaravelJsonApi\Eloquent\Fields\ID;
use LaravelJsonApi\Eloquent\Fields\Number;
use LaravelJsonApi\Eloquent\Fields\Relations\BelongsTo;
use LaravelJsonApi\Eloquent\Fields\Relations\HasMany;
use LaravelJsonApi\Eloquent\Fields\Str;
use LaravelJsonApi\Eloquent\Filters\Scope;
use LaravelJsonApi\Eloquent\Filters\WhereIdIn;
use LaravelJsonApi\Eloquent\Pagination\PagePagination;
use LaravelJsonApi\Eloquent\Schema;

class PollSchema extends Schema
{
    public static string $model = Poll::class;

    protected int $maxDepth = 3;

    protected $defaultSort = '-created_at';

    public function fields(): array
    {
        return [
            ID::make(),
            Str::make('title')->sortable(),
            Str::make('description'),
            Boolean::make('is_active')->sortable(),
            Boolean::make('allow_multiple_votes'),
            DateTime::make('start_date')
                ->serializeUsing(static fn(?Carbon $value) => $value?->format('Y-m-d H:i:s')),
            DateTime::make('end_date')
                ->serializeUsing(static fn(?Carbon $value) => $value?->format('Y-m-d H:i:s')),
            Number::make('tenant_id')->sortable(),
            BelongsTo::make('tenant')->type('tenants')->readOnly(),
            HasMany::make('options', 'options')->type('poll-options'),
            DateTime::make('created_at')
                ->serializeUsing(static fn(?Carbon $value) => $value?->format('Y-m-d H:i:s'))
                ->sortable()
                ->readOnly(),
            DateTime::make('updated_at')
                ->serializeUsing(static fn(?Carbon $value) => $value?->format('Y-m-d H:i:s'))
                ->readOnly(),
        ];
    }

    public function filters(): array
    {
        return [
            WhereIdIn::make($this),
            Scope::make('title'),
            Scope::make('is_active'),
        ];
    }

    public function pagination(): ?Paginator
    {
        return PagePagination::make();
    }
}
