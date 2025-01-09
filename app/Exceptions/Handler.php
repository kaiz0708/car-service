<?php

namespace App\Exceptions;

use App\DTO\ApiMessageDto;
use App\Response\CustomResponseMessage;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception):\Illuminate\Http\JsonResponse|\Illuminate\Http\Response|CustomResponseMessage
    {
        $response = new ApiMessageDto();
        $response->code = $exception->getCode();
        $response->message = $exception->getMessage();

        if ($exception instanceof CustomAuthenticationException) {
            return new CustomResponseMessage($response, $response->code);
        }
        return parent::render($request, $exception);
    }
}
