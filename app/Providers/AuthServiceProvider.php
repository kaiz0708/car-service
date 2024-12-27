<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use App\Providers\CustomAuthServerProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // 
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Passport::tokensCan([
            'access-api' => 'Access API endpoints',
        ]);

        app()->bind(
            \Laravel\Passport\Bridge\AccessToken::class,
            \App\Auth\CustomTokenConverter::class,
        );

        app()->bind(AccessTokenEntityInterface::class, function () {
            return new \App\Auth\CustomTokenEnhancer();
        });

        app()->register(CustomAuthServerProvider::class);
    }
}
