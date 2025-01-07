<?php

namespace App\Providers;

use App\Grants\CustomPasswordGrant;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Bridge\RefreshTokenRepository;
use Laravel\Passport\Bridge\UserRepository;
use League\OAuth2\Server\AuthorizationServer;

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
        $grants = $this->makeCustomGrant();
        $authorizationServer = $this->app->make(AuthorizationServer::class);
        foreach ($grants as $type => $grant) {
            $authorizationServer->enableGrantType(
                $grant,
                new \DateInterval('PT1H')
            );
        }

        $this->app->bind(
            \App\CustomAuthToken\CustomAccessTokenRepository::class
        );
        $this->app->bind(
            \App\CustomAuthToken\CustomRefreshTokenRepository::class
        );
    }


    /**
     * @throws BindingResolutionException
     */
    protected function makeCustomGrant(): array
    {
        return [
            'password' => new CustomPasswordGrant(
                $this->app->make(UserRepository::class),
                $this->app->make(RefreshTokenRepository::class),
                "password"
            ),
        ];
    }
}
