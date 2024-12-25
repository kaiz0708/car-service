<?php
namespace App\Auth;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;

class CustomTokenEnhancer
{
    public function enhance($accessToken)
    {
        // Cấu hình cho JWT
        $config = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText('your-signing-key') // Chìa khóa bí mật cho việc ký JWT
        );

        $now = new \DateTimeImmutable();

        // Tạo JWT token
        $token = $config->builder()
            ->identifiedBy(bin2hex(random_bytes(16))) // Đặt một ID duy nhất cho token
            ->issuedAt($now) // Thời gian cấp token
            ->expiresAt($now->modify('+1 hour')) // Thời gian hết hạn token
            ->withClaim('roles', $accessToken->user->roles ?? []) // Claim roles
            ->withClaim('client', $accessToken->getClient()->getIdentifier()) // Claim client
            ->getToken($config->signer(), $config->signingKey()); // Tạo JWT

        // Trả về chuỗi JWT
        return $token->toString();
    }
}

