<?php

namespace App\CustomAuthToken;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use Ramsey\Uuid\Uuid;

class CustomAccessTokenRepository implements AccessTokenRepositoryInterface
{
    /**
     * @throws \DateMalformedStringException
     */
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null): \App\CustomAuthToken\CustomAccessToken
    {
        $accessToken = new CustomAccessToken($userIdentifier, $scopes, $clientEntity);
        $tokenId = Uuid::uuid4()->toString();
        $accessToken->setClient($clientEntity);
        $accessToken->setUserIdentifier($userIdentifier);
        $accessToken->setIdentifier($tokenId);
        $accessToken->setClaims([
            "permissions" => $scopes
        ]);
        $accessToken->setExpiryDateTime((new DateTimeImmutable())->modify('+1 hour'));

        return $accessToken;
    }

    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity): void
    {
        DB::table('oauth_access_tokens')->insert([
            'id' => $accessTokenEntity->getIdentifier(),
            'user_id' => $accessTokenEntity->getUserIdentifier(),
            'client_id' => $accessTokenEntity->getClient()->getIdentifier(),
            'name' => "defaults",
            'scopes' => json_encode($accessTokenEntity->getScopes()),
            'revoked' => false,
            'created_at' => now(),
            'updated_at' => now(),
            'expires_at' => $accessTokenEntity->getExpiryDateTime()->format('Y-m-d H:i:s'),
        ]);
    }



    public function revokeAccessToken($tokenId)
    {
        // Logic để thu hồi access token
    }

    public function isAccessTokenRevoked($tokenId): bool
    {
        return false;
    }
}
