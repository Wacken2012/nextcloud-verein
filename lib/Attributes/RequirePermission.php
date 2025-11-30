<?php

namespace OCA\Verein\Attributes;

use Attribute;

/**
 * PHP Attribute for declaring required permissions on controllers or methods.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class RequirePermission {
    private string $permission;

    public function __construct(string $permission) {
        $this->permission = $permission;
    }

    public function getPermission(): string {
        return $this->permission;
    }
}
