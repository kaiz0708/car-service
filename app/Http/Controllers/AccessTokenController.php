<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Passport\Http\Controllers\AccessTokenController as PassportAccessTokenController;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequestFactory;
use Psr\Http\Message\ServerRequestInterface;

class AccessTokenController extends PassportAccessTokenController
{
    public function issueToken(ServerRequestInterface $request)
    {
      echo "kaskaskas";
      return parent::issueToken($request);
    }
}
