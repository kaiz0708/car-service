<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class CustomAuthenticationException extends Exception
{
    protected $message;
    protected $code;

    public function __construct($message, $statusCode)
    {
        $this->message = $message;
        $this->code = $statusCode;
        parent::__construct($this->message, $this->code);
    }
}
