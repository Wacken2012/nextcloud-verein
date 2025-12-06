<?php

declare(strict_types=1);

namespace OCA\Verein\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * Mapper for Event entities
 */
class EventMapper extends QBMapper {

	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'verein_events', Event::class);
	}

	/**
	 * Find event by ID
	 */
	public function find(int $id): Event {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));
		
		return $this->findEntity($qb);
	}

	/**
	 * Find all events
	 */
	public function findAll(int $limit = null, int $offset = null): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->orderBy('start_date', 'ASC');
		
		if ($limit !== null) {
			$qb->setMaxResults($limit);
		}
		if ($offset !== null) {
			$qb->setFirstResult($offset);
		}
		
		return $this->findEntities($qb);
	}

	/**
	 * Find events in date range
	 */
	public function findByDateRange(\DateTime $from, \DateTime $to): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->gte('start_date', $qb->createNamedParameter($from->format('Y-m-d H:i:s'))))
			->andWhere($qb->expr()->lte('start_date', $qb->createNamedParameter($to->format('Y-m-d H:i:s'))))
			->orderBy('start_date', 'ASC');
		
		return $this->findEntities($qb);
	}

	/**
	 * Find upcoming events
	 */
	public function findUpcoming(int $limit = 10): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->gte('start_date', $qb->createNamedParameter((new \DateTime())->format('Y-m-d H:i:s'))))
			->orderBy('start_date', 'ASC')
			->setMaxResults($limit);
		
		return $this->findEntities($qb);
	}

	/**
	 * Find events by type
	 */
	public function findByType(string $eventType): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('event_type', $qb->createNamedParameter($eventType)))
			->orderBy('start_date', 'ASC');
		
		return $this->findEntities($qb);
	}

	/**
	 * Find events by creator
	 */
	public function findByCreator(string $userId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('created_by', $qb->createNamedParameter($userId)))
			->orderBy('start_date', 'DESC');
		
		return $this->findEntities($qb);
	}

	/**
	 * Find events with RSVP enabled
	 */
	public function findWithRsvp(): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('rsvp_enabled', $qb->createNamedParameter(1, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->gte('start_date', $qb->createNamedParameter((new \DateTime())->format('Y-m-d H:i:s'))))
			->orderBy('start_date', 'ASC');
		
		return $this->findEntities($qb);
	}

	/**
	 * Count all events
	 */
	public function countAll(): int {
		$qb = $this->db->getQueryBuilder();
		$qb->select($qb->createFunction('COUNT(*)'))
			->from($this->getTableName());
		
		$result = $qb->executeQuery();
		$count = (int) $result->fetchOne();
		$result->closeCursor();
		
		return $count;
	}

	/**
	 * Get events for month view
	 */
	public function findByMonth(int $year, int $month): array {
		$firstDay = new \DateTime("$year-$month-01 00:00:00");
		$lastDay = (clone $firstDay)->modify('last day of this month')->setTime(23, 59, 59);
		
		return $this->findByDateRange($firstDay, $lastDay);
	}

	/**
	 * Delete event by ID
	 */
	public function deleteById(int $id): void {
		$event = $this->find($id);
		$this->delete($event);
	}
}
