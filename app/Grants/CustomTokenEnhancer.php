<?php

namespace App\Grants;

use Laravel\Passport\Bridge\AccessToken;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\CryptKey;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;

class CustomTokenEnhancer extends AccessToken
{
    protected $permissions;

    public function __construct(
        string $userIdentifier, 
        ClientEntityInterface $client,
        array $permissions = []
    ) {
        parent::__construct($userIdentifier, $permissions, $client);
        $this->permissions = $permissions;
    }

    public function setPermissions(array $permissions)
    {
        $this->permissions = $permissions;
        return $this;
    }

    public function convertToJWT(CryptKey $privateKey = null)
    {
        $config = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::file($privateKey->getKeyPath())
        );

        $now = new \DateTimeImmutable();
        $builder = $config->builder()
            ->issuedBy('your-issuer')
            ->permittedFor($this->getClient()->getIdentifier())
            ->identifiedBy($this->getIdentifier())
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($this->getExpiryDateTime())
            ->withClaim('scopes', $this->getScopes())
            ->withClaim('user_id', $this->getUserIdentifier())
            ->withClaim('permissions', $this->permissions);

        return $builder->getToken($config->signer(), $config->signingKey());
    }
}