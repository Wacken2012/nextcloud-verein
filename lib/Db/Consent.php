<?php

declare(strict_types=1);

namespace OCA\Verein\Db;

use OCP\AppFramework\Db\Entity;

/**
 * Entity für Einwilligungen (DSGVO Consent)
 * 
 * @method int getMemberId()
 * @method void setMemberId(int $memberId)
 * @method string getConsentType()
 * @method void setConsentType(string $consentType)
 * @method bool getGiven()
 * @method void setGiven(bool $given)
 * @method \DateTime|null getGivenAt()
 * @method void setGivenAt(?\DateTime $givenAt)
 * @method \DateTime|null getRevokedAt()
 * @method void setRevokedAt(?\DateTime $revokedAt)
 * @method string|null getIpAddress()
 * @method void setIpAddress(?string $ipAddress)
 * @method string|null getSource()
 * @method void setSource(?string $source)
 */
class Consent extends Entity {
    
    /** @var int */
    protected $memberId;
    
    /** @var string */
    protected $consentType;
    
    /** @var int */
    protected $given = 0;
    
    /** @var \DateTime|null */
    protected $givenAt;
    
    /** @var \DateTime|null */
    protected $revokedAt;
    
    /** @var string|null */
    protected $ipAddress;
    
    /** @var string|null */
    protected $source;

    // Vordefinierte Consent-Typen
    public const TYPE_NEWSLETTER = 'newsletter';
    public const TYPE_MARKETING = 'marketing';
    public const TYPE_ANALYTICS = 'analytics';
    public const TYPE_PARTNERS = 'partners';
    public const TYPE_PHOTOS = 'photos';
    public const TYPE_INTERNAL_COMMUNICATION = 'internal_communication';
    public const TYPE_BIRTHDAY_LIST = 'birthday_list';
    public const TYPE_MEMBER_DIRECTORY = 'member_directory';

    // Quellen
    public const SOURCE_WEB = 'web';
    public const SOURCE_API = 'api';
    public const SOURCE_IMPORT = 'import';
    public const SOURCE_PAPER = 'paper';

    public function __construct() {
        $this->addType('memberId', 'integer');
        $this->addType('given', 'integer');
        $this->addType('givenAt', 'datetime');
        $this->addType('revokedAt', 'datetime');
    }

    /**
     * Getter für given als boolean
     */
    public function getGiven(): bool {
        return (bool)$this->given;
    }

    /**
     * Setter für given als boolean
     */
    public function setGiven(bool $given): void {
        $this->given = $given ? 1 : 0;
        $this->markFieldUpdated('given');
    }

    /**
     * Formatierte Darstellung für UI
     */
    public function toArray(): array {
        return [
            'id' => $this->getId(),
            'memberId' => $this->getMemberId(),
            'consentType' => $this->getConsentType(),
            'consentTypeLabel' => $this->getConsentTypeLabel(),
            'given' => $this->getGiven(),
            'givenAt' => $this->getGivenAt()?->format('c'),
            'revokedAt' => $this->getRevokedAt()?->format('c'),
            'source' => $this->getSource(),
            'sourceLabel' => $this->getSourceLabel(),
        ];
    }

    /**
     * Menschenlesbare Bezeichnung für den Consent-Typ
     */
    public function getConsentTypeLabel(): string {
        $labels = [
            self::TYPE_NEWSLETTER => 'Newsletter',
            self::TYPE_MARKETING => 'Marketing und Werbung',
            self::TYPE_ANALYTICS => 'Anonyme Nutzungsstatistiken',
            self::TYPE_PARTNERS => 'Weitergabe an Partner',
            self::TYPE_PHOTOS => 'Veröffentlichung von Fotos',
            self::TYPE_INTERNAL_COMMUNICATION => 'Interne Vereinskommunikation',
            self::TYPE_BIRTHDAY_LIST => 'Geburtstagsliste',
            self::TYPE_MEMBER_DIRECTORY => 'Mitgliederverzeichnis',
        ];

        return $labels[$this->getConsentType()] ?? $this->getConsentType();
    }

    /**
     * Menschenlesbare Bezeichnung für die Quelle
     */
    public function getSourceLabel(): string {
        $labels = [
            self::SOURCE_WEB => 'Online',
            self::SOURCE_API => 'API',
            self::SOURCE_IMPORT => 'Datenimport',
            self::SOURCE_PAPER => 'Papierformular',
        ];

        return $labels[$this->getSource()] ?? ($this->getSource() ?? 'Unbekannt');
    }

    /**
     * Gibt alle verfügbaren Consent-Typen zurück
     */
    public static function getAllTypes(): array {
        return [
            self::TYPE_NEWSLETTER => 'Newsletter',
            self::TYPE_MARKETING => 'Marketing und Werbung',
            self::TYPE_ANALYTICS => 'Anonyme Nutzungsstatistiken',
            self::TYPE_PARTNERS => 'Weitergabe an Partner',
            self::TYPE_PHOTOS => 'Veröffentlichung von Fotos',
            self::TYPE_INTERNAL_COMMUNICATION => 'Interne Vereinskommunikation',
            self::TYPE_BIRTHDAY_LIST => 'Geburtstagsliste',
            self::TYPE_MEMBER_DIRECTORY => 'Mitgliederverzeichnis',
        ];
    }

    /**
     * Beschreibungen für die Consent-Typen (für UI)
     */
    public static function getTypeDescriptions(): array {
        return [
            self::TYPE_NEWSLETTER => 'Ich möchte den Vereinsnewsletter per E-Mail erhalten.',
            self::TYPE_MARKETING => 'Meine Daten dürfen für Werbung und Marketing verwendet werden.',
            self::TYPE_ANALYTICS => 'Anonymisierte Nutzungsdaten dürfen für Statistiken verwendet werden.',
            self::TYPE_PARTNERS => 'Meine Daten dürfen an Partnerorganisationen weitergegeben werden.',
            self::TYPE_PHOTOS => 'Fotos von mir dürfen auf der Vereinswebsite und in sozialen Medien veröffentlicht werden.',
            self::TYPE_INTERNAL_COMMUNICATION => 'Ich möchte über Vereinsaktivitäten per E-Mail informiert werden.',
            self::TYPE_BIRTHDAY_LIST => 'Mein Geburtstag darf in der internen Geburtstagsliste erscheinen.',
            self::TYPE_MEMBER_DIRECTORY => 'Meine Kontaktdaten dürfen im internen Mitgliederverzeichnis erscheinen.',
        ];
    }
}
