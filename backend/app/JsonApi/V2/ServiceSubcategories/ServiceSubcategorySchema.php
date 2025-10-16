<?php

namespace App\JsonApi\V2\ServiceSubcategories;

use App\Models\ServiceSubcategory;
use LaravelJsonApi\Eloquent\Contracts\Paginator;
use LaravelJsonApi\Eloquent\Fields\DateTime;
use LaravelJsonApi\Eloquent\Fields\ID;
use LaravelJsonApi\Eloquent\Fields\Str;
use LaravelJsonApi\Eloquent\Fields\Relations\BelongsTo;
use LaravelJsonApi\Eloquent\Fields\Relations\HasMany;
use LaravelJsonApi\Eloquent\Filters\Scope;
use LaravelJsonApi\Eloquent\Filters\WhereIdIn;
use LaravelJsonApi\Eloquent\Pagination\PagePagination;
use LaravelJsonApi\Eloquent\Schema;

class ServiceSubcategorySchema extends Schema
{

    protected $defaultSort = '-created_at';

    /**
     * The model the schema corresponds to.
     *
     * @var string
     */
    public static string $model = ServiceSubcategory::class;

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
            BelongsTo::make('tenant')->type('tenants')->readOnly(),
            BelongsTo::make('category')->type('service-categories'),
            HasMany::make('providers')->type('service-providers'),
            DateTime::make('created_at')->readOnly()->sortable(),
            DateTime::make('updated_at')->readOnly()->sortable(),
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
            Scope::make('description'),
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
