<?php

declare(strict_types=1);

namespace OCA\Verein\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * Mapper for EventRsvp entities
 */
class EventRsvpMapper extends QBMapper {

	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'verein_event_rsvp', EventRsvp::class);
	}

	/**
	 * Find RSVP by ID
	 */
	public function find(int $id): EventRsvp {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));
		
		return $this->findEntity($qb);
	}

	/**
	 * Find all RSVPs for an event
	 */
	public function findByEvent(int $eventId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('event_id', $qb->createNamedParameter($eventId, IQueryBuilder::PARAM_INT)))
			->orderBy('responded_at', 'ASC');
		
		return $this->findEntities($qb);
	}

	/**
	 * Find RSVP by event and user
	 */
	public function findByEventAndUser(int $eventId, string $userId): ?EventRsvp {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('event_id', $qb->createNamedParameter($eventId, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)));
		
		try {
			return $this->findEntity($qb);
		} catch (\OCP\AppFramework\Db\DoesNotExistException $e) {
			return null;
		}
	}

	/**
	 * Find all RSVPs by user
	 */
	public function findByUser(string $userId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->orderBy('responded_at', 'DESC');
		
		return $this->findEntities($qb);
	}

	/**
	 * Count responses by type for an event
	 */
	public function countByResponse(int $eventId, string $response): int {
		$qb = $this->db->getQueryBuilder();
		$qb->select($qb->createFunction('COUNT(*)'))
			->from($this->getTableName())
			->where($qb->expr()->eq('event_id', $qb->createNamedParameter($eventId, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->eq('response', $qb->createNamedParameter($response)));
		
		$result = $qb->executeQuery();
		$count = (int) $result->fetchOne();
		$result->closeCursor();
		
		return $count;
	}

	/**
	 * Get RSVP statistics for an event
	 */
	public function getEventStatistics(int $eventId): array {
		return [
			'yes' => $this->countByResponse($eventId, EventRsvp::RESPONSE_YES),
			'no' => $this->countByResponse($eventId, EventRsvp::RESPONSE_NO),
			'maybe' => $this->countByResponse($eventId, EventRsvp::RESPONSE_MAYBE),
			'pending' => $this->countByResponse($eventId, EventRsvp::RESPONSE_PENDING),
		];
	}

	/**
	 * Delete all RSVPs for an event
	 */
	public function deleteByEvent(int $eventId): void {
		$qb = $this->db->getQueryBuilder();
		$qb->delete($this->getTableName())
			->where($qb->expr()->eq('event_id', $qb->createNamedParameter($eventId, IQueryBuilder::PARAM_INT)));
		$qb->executeStatement();
	}

	/**
	 * Find RSVPs with positive responses for an event
	 */
	public function findAttending(int $eventId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('event_id', $qb->createNamedParameter($eventId, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->eq('response', $qb->createNamedParameter(EventRsvp::RESPONSE_YES)))
			->orderBy('responded_at', 'ASC');
		
		return $this->findEntities($qb);
	}
}
