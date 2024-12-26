<?php
namespace App\Auth;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Signer\Key\InMemory;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Entities\ClientEntityInterface;

class CustomTokenEnhancer implements AccessTokenEntityInterface
{
    protected $jwtToken;

    public function enhance($accessToken): AccessTokenEntityInterface
    {
        // Cấu hình cho JWT
        $config = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText('your-signing-key')
        );

        $now = new \DateTimeImmutable();

        // Tạo JWT token
        $token = $config->builder()
            ->identifiedBy(bin2hex(random_bytes(16))) // Đặt một ID duy nhất cho token
            ->issuedAt($now) // Thời gian cấp token
            ->expiresAt($now->modify('+1 hour')) // Thời gian hết hạn token
            ->withClaim('roles', $accessToken->getUser()->roles ?? []) // Claim roles
            ->withClaim('client', $accessToken->getClient()->getIdentifier()) // Claim client
            ->getToken($config->signer(), $config->signingKey()); // Tạo JWT

        // Gán JWT vào entity
        $this->jwtToken = $token;

        // Trả về chính access token entity (không phải chuỗi)
        return $this;
    }

    // Phương thức để lấy JWT token
    public function getJwtToken(): Plain
    {
        return $this->jwtToken;
    }

    // Các method khác cần implement từ AccessTokenEntityInterface
    public function getUserIdentifier()
    {
        return 'user_id';
    }

    public function getClient()
    {
        return new class {
            public function getIdentifier()
            {
                return 'client_id';
            }
        };
    }

    public function setPrivateKey(CryptKey $privateKey)
    {
        $this->privateKey = $privateKey;
    }

    // Triển khai setClient
    public function setClient(ClientEntityInterface $client)
    {
        $this->client = $client;
    }

    // Triển khai __toString
    public function __toString()
    {
        return $this->jwtToken ? $this->jwtToken->toString() : '';
    }

    public function setIdentifier($identifier) {}
    public function getIdentifier() {}
    public function setExpiryDateTime(\DateTimeImmutable $expiry) {}
    public function getExpiryDateTime() {}
    public function setUserIdentifier($identifier) {}
    public function addScope($scope) {}
    public function getScopes() {}
    public function isExpired() {}
}


