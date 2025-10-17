<?php

namespace App\JsonApi\V2\Roles;

use App\Models\Role;
use Carbon\Carbon;
use LaravelJsonApi\Eloquent\Contracts\Paginator;
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

class RoleSchema extends Schema
{
    /**
     * The model the schema corresponds to.
     *
     * @var string
     */
    public static string $model = Role::class;

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
            Number::make('tenant_id')->sortable(),
            BelongsTo::make('tenant')->type('tenants')->readOnly(),
            HasMany::make('permissions'),
            HasMany::make('users'),
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
        ];
    }

    public function indexQuery(?\Illuminate\Http\Request $request, \Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        $user = auth()->user();

        if (!$user) {
            return $query;
        }

        // Sysadmin și admini văd toți rolurile
        if ($user->hasRole(['admin', 'sysadmin'])) {
            return $query;
        }

        // Utilizatorii cu tenant văd doar rolurile propriului tenant
        if ($user->tenant_id) {
            return $query->where('tenant_id', $user->tenant_id);
        }

        return $query;
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
