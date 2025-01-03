<?php
namespace App\CustomAuthToken;

use DateTimeImmutable;
use Exception;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;

class CustomRefreshToken implements RefreshTokenEntityInterface
{
    use EntityTrait, TokenEntityTrait;

    protected array $claims = [];
    protected CustomAccessToken $accessToken;
    protected DateTimeImmutable $expiry;
    /**
     * @var true
     */
    private bool $revoked;

    public function setAccessToken(CustomAccessToken|AccessTokenEntityInterface $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    public function getAccessToken(): CustomAccessToken|AccessTokenEntityInterface
    {
        return $this->accessToken;
    }

    public function setClaims(array $claims): void
    {
        $this->claims = array_merge($this->claims, $claims);
    }

    public function getClaims(): array
    {
        return $this->claims;
    }

    public function setExpiryDateTime(DateTimeImmutable $expiry): void
    {
        $this->expiry = $expiry;
    }

    public function getExpiryDateTime(): DateTimeImmutable
    {
        return $this->expiry;
    }

    public function isExpired(): bool
    {
        return $this->expiry < new DateTimeImmutable();
    }

    public function revoke(): void
    {
        $this->revoked = true;
    }

    public function isRevoked(): bool
    {
        return $this->revoked;
    }

    public function getKeyContents():CryptKey
    {
        $privateKeyPath = storage_path("oauth-private.key");

        if (file_exists($privateKeyPath)) {
            return new CryptKey('file://' . $privateKeyPath);
        }

        throw new Exception("Private key not found at: " . $privateKeyPath);
    }

    /**
     * @throws Exception
     */
    public function convertToJWT(): \Lcobucci\JWT\UnencryptedToken
    {
        // Lấy private key
        $privateKey = $this->getKeyContents();

        $config = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::file($privateKey->getKeyPath())
        );

        $now = new DateTimeImmutable();

        $builder = $config->builder()
            ->issuedBy(config('app.url'))
            ->identifiedBy($this->getIdentifier(), true)
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($this->getExpiryDateTime())
            ->withClaim('user_id', $this->accessToken->getUserIdentifier());

        foreach ($this->getClaims() as $key => $value) {
            $builder = $builder->withClaim($key, $value);
        }

        return $builder->getToken($config->signer(), $config->signingKey());
    }

    /**
     * @throws Exception
     */
    public function getJWT(): string
    {
        return $this->convertToJWT()->toString();
    }
}

