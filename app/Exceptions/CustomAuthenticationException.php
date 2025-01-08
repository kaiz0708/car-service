<?php

namespace App\Exceptions;

use App\DTO\ApiMessageDto;
use Exception;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class CustomAuthenticationException extends Exception
{
    protected $message;
    protected mixed $statusCode;

    public function __construct($message = "Unauthenticated", $statusCode = ResponseAlias::HTTP_UNAUTHORIZED)
    {
        $this->message = $message;
        $this->statusCode = $statusCode;

        parent::__construct($this->message);
    }
}
