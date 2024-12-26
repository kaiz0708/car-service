<?php

namespace App\Auth;

use Laravel\Passport\Bridge\AccessToken;

class CustomTokenConverter extends AccessToken
{
    public function convertToJWT($accessToken)
    {
        return $accessToken->withClaim('custom_data', [
            'device' => 'Web',
            'location' => 'VN'
        ]);
    }
}