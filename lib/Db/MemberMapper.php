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

    public function find(int $id): Member {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('id', $qb->createNamedParameter($id)));
        return $this->findEntity($qb);
    }
}
