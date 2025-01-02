<?php

namespace App\Grants;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use Ramsey\Uuid\Uuid;

class CustomAccessTokenRepository implements AccessTokenRepositoryInterface
{
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null): \App\Grants\CustomAccessToken
    {
        Log::info('Entering getNewToken() method');
        Log::info('UserIdentifier: ' . ($userIdentifier ?? 'null'));
        $accessToken = new CustomAccessToken();
        $accessToken->setClient($clientEntity);
        $accessToken->setUserIdentifier($userIdentifier);
        $accessToken->setExpiryDateTime((new DateTimeImmutable())->modify('+1 hour'));

        foreach ($scopes as $scope) {
            $accessToken->addScope($scope);
        }

        return $accessToken;
    }

    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity): void
    {
        $tokenId = Uuid::uuid4()->toString();

        Log::info('Generated Token ID: ' . $tokenId);

        $accessTokenEntity->setIdentifier($tokenId);

        DB::table('oauth_access_tokens')->insert([
            'id' => $tokenId,
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

    public function getKeyContents(): bool|string
    {
        $privateKeyPath = storage_path('oauth-private.key');
        if (file_exists($privateKeyPath)) {
            return file_get_contents($privateKeyPath);
        }

        throw new \Exception("Private key not found at: " . $privateKeyPath);
    }
}
