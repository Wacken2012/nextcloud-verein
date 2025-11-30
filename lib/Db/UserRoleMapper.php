<?php
namespace OCA\Verein\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * UserRoleMapper - Data Access Layer für User-Rollen-Zuweisungen
 */
class UserRoleMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'verein_user_roles', UserRole::class);
    }

    /**
     * Hole eine User-Rollen-Zuordnung nach ID
     *
     * @throws DoesNotExistException
     * @throws MultipleObjectsReturnedException
     */
    public function find(int $id): UserRole {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));

        return $this->findEntity($qb);
    }

    /**
     * Hole alle Rollen eines Users
     *
     * @return UserRole[]
     */
    public function findByUserId(string $userId): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId, IQueryBuilder::PARAM_STR)));

        return $this->findEntities($qb);
    }

    /**
     * Hole alle Rollen eines Users für einen Club
     *
     * @return UserRole[]
     */
    public function findByUserAndClub(string $userId, int $clubId): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId, IQueryBuilder::PARAM_STR)))
            ->andWhere($qb->expr()->eq('club_id', $qb->createNamedParameter($clubId, IQueryBuilder::PARAM_INT)));

        return $this->findEntities($qb);
    }

    /**
     * Prüfe ob User-Rollen-Zuordnung existiert
     */
    public function existsForUserAndRole(string $userId, int $roleId, int $clubId): bool {
        $qb = $this->db->getQueryBuilder();
        $qb->select('id')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId, IQueryBuilder::PARAM_STR)))
            ->andWhere($qb->expr()->eq('role_id', $qb->createNamedParameter($roleId, IQueryBuilder::PARAM_INT)))
            ->andWhere($qb->expr()->eq('club_id', $qb->createNamedParameter($clubId, IQueryBuilder::PARAM_INT)))
            ->setMaxResults(1);

        try {
            $this->findEntity($qb);
            return true;
        } catch (DoesNotExistException $e) {
            return false;
        } catch (MultipleObjectsReturnedException $e) {
            return true;
        }
    }

    /**
     * Hole alle Zuordnungen für ein Club
     *
     * @return UserRole[]
     */
    public function findByClubId(int $clubId): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('club_id', $qb->createNamedParameter($clubId, IQueryBuilder::PARAM_INT)));

        return $this->findEntities($qb);
    }

    /**
     * Lösche alle Rollen eines Users für einen Club
     */
    public function deleteByUserAndClub(string $userId, int $clubId): int {
        $qb = $this->db->getQueryBuilder();
        $qb->delete($this->getTableName())
            ->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId, IQueryBuilder::PARAM_STR)))
            ->andWhere($qb->expr()->eq('club_id', $qb->createNamedParameter($clubId, IQueryBuilder::PARAM_INT)));

        return $qb->execute();
    }

    /**
     * Lösche alle Rollen eines Users (alle Clubs)
     */
    public function deleteByUser(string $userId): int {
        $qb = $this->db->getQueryBuilder();
        $qb->delete($this->getTableName())
            ->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId, IQueryBuilder::PARAM_STR)));

        return $qb->execute();
    }

    /**
     * Erstelle eine Zuordnung
     */
    public function insert(\OCP\AppFramework\Db\Entity $entity): \OCP\AppFramework\Db\Entity {
        $entity->setGrantedAt(date('Y-m-d H:i:s'));
        return parent::insert($entity);
    }
}
