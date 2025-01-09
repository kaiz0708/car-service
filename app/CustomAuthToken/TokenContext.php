<?php

namespace App\CustomAuthToken;

class TokenContext
{
    private static ?string $token = null;

    /**
     * Lưu token vào context
     *
     * @param string $token
     * @return void
     */
    public static function setToken(string $token): void
    {
        self::$token = $token;
    }

    /**
     * Lấy token từ context
     *
     * @return string|null
     */
    public static function getToken(): ?string
    {
        return self::$token;
    }
}
