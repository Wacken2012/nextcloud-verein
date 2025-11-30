<?php
namespace OCA\Verein\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class MemberMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'verein_members', Member::class);
    }

    public function findAll(): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName());
        return $this->findEntities($qb);
    }

    public function search(string $query, int $limit = 50): array {
        $qb = $this->db->getQueryBuilder();
        $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $query) . '%';
        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->orX(
                $qb->expr()->like('name', $qb->createNamedParameter($like)),
                $qb->expr()->like('email', $qb->createNamedParameter($like))
            ))
            ->setMaxResults($limit);
        return $this->findEntities($qb);
    }

    public function find(int $id): Member {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('id', $qb->createNamedParameter($id)));
        return $this->findEntity($qb);
    }
}
