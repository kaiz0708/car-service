<?php

namespace App\Grants;

use App\CustomAuthToken\CustomAccessToken;
use App\CustomAuthToken\CustomAccessTokenRepository;
use App\CustomAuthToken\CustomRefreshToken;
use App\CustomAuthToken\CustomRefreshTokenRepository;
use App\CustomAuthToken\CustomResponseType;
use App\Models\Account;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Bridge\RefreshTokenRepository;
use Laravel\Passport\Bridge\User as UserEntity;
use Laravel\Passport\Bridge\UserRepository;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Grant\AbstractGrant;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Psr\Http\Message\ServerRequestInterface;

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

    protected function getUserEntityByUserCredentials($username, $password): Account|null
    {
        $user = Account::where('nickname', $username)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }
        return $user;
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

    /**
     * @throws UniqueTokenIdentifierConstraintViolationException
     * @throws OAuthServerException
     * @throws \DateMalformedStringException
     */
    public function respondToAccessTokenRequest(
        ServerRequestInterface                   $request,
        CustomResponseType|ResponseTypeInterface $responseType,
        \DateInterval                            $accessTokenTTL
    ): CustomResponseType
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
            (new UserEntity($user->getAuthIdentifier()))->getIdentifier(),
            []
        );

        $refreshToken = $this->issueRefreshToken($accessToken);

        $customResponseType = new CustomResponseType();
        $customResponseType->setAccessToken($accessToken);
        $customResponseType->setRefreshToken($refreshToken);

        return $customResponseType;
    }
}


