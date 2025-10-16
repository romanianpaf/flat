<?php

namespace App\Providers;

use App\Models\Automation;
use App\Models\Category;
use App\Models\Item;
use App\Models\Poll;
use App\Models\ServiceCategory;
use App\Models\ServiceProvider;
use App\Models\ServiceProviderRating;
use App\Models\ServiceSubcategory;
use App\Models\Tag;
use App\Models\Tenant;
use App\Models\User;
use App\Policies\AutomationPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\ItemPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\PollPolicy;
use App\Policies\RolePolicy;
use App\Policies\ServiceCategoryPolicy;
use App\Policies\ServiceProviderPolicy;
use App\Policies\ServiceProviderRatingPolicy;
use App\Policies\ServiceSubcategoryPolicy;
use App\Policies\TagPolicy;
use App\Policies\TenantPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as BaseAuthServiceProvider;
use Laravel\Passport\Passport;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AuthServiceProvider extends BaseAuthServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Automation::class           => AutomationPolicy::class,
        Category::class             => CategoryPolicy::class,
        Item::class                 => ItemPolicy::class,
        Permission::class           => PermissionPolicy::class,
        Poll::class                 => PollPolicy::class,
        Role::class                 => RolePolicy::class,
        ServiceCategory::class      => ServiceCategoryPolicy::class,
        ServiceSubcategory::class   => ServiceSubcategoryPolicy::class,
        ServiceProvider::class      => ServiceProviderPolicy::class,
        ServiceProviderRating::class => ServiceProviderRatingPolicy::class,
        Tag::class                  => TagPolicy::class,
        Tenant::class               => TenantPolicy::class,
        User::class                 => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // if (!$this->app->routesAreCached()) {
        //     Passport::routes();
        // }

        Passport::tokensExpireIn(now()->addDay());
    }
}
