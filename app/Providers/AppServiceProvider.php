<?php

namespace App\Providers;

use App\Grants\CustomGrant;
use App\Grants\PasswordGrant;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface::class,
            \App\CustomAuthToken\CustomAccessTokenRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

    }
}
