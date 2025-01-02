<?php
namespace App\Grants;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;


class CustomAccessToken implements AccessTokenEntityInterface
{
    use AccessTokenTrait, EntityTrait, TokenEntityTrait;

    protected array $claims = [];
    protected $id;

    public function addClaims(array $claims): void
    {
        $this->claims = array_merge($this->claims, $claims);
    }

    public function getClaims(): array
    {
        return $this->claims;
    }

    public function setIdentifier($id): void
    {
        $this->id = $id;
    }

    // Thêm phương thức getIdentifier để lấy ID
    public function getIdentifier(): ?string
    {
        return $this->id;
    }
}

