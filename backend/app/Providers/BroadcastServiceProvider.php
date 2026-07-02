<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Autorizarea canalelor private se face cu tokenul Passport (Bearer),
        // pe /api/broadcasting/auth — folosit de Laravel Echo din frontend.
        Broadcast::routes(['prefix' => 'api', 'middleware' => ['auth:api']]);

        require base_path('routes/channels.php');
    }
}
