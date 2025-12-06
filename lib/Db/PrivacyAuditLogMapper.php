<?php

declare(strict_types=1);

namespace OCA\Verein\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * Mapper für DSGVO-Audit-Log Einträge
 * @extends QBMapper<PrivacyAuditLog>
 */
class PrivacyAuditLogMapper extends QBMapper {

    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'verein_audit_log', PrivacyAuditLog::class);
    }

    /**
     * Erstellt einen neuen Audit-Log Eintrag
     */
    public function log(
        int $memberId,
        string $action,
        ?string $userId = null,
        array $details = [],
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): PrivacyAuditLog {
        $entry = new PrivacyAuditLog();
        $entry->setMemberId($memberId);
        $entry->setAction($action);
        $entry->setUserId($userId);
        $entry->setDetailsArray($details);
        $entry->setIpAddress($ipAddress);
        $entry->setUserAgent($userAgent);
        $entry->setCreatedAt(new \DateTime());

        return $this->insert($entry);
    }

    /**
     * Hole alle Audit-Logs für ein Mitglied
     * 
     * @return PrivacyAuditLog[]
     */
    public function findByMemberId(int $memberId, ?int $limit = null, ?int $offset = null): array {
        $qb = $this->db->getQueryBuilder();

        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('member_id', $qb->createNamedParameter($memberId, IQueryBuilder::PARAM_INT)))
            ->orderBy('created_at', 'DESC');

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }
        if ($offset !== null) {
            $qb->setFirstResult($offset);
        }

        return $this->findEntities($qb);
    }

    /**
     * Hole Audit-Logs nach Aktion
     * 
     * @return PrivacyAuditLog[]
     */
    public function findByAction(string $action, ?int $limit = 100): array {
        $qb = $this->db->getQueryBuilder();

        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('action', $qb->createNamedParameter($action)))
            ->orderBy('created_at', 'DESC');

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        return $this->findEntities($qb);
    }

    /**
     * Hole Audit-Logs für einen Zeitraum
     * 
     * @return PrivacyAuditLog[]
     */
    public function findByDateRange(\DateTime $from, \DateTime $to, ?int $limit = 1000): array {
        $qb = $this->db->getQueryBuilder();

        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->gte('created_at', $qb->createNamedParameter($from, IQueryBuilder::PARAM_DATE)))
            ->andWhere($qb->expr()->lte('created_at', $qb->createNamedParameter($to, IQueryBuilder::PARAM_DATE)))
            ->orderBy('created_at', 'DESC');

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        return $this->findEntities($qb);
    }

    /**
     * Hole alle Logs für einen bestimmten User
     * 
     * @return PrivacyAuditLog[]
     */
    public function findByUserId(string $userId, ?int $limit = 100): array {
        $qb = $this->db->getQueryBuilder();

        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
            ->orderBy('created_at', 'DESC');

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        return $this->findEntities($qb);
    }

    /**
     * Zähle Audit-Einträge für ein Mitglied
     */
    public function countByMemberId(int $memberId): int {
        $qb = $this->db->getQueryBuilder();

        $qb->select($qb->createFunction('COUNT(*)'))
            ->from($this->getTableName())
            ->where($qb->expr()->eq('member_id', $qb->createNamedParameter($memberId, IQueryBuilder::PARAM_INT)));

        $result = $qb->executeQuery();
        $count = (int)$result->fetchOne();
        $result->closeCursor();

        return $count;
    }

    /**
     * Suche nach Einträgen (für Admin-Übersicht)
     * 
     * @return PrivacyAuditLog[]
     */
    public function search(
        ?string $action = null,
        ?int $memberId = null,
        ?string $userId = null,
        ?\DateTime $from = null,
        ?\DateTime $to = null,
        int $limit = 100,
        int $offset = 0
    ): array {
        $qb = $this->db->getQueryBuilder();

        $qb->select('*')
            ->from($this->getTableName())
            ->orderBy('created_at', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        if ($action !== null) {
            $qb->andWhere($qb->expr()->eq('action', $qb->createNamedParameter($action)));
        }
        if ($memberId !== null) {
            $qb->andWhere($qb->expr()->eq('member_id', $qb->createNamedParameter($memberId, IQueryBuilder::PARAM_INT)));
        }
        if ($userId !== null) {
            $qb->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)));
        }
        if ($from !== null) {
            $qb->andWhere($qb->expr()->gte('created_at', $qb->createNamedParameter($from, IQueryBuilder::PARAM_DATE)));
        }
        if ($to !== null) {
            $qb->andWhere($qb->expr()->lte('created_at', $qb->createNamedParameter($to, IQueryBuilder::PARAM_DATE)));
        }

        return $this->findEntities($qb);
    }

    /**
     * Lösche alle Audit-Logs für ein Mitglied (nur bei Hard-Delete)
     */
    public function deleteByMemberId(int $memberId): int {
        $qb = $this->db->getQueryBuilder();

        $qb->delete($this->getTableName())
            ->where($qb->expr()->eq('member_id', $qb->createNamedParameter($memberId, IQueryBuilder::PARAM_INT)));

        return $qb->executeStatement();
    }

    /**
     * Statistiken für Admin-Dashboard
     */
    public function getStatistics(): array {
        $qb = $this->db->getQueryBuilder();

        // Anzahl pro Aktion
        $qb->select('action')
            ->addSelect($qb->createFunction('COUNT(*) as count'))
            ->from($this->getTableName())
            ->groupBy('action');

        $result = $qb->executeQuery();
        $actionCounts = [];
        while ($row = $result->fetch()) {
            $actionCounts[$row['action']] = (int)$row['count'];
        }
        $result->closeCursor();

        // Gesamtzahl
        $qb2 = $this->db->getQueryBuilder();
        $qb2->select($qb2->createFunction('COUNT(*)'))
            ->from($this->getTableName());
        $result2 = $qb2->executeQuery();
        $total = (int)$result2->fetchOne();
        $result2->closeCursor();

        // Letzte 30 Tage
        $thirtyDaysAgo = new \DateTime('-30 days');
        $qb3 = $this->db->getQueryBuilder();
        $qb3->select($qb3->createFunction('COUNT(*)'))
            ->from($this->getTableName())
            ->where($qb3->expr()->gte('created_at', $qb3->createNamedParameter($thirtyDaysAgo, IQueryBuilder::PARAM_DATE)));
        $result3 = $qb3->executeQuery();
        $last30Days = (int)$result3->fetchOne();
        $result3->closeCursor();

        return [
            'total' => $total,
            'last30Days' => $last30Days,
            'byAction' => $actionCounts,
        ];
    }
}
