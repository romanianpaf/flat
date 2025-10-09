<?php

namespace App\JsonApi\V2\UserVoices;

use App\Models\UserVoice;
use Carbon\Carbon;
use LaravelJsonApi\Eloquent\Fields\Boolean;
use LaravelJsonApi\Eloquent\Fields\DateTime;
use LaravelJsonApi\Eloquent\Fields\ID;
use LaravelJsonApi\Eloquent\Fields\Number;
use LaravelJsonApi\Eloquent\Fields\Relations\BelongsTo;
use LaravelJsonApi\Eloquent\Fields\Str;
use LaravelJsonApi\Eloquent\Filters\WhereIdIn;
use LaravelJsonApi\Eloquent\Pagination\PagePagination;
use LaravelJsonApi\Eloquent\Schema;

class UserVoiceSchema extends Schema
{
    public static string $model = UserVoice::class;

    protected int $maxDepth = 3;

    protected $defaultSort = '-created_at';

    public function fields(): array
    {
        return [
            ID::make(),
            Str::make('suggestion')->sortable(),
            Number::make('user_id')->sortable(),
            Number::make('tenant_id')->sortable(),
            Number::make('votes_up')->sortable(),
            Number::make('votes_down')->sortable(),
            Boolean::make('is_active')->sortable(),
            BelongsTo::make('user')->type('users')->readOnly(),
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

    public function filters(): array
    {
        return [
            WhereIdIn::make($this),
        ];
    }

    public function pagination(): PagePagination
    {
        return PagePagination::make();
    }
}

