<?php

namespace OCA\Verein\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\AppFramework\Db\Entity;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class RoleMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'verein_roles', Role::class);
    }

    public function find(int $id): Role {
        return parent::find($id);
    }

    public function findByName(string $name): Role {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('name', $qb->createNamedParameter($name, IQueryBuilder::PARAM_STR)))
            ->setMaxResults(1);

        return $this->findEntity($qb);
    }

    /**
     * @return Role[]
     */
    public function findAll(): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName())
            ->orderBy('name', 'ASC');

        return $this->findEntities($qb);
    }

    /**
     * @return Role[]
     */
    public function findByClubType(string $clubType): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('club_type', $qb->createNamedParameter($clubType, IQueryBuilder::PARAM_STR)))
            ->orderBy('name', 'ASC');

        return $this->findEntities($qb);
    }

    public function delete(Entity $role): Entity {
        return parent::delete($role);
    }

    public function insert(Entity $role): Entity {
        return parent::insert($role);
    }

    public function update(Entity $role): Entity {
        return parent::update($role);
    }
}
