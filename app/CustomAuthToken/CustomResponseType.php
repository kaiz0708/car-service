<?php

namespace App\CustomAuthToken;

use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Psr\Http\Message\ResponseInterface;

class CustomResponseType implements ResponseTypeInterface
{
    protected CustomAccessToken $accessToken;
    protected CustomRefreshToken $refreshToken;

    public function setAccessToken(CustomAccessToken|\League\OAuth2\Server\Entities\AccessTokenEntityInterface $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    /**
     * @throws \Exception
     */
    public function getAccessToken(): string
    {
        return $this->accessToken->getJWT();
    }

    public function setRefreshToken(CustomRefreshToken|\League\OAuth2\Server\Entities\RefreshTokenEntityInterface $refreshToken): void
    {
        $this->refreshToken = $refreshToken;
    }

    /**
     * @throws \Exception
     */
    public function getRefreshToken(): string
    {
        return $this->refreshToken->getJWT();
    }

    /**
     * @throws \Exception
     */
    public function generateHttpResponse(ResponseInterface $response): \Psr\Http\Message\MessageInterface|ResponseInterface
    {
        $body = json_encode([
            'access_token' => $this->getAccessToken(),
            'refresh_token' => $this->getRefreshToken(),
            'token_type' => 'bearer',
            'expires_in' => 3600,
        ]);

        $response->getBody()->write($body);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function setEncryptionKey($key = null)
    {
        // TODO: Implement setEncryptionKey() method.
    }
}
