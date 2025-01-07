<?php

namespace App\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class PreAuthorize
{
    public function __construct(public string $permission)
    {

    }
}
