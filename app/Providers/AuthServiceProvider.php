<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use League\OAuth2\Server\AuthorizationServer;
use Laravel\Passport\Bridge\RefreshTokenRepository;
use Laravel\Passport\Bridge\UserRepository;
use App\Grants\CustomPasswordGrant;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // 
    ];

    public function boot(): void
    {
        $this->app->make(AuthorizationServer::class)->enableGrantType(
            $this->makeCustomGrant(),
            new \DateInterval('PT1H') // Access token TTL
        );
    }


    protected function makeCustomGrant()
    {
        $grant = new CustomPasswordGrant(
            $this->app->make(UserRepository::class),
            $this->app->make(RefreshTokenRepository::class),
            "password"
        );
        
        return $grant;
    }
}
