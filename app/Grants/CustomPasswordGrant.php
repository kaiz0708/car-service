<?php

namespace App\Grants;

use App\Models\Account;
use App\Models\Role;
use App\Response\CustomResponseType;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Bridge\RefreshTokenRepository;
use Laravel\Passport\Bridge\User as UserEntity;
use Laravel\Passport\Bridge\UserRepository;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Psr\Http\Message\ServerRequestInterface;

class CustomPasswordGrant extends CustomGrant
{
    public function __construct(
        UserRepository $userRepository,
        RefreshTokenRepository $refreshTokenRepository,
        string $grantType,
    ) {
        parent::__construct($userRepository ,$refreshTokenRepository, $grantType);
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

        /** @var Role $role */
        $role = Role::with('permissions')->find($user->role()->value('id'));

        $permission = $role->permissions->pluck('permission_code')->toArray();

        $accessToken = $this->issueAccessToken(
            $accessTokenTTL,
            $client,
            (new UserEntity($user->getAuthIdentifier()))->getIdentifier(),
            $permission
        );

        $refreshToken = $this->issueRefreshToken($accessToken);

        $responseType = new CustomResponseType();
        $responseType->setAccessToken($accessToken);
        $responseType->setRefreshToken($refreshToken);
        return $responseType;
    }
}


