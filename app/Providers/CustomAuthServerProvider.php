<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\PasswordGrant;
use Laravel\Passport\Bridge\UserRepository;
use Laravel\Passport\Passport;
use App\Auth\CustomTokenConverter;
use App\Auth\CustomTokenGranter;

class CustomAuthServerProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(AuthorizationServer::class, function ($app) {
            $server = $app->make(AuthorizationServer::class);
            
            // Đăng ký Password Grant
            $server->enableGrantType(
                $this->makePasswordGrant($app), 
                Passport::tokensExpireIn()
            );

            // Thêm CustomTokenConverter (Access Token)
            $server->setAccessTokenRepository($app->make(CustomTokenConverter::class));

            // Đăng ký Custom Token Granters
            $this->registerCustomGranters($server, $app);

            return $server;
        });
    }

    protected function makePasswordGrant($app)
    {
        $grant = new PasswordGrant(
            new UserRepository($app->make('auth')->createUserProvider('users')),
            $app->make('auth')->createUserProvider('users')
        );

        $grant->setRefreshTokenTTL(Passport::refreshTokensExpireIn());
        return $grant;
    }

    // Đăng ký CustomTokenGranter
    protected function registerCustomGranters(AuthorizationServer $server, $app)
    {
        $granters = [
            new CustomTokenGranter($app->make('auth')->guard(), 'custom'),
            new CustomTokenGranter($app->make('auth')->guard(), 'anonymous'),
        ];

        foreach ($granters as $granter) {
            $server->enableGrantType(
                $granter,
                Passport::tokensExpireIn()
            );
        }
    }
}

