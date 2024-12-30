<?php

namespace App\Grants;

use Laravel\Passport\Bridge\RefreshTokenRepository;
use Laravel\Passport\Bridge\UserRepository;
use Laravel\Passport\Bridge\User as UserEntity;
use League\OAuth2\Server\Grant\AbstractGrant;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Models\Account;
use League\OAuth2\Server\Exception\OAuthServerException;
use Illuminate\Support\Facades\Hash;
use App\Grants\CustomTokenEnhancer;

class CustomPasswordGrant extends AbstractGrant
{
    protected $userRepository;
    private $grantType;

    public function __construct(
        UserRepository $userRepository,
        RefreshTokenRepository $refreshTokenRepository,
        string $grantType
    ) {
        $this->setUserRepository($userRepository);
        $this->setRefreshTokenRepository($refreshTokenRepository);
        $this->userRepository = $userRepository;
        $this->grantType = $grantType;
        $this->refreshTokenTTL = new \DateInterval('P1M');
    }

    public function getIdentifier()
    {
        return $this->grantType;
    }

    protected function getUserEntityByUserCredentials($username, $password, $grantType, $clientId)
    {
        $user = Account::where('nickname', $username)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        return new UserEntity($user->getAuthIdentifier());
    }

    public function respondToAccessTokenRequest(
        ServerRequestInterface $request,
        ResponseTypeInterface $responseType,
        \DateInterval $accessTokenTTL
    ) {
        // Lấy client mặc định (ví dụ client đầu tiên)
        $client = $this->clientRepository->getClientEntity(1); 
        
        // Chỉ cần validate username và password
        $username = $this->getRequestParameter('username', $request);
        $password = $this->getRequestParameter('password', $request);
        
        if (!$username || !$password) {
            throw OAuthServerException::invalidRequest('username or password');
        }
    
        $user = $this->getUserEntityByUserCredentials(
            $username,
            $password,
            $this->getIdentifier(),
            $client->getIdentifier()
        );


    
        if ($user === null) {
            throw OAuthServerException::invalidCredentials();
        }

        // Generate tokens

        $permissions = ['admin_access'];
        $accessToken = new CustomTokenEnhancer(
            $user->getIdentifier(),
            $client,
            $permissions
        );

        $refreshToken = $this->issueRefreshToken($accessToken);

        // Return response
        $responseType->setAccessToken($accessToken);
        $responseType->setRefreshToken($refreshToken);

        return $responseType;
    }
}


