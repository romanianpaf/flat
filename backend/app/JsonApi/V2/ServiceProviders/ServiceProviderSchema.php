<?php

namespace App\JsonApi\V2\ServiceProviders;

use App\Models\ServiceProvider;
use LaravelJsonApi\Eloquent\Contracts\Paginator;
use LaravelJsonApi\Eloquent\Fields\Boolean;
use LaravelJsonApi\Eloquent\Fields\DateTime;
use LaravelJsonApi\Eloquent\Fields\ID;
use LaravelJsonApi\Eloquent\Fields\Str;
use LaravelJsonApi\Eloquent\Fields\Relations\BelongsTo;
use LaravelJsonApi\Eloquent\Fields\Relations\HasMany;
use LaravelJsonApi\Eloquent\Filters\Scope;
use LaravelJsonApi\Eloquent\Filters\WhereIdIn;
use LaravelJsonApi\Eloquent\Pagination\PagePagination;
use LaravelJsonApi\Eloquent\Schema;

class ServiceProviderSchema extends Schema
{

    protected $defaultSort = '-created_at';

    /**
     * The model the schema corresponds to.
     *
     * @var string
     */
    public static string $model = ServiceProvider::class;

    /**
     * Get the resource fields.
     *
     * @return array
     */
    public function fields(): array
    {
        return [
            ID::make(),
            Str::make('type')->sortable(),
            Str::make('first_name'),
            Str::make('last_name'),
            Str::make('company_name'),
            Str::make('phone'),
            Str::make('email'),
            Str::make('photo_path'),
            Str::make('service_description'),
            Boolean::make('is_published')->sortable(),
            BelongsTo::make('tenant')->type('tenants')->readOnly(),
            BelongsTo::make('category')->type('service-categories'),
            BelongsTo::make('subcategory')->type('service-subcategories'),
            BelongsTo::make('creator')->type('users')->readOnly(),
            BelongsTo::make('updater')->type('users')->readOnly(),
            HasMany::make('ratings')->type('service-provider-ratings'),
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
            Scope::make('type'),
            Scope::make('service_category_id'),
            Scope::make('service_subcategory_id'),
            Scope::make('is_published'),
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
