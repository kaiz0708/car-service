<?php

namespace App\Auth;

use Laravel\Passport\Bridge\AccessToken;
use Lcobucci\JWT\Builder;

class CustomTokenConverter extends AccessToken
{
    public function convertToJWT($accessToken)
    {
        $jwt = parent::convertToJWT($accessToken);

        return $jwt->withClaim('custom_data', [
            'device' => 'Web',
            'location' => 'VN'
        ]);
    }
}