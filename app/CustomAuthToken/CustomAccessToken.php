<?php
namespace App\CustomAuthToken;

use DateTimeImmutable;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;


class CustomAccessToken implements AccessTokenEntityInterface
{
    use AccessTokenTrait, EntityTrait, TokenEntityTrait;

    protected array $claims = [];
    protected string $jwt;

    public function setClaims(array $claims): void
    {
        $this->claims = array_merge($this->claims, $claims);
    }

    public function getClaims(): array
    {
        return $this->claims;
    }

    public function getKeyContents(string $path):CryptKey
    {

        if (file_exists($path)) {
            return new CryptKey('file://' . $path);
        }

        throw new \Exception("Private key not found at: " . $path);
    }
    /**
     * @throws \Exception
     */
    public function convertToJWT(): \Lcobucci\JWT\UnencryptedToken
    {
        // Lấy private key
        $privateKey = $this->getKeyContents(storage_path('oauth-private.key'));
        $publicKey = $this->getKeyContents(storage_path('oauth-public.key'));

        $config = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::file($privateKey->getKeyPath()),
            InMemory::file($publicKey->getKeyPath())
        );

        $now = new DateTimeImmutable();

        $builder = $config->builder()
            ->issuedBy(config('app.url'))
            ->identifiedBy($this->getIdentifier(), true)
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($this->getExpiryDateTime())
            ->withClaim('user_id', $this->getUserIdentifier());

        foreach ($this->getClaims() as $key => $value) {
            $builder = $builder->withClaim($key, $value);
        }

        return $builder->getToken($config->signer(), $config->signingKey());
    }

    /**
     * @throws \Exception
     */
    public function getJWT(): string
    {
        return $this->convertToJWT()->toString();
    }
}




