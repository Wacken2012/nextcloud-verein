<?php

declare(strict_types=1);

namespace OCA\Verein\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * Mapper für Einwilligungen (Consent)
 * @extends QBMapper<Consent>
 */
class ConsentMapper extends QBMapper {

    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'verein_consents', Consent::class);
    }

    /**
     * Hole Einwilligung für einen bestimmten Typ
     */
    public function findByMemberAndType(int $memberId, string $consentType): ?Consent {
        $qb = $this->db->getQueryBuilder();

        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('member_id', $qb->createNamedParameter($memberId, IQueryBuilder::PARAM_INT)))
            ->andWhere($qb->expr()->eq('consent_type', $qb->createNamedParameter($consentType)));

        try {
            return $this->findEntity($qb);
        } catch (DoesNotExistException $e) {
            return null;
        }
    }

    /**
     * Hole alle Einwilligungen für ein Mitglied
     * 
     * @return Consent[]
     */
    public function findByMemberId(int $memberId): array {
        $qb = $this->db->getQueryBuilder();

        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('member_id', $qb->createNamedParameter($memberId, IQueryBuilder::PARAM_INT)))
            ->orderBy('consent_type', 'ASC');

        return $this->findEntities($qb);
    }

    /**
     * Setze oder aktualisiere eine Einwilligung
     */
    public function setConsent(
        int $memberId,
        string $consentType,
        bool $given,
        ?string $ipAddress = null,
        string $source = Consent::SOURCE_WEB
    ): Consent {
        $existing = $this->findByMemberAndType($memberId, $consentType);

        if ($existing !== null) {
            // Update
            $existing->setGiven($given);
            if ($given) {
                $existing->setGivenAt(new \DateTime());
                $existing->setRevokedAt(null);
            } else {
                $existing->setRevokedAt(new \DateTime());
            }
            $existing->setIpAddress($ipAddress);
            $existing->setSource($source);
            
            return $this->update($existing);
        }

        // Neu erstellen
        $consent = new Consent();
        $consent->setMemberId($memberId);
        $consent->setConsentType($consentType);
        $consent->setGiven($given);
        $consent->setIpAddress($ipAddress);
        $consent->setSource($source);
        
        if ($given) {
            $consent->setGivenAt(new \DateTime());
        }

        return $this->insert($consent);
    }

    /**
     * Hole alle Mitglieder mit einer bestimmten Einwilligung
     * 
     * @return int[] Member IDs
     */
    public function findMemberIdsWithConsent(string $consentType): array {
        $qb = $this->db->getQueryBuilder();

        $qb->select('member_id')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('consent_type', $qb->createNamedParameter($consentType)))
            ->andWhere($qb->expr()->eq('given', $qb->createNamedParameter(1, IQueryBuilder::PARAM_INT)));

        $result = $qb->executeQuery();
        $memberIds = [];
        while ($row = $result->fetch()) {
            $memberIds[] = (int)$row['member_id'];
        }
        $result->closeCursor();

        return $memberIds;
    }

    /**
     * Zähle Einwilligungen pro Typ (für Statistiken)
     */
    public function countByType(): array {
        $qb = $this->db->getQueryBuilder();

        $qb->select('consent_type')
            ->addSelect($qb->createFunction('SUM(CASE WHEN given = 1 THEN 1 ELSE 0 END) as given_count'))
            ->addSelect($qb->createFunction('SUM(CASE WHEN given = 0 THEN 1 ELSE 0 END) as revoked_count'))
            ->from($this->getTableName())
            ->groupBy('consent_type');

        $result = $qb->executeQuery();
        $stats = [];
        while ($row = $result->fetch()) {
            $stats[$row['consent_type']] = [
                'given' => (int)$row['given_count'],
                'revoked' => (int)$row['revoked_count'],
            ];
        }
        $result->closeCursor();

        return $stats;
    }

    /**
     * Lösche alle Einwilligungen für ein Mitglied
     */
    public function deleteByMemberId(int $memberId): int {
        $qb = $this->db->getQueryBuilder();

        $qb->delete($this->getTableName())
            ->where($qb->expr()->eq('member_id', $qb->createNamedParameter($memberId, IQueryBuilder::PARAM_INT)));

        return $qb->executeStatement();
    }

    /**
     * Hole alle Einwilligungen als Map für ein Mitglied
     * Nützlich für schnellen Zugriff im Frontend
     */
    public function getConsentsMap(int $memberId): array {
        $consents = $this->findByMemberId($memberId);
        $map = [];
        
        // Initialisiere mit allen bekannten Typen
        foreach (Consent::getAllTypes() as $type => $label) {
            $map[$type] = [
                'given' => false,
                'label' => $label,
                'description' => Consent::getTypeDescriptions()[$type] ?? '',
                'givenAt' => null,
                'revokedAt' => null,
            ];
        }

        // Überschreibe mit tatsächlichen Werten
        foreach ($consents as $consent) {
            $map[$consent->getConsentType()] = [
                'given' => $consent->getGiven(),
                'label' => $consent->getConsentTypeLabel(),
                'description' => Consent::getTypeDescriptions()[$consent->getConsentType()] ?? '',
                'givenAt' => $consent->getGivenAt()?->format('c'),
                'revokedAt' => $consent->getRevokedAt()?->format('c'),
            ];
        }

        return $map;
    }

    /**
     * Massenaktualisierung für Consent-Typen (z.B. beim Admin-Import)
     */
    public function bulkSetConsent(
        array $memberIds,
        string $consentType,
        bool $given,
        string $source = Consent::SOURCE_IMPORT
    ): int {
        $updated = 0;
        foreach ($memberIds as $memberId) {
            $this->setConsent((int)$memberId, $consentType, $given, null, $source);
            $updated++;
        }
        return $updated;
    }
}
