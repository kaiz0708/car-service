<?php

namespace App\Grants;

use Laravel\Passport\Bridge\RefreshTokenRepository;
use Laravel\Passport\Bridge\UserRepository;
use Laravel\Passport\Bridge\User as UserEntity;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Grant\AbstractGrant;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Models\Account;
use League\OAuth2\Server\Exception\OAuthServerException;
use Illuminate\Support\Facades\Hash;
use League\OAuth2\Server\Entities\ClientEntityInterface;

class CustomPasswordGrant extends AbstractGrant
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

    public function getIdentifier(): string
    {
        return $this->grantType;
    }

    protected function getUserEntityByUserCredentials($username, $password): ?UserEntity
    {
        $user = Account::where('nickname', $username)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }
        return new UserEntity($user->getAuthIdentifier());
    }


    /**
     * @throws UniqueTokenIdentifierConstraintViolationException
     * @throws \DateMalformedStringException
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
        $initToken->persistNewAccessToken($accessToken);

        return $accessToken;
    }

    /**
     * @throws UniqueTokenIdentifierConstraintViolationException
     * @throws OAuthServerException
     * @throws \DateMalformedStringException
     */
    public function respondToAccessTokenRequest(
        ServerRequestInterface $request,
        ResponseTypeInterface $responseType,
        \DateInterval $accessTokenTTL
    ): ResponseTypeInterface
    {
        $client = $this->clientRepository->getClientEntity(5);

        if (!$client) {
            throw OAuthServerException::invalidClient($request);
        }

        $username = $this->getRequestParameter('username', $request);
        $password = $this->getRequestParameter('password', $request);

        if (!$username || !$password) {
            throw OAuthServerException::invalidRequest('username or password');
        }

        $user = $this->getUserEntityByUserCredentials(
            $username,
            $password,
        );

        $accessToken = $this->issueAccessToken(
            $accessTokenTTL,
            $client,
            $user->getIdentifier(),
            []
        );

        $refreshToken = $this->issueRefreshToken($accessToken);

        $responseType->setAccessToken($accessToken);
        $responseType->setRefreshToken($refreshToken);

        return $responseType;
    }
}


