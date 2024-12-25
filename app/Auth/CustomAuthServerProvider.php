<?php

namespace App\Auth;

use Laravel\Passport\Passport;
use Illuminate\Support\ServiceProvider;
use League\OAuth2\Server\AuthorizationServer;
use Laravel\Passport\Bridge\PersonalAccessGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use Laravel\Passport\Bridge\UserRepository;
use Psr\Http\Message\ServerRequestInterface;
use App\Auth\CustomTokenGranter;
use App\Auth\CustomTokenEnhancer;
use App\Auth\CustomTokenConverter;

class CustomAuthServerProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(AuthorizationServer::class, function ($app) {
            $server = $app->make(AuthorizationServer::class);
            
            // Thêm CustomTokenEnhancer (TokenEnhancerChain)
            $server->enableGrantType(
                $this->makePasswordGrant(), 
                Passport::tokensExpireIn()
            );

            // Đăng ký Custom Token Granters (Tương đương CompositeTokenGranter)
            $server->setGrantTypeResolver(function (ServerRequestInterface $request) use ($app) {
                $granters = [
                    new CustomTokenGranter($app->make('auth')->guard(), 'custom'),
                    new CustomTokenGranter($app->make('auth')->guard(), 'anonymous'),
                    new CustomTokenGranter($app->make('auth')->guard(), 'tablet'),
                    new CustomTokenGranter($app->make('auth')->guard(), 'qrlive'),
                ];

                foreach ($granters as $granter) {
                    if ($granter->canGrant($request)) {
                        return $granter;
                    }
                }
            });

            // Thêm CustomTokenConverter (Tương đương JwtAccessTokenConverter)
            $server->setAccessTokenRepository(new CustomTokenConverter());

            return $server;
        });
    }

    // Password Grant (Tương tự AuthenticationManager)
    protected function makePasswordGrant()
    {
        $grant = new PasswordGrant(
            new UserRepository(),
            app()->make('auth')->createUserProvider()
        );

        $grant->setRefreshTokenTTL(Passport::refreshTokensExpireIn());
        return $grant;
    }

    public function boot()
    {
        Passport::routes();

        // Bảo mật token
        Passport::tokensCan([
            'trusted' => 'Access trusted endpoints',
            'basic' => 'Basic API access'
        ]);

        Passport::setDefaultScope([
            'basic'
        ]);
    }
}