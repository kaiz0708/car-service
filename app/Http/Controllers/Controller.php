<?php

namespace App\Http\Controllers;

use App\CustomAuthToken\TokenContext;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Lấy Bearer Token từ request
     *
     *
     * @return string|null
     */
    protected function getCurrentToken(): ?string
    {
        return TokenContext::getToken();
    }

}
