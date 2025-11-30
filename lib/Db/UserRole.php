<?php

namespace OCA\Verein\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class UserRole extends Entity implements JsonSerializable {
    protected ?string $userId = null;
    protected ?int $roleId = null;
    protected ?int $clubId = null;
    protected ?string $grantedBy = null;
    protected ?string $grantedAt = null;

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('userId', 'string');
        $this->addType('roleId', 'integer');
        $this->addType('clubId', 'integer');
        $this->addType('grantedBy', 'string');
        $this->addType('grantedAt', 'string');
    }

    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'roleId' => $this->roleId,
            'clubId' => $this->clubId,
            'grantedBy' => $this->grantedBy,
            'grantedAt' => $this->grantedAt,
        ];
    }
}
