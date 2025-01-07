<?php

namespace App\Grants;

use App\CustomAuthToken\CustomAccessToken;
use App\CustomAuthToken\CustomAccessTokenRepository;
use App\CustomAuthToken\CustomRefreshToken;
use App\CustomAuthToken\CustomRefreshTokenRepository;
use DateInterval;
use Laravel\Passport\Bridge\RefreshTokenRepository;
use Laravel\Passport\Bridge\UserRepository;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Grant\AbstractGrant;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Psr\Http\Message\ServerRequestInterface;

class CustomGrant extends AbstractGrant
{
    protected $userRepository;
    private string $grantType;

    public function __construct(
        UserRepository $userRepository,
        RefreshTokenRepository $refreshTokenRepository,
        string $grantType,
    ) {
        $this->setUserRepository($userRepository);
        $this->setRefreshTokenRepository($refreshTokenRepository);
        $this->userRepository = $userRepository;
        $this->grantType = $grantType;
        $this->refreshTokenTTL = new \DateInterval('P1M');
    }


    /**
     * @throws UniqueTokenIdentifierConstraintViolationException
     * @throws \DateMalformedStringException
     * @throws \Exception
     */
    protected function issueAccessToken(
        \DateInterval $accessTokenTTL,
        ClientEntityInterface $client,
        $userIdentifier,
        array $scopes = []
    ): CustomAccessToken|\League\OAuth2\Server\Entities\AccessTokenEntityInterface
    {
        $initToken = new CustomAccessTokenRepository();
        $accessToken = $initToken->getNewToken($client, $scopes, $userIdentifier);
        $accessToken->convertToJWT();
        $initToken->persistNewAccessToken($accessToken);

        return $accessToken;
    }


    /**
     * @throws \DateMalformedStringException
     * @throws UniqueTokenIdentifierConstraintViolationException
     * @throws \Exception
     */
    protected function issueRefreshToken(CustomAccessToken|\League\OAuth2\Server\Entities\AccessTokenEntityInterface $accessToken): CustomRefreshToken
    {
        $initToken = new CustomRefreshTokenRepository();
        $refreshToken = $initToken->getNewRefreshToken();
        $refreshToken->setAccessToken($accessToken);
        $refreshToken->convertToJWT();
        $initToken->persistNewRefreshToken($refreshToken);

        return $refreshToken;
    }

    public function getIdentifier(): string
    {
        return $this->grantType;
    }

    public function respondToAccessTokenRequest(ServerRequestInterface $request, ResponseTypeInterface $responseType, DateInterval $accessTokenTTL)
    {

    }
}
