<?php

namespace App\Providers;

use App\Grants\CustomAccessTokenRepository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use League\OAuth2\Server\AuthorizationServer;
use Laravel\Passport\Bridge\RefreshTokenRepository;
use Laravel\Passport\Bridge\UserRepository;
use App\Grants\CustomPasswordGrant;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        //
    ];

    /**
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        $this->app->make(AuthorizationServer::class)->enableGrantType(
            $this->makeCustomGrant(),
            new \DateInterval('PT1H') // Access token TTL
        );

        $this->app->bind(
            \App\Grants\CustomAccessTokenRepository::class
        );
    }


    /**
     * @throws BindingResolutionException
     */
    protected function makeCustomGrant(): CustomPasswordGrant
    {
        return new CustomPasswordGrant(
            $this->app->make(UserRepository::class),
            $this->app->make(RefreshTokenRepository::class),
            "password"
        );
    }
}
