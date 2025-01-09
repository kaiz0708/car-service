<?php

namespace App\Response;

use Illuminate\Http\JsonResponse;
use App\DTO\ApiMessageDto;

class CustomResponseMessage extends JsonResponse
{
    public function __construct(ApiMessageDto $apiMessageDto, int $status, array $headers = [], int $options = 0)
    {
        parent::__construct($apiMessageDto, $status, $headers, $options);
    }
}
