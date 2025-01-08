<?php

namespace App\DTO;

class ApiMessageDto
{
    public bool $result = true;
    public ?string $code = null;
    public mixed $data = null;
    public ?string $message = null;

    public function __get($name)
    {
        return $this->$name;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    /**
     * Convert the DTO to an array format.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'result' => $this->result,
            'code' => $this->code,
            'data' => $this->data,
            'message' => $this->message
        ];
    }
}
