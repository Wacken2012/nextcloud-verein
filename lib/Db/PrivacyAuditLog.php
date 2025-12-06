<?php

declare(strict_types=1);

namespace OCA\Verein\Db;

use OCP\AppFramework\Db\Entity;

/**
 * Entity für DSGVO-Audit-Log Einträge
 * 
 * @method int getMemberId()
 * @method void setMemberId(int $memberId)
 * @method string|null getUserId()
 * @method void setUserId(?string $userId)
 * @method string getAction()
 * @method void setAction(string $action)
 * @method string|null getDetails()
 * @method void setDetails(?string $details)
 * @method string|null getIpAddress()
 * @method void setIpAddress(?string $ipAddress)
 * @method string|null getUserAgent()
 * @method void setUserAgent(?string $userAgent)
 * @method \DateTime getCreatedAt()
 * @method void setCreatedAt(\DateTime $createdAt)
 */
class PrivacyAuditLog extends Entity {
    
    /** @var int */
    protected $memberId;
    
    /** @var string|null */
    protected $userId;
    
    /** @var string */
    protected $action;
    
    /** @var string|null */
    protected $details;
    
    /** @var string|null */
    protected $ipAddress;
    
    /** @var string|null */
    protected $userAgent;
    
    /** @var \DateTime */
    protected $createdAt;

    // Konstanten für Aktionstypen
    public const ACTION_EXPORT = 'export';
    public const ACTION_DELETE_SOFT = 'delete_soft';
    public const ACTION_DELETE_HARD = 'delete_hard';
    public const ACTION_CONSENT_GIVEN = 'consent_given';
    public const ACTION_CONSENT_REVOKED = 'consent_revoked';
    public const ACTION_VIEW = 'view';
    public const ACTION_EDIT = 'edit';
    public const ACTION_DELETE_REQUEST = 'delete_request';
    public const ACTION_DELETE_APPROVED = 'delete_approved';
    public const ACTION_DELETE_REJECTED = 'delete_rejected';

    public function __construct() {
        $this->addType('memberId', 'integer');
        $this->addType('createdAt', 'datetime');
    }

    /**
     * Hilfsmethode zum Setzen von Details als Array
     */
    public function setDetailsArray(array $details): void {
        $this->setDetails(json_encode($details, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Hilfsmethode zum Lesen von Details als Array
     */
    public function getDetailsArray(): array {
        $details = $this->getDetails();
        if (empty($details)) {
            return [];
        }
        return json_decode($details, true) ?? [];
    }

    /**
     * Formatierte Darstellung für UI
     */
    public function toArray(): array {
        return [
            'id' => $this->getId(),
            'memberId' => $this->getMemberId(),
            'userId' => $this->getUserId(),
            'action' => $this->getAction(),
            'actionLabel' => $this->getActionLabel(),
            'details' => $this->getDetailsArray(),
            'ipAddress' => $this->getIpAddress(),
            'userAgent' => $this->getUserAgent(),
            'createdAt' => $this->getCreatedAt()?->format('c'),
        ];
    }

    /**
     * Menschenlesbare Aktionsbezeichnung
     */
    public function getActionLabel(): string {
        $labels = [
            self::ACTION_EXPORT => 'Datenexport',
            self::ACTION_DELETE_SOFT => 'Anonymisierung',
            self::ACTION_DELETE_HARD => 'Löschung',
            self::ACTION_CONSENT_GIVEN => 'Einwilligung erteilt',
            self::ACTION_CONSENT_REVOKED => 'Einwilligung widerrufen',
            self::ACTION_VIEW => 'Dateneinsicht',
            self::ACTION_EDIT => 'Datenänderung',
            self::ACTION_DELETE_REQUEST => 'Löschanfrage gestellt',
            self::ACTION_DELETE_APPROVED => 'Löschanfrage genehmigt',
            self::ACTION_DELETE_REJECTED => 'Löschanfrage abgelehnt',
        ];

        return $labels[$this->getAction()] ?? $this->getAction();
    }
}
