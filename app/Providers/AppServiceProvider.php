<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use League\OAuth2\Server\AuthorizationServer;
use App\Grants\PasswordGrant;
use App\Grants\CustomGrant;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        
    }
}
