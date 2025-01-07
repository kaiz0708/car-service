<?php
namespace App\CustomAuthToken;

use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use Ramsey\Uuid\Uuid;

class CustomRefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    /**
     * @throws \DateMalformedStringException
     * @throws \Exception
     */
    public function getNewRefreshToken(): CustomRefreshToken
    {
        $refreshToken = new CustomRefreshToken();
        $tokenId = Uuid::uuid4()->toString();
        $refreshToken->setIdentifier($tokenId);
        $refreshToken->setExpiryDateTime((new DateTimeImmutable())->modify('+1 month'));
        return $refreshToken;
    }

    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity): void
    {
        DB::table('oauth_refresh_tokens')->insert([
            'id' => $refreshTokenEntity->getIdentifier(),
            'access_token_id' => $refreshTokenEntity->getAccessToken()->getIdentifier(),
            'revoked' => false,
            'expires_at' => $refreshTokenEntity->getExpiryDateTime()->format('Y-m-d H:i:s'),
        ]);
    }

    public function revokeRefreshToken($tokenId): void
    {
        DB::table('oauth_refresh_tokens')
            ->where('id', $tokenId)
            ->update(['revoked' => true]);
    }

    public function isRefreshTokenRevoked($tokenId): bool
    {
        $token = DB::table('oauth_refresh_tokens')
            ->where('id', $tokenId)
            ->first();

        return $token ? (bool) $token->revoked : true;
    }
}
