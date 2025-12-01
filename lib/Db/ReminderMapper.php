<?php

declare(strict_types=1);

namespace OCA\Verein\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class ReminderMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'nextcloud_verein_reminders');
	}

	/**
	 * Finde alle ausstehenden Mahnungen
	 */
	public function findPending(): array {
		$qb = $this->db->getQueryBuilder();

		return $this->findEntities(
			$qb->select('*')
				->from($this->getTableName())
				->where($qb->expr()->eq('status', $qb->createNamedParameter('pending')))
				->orderBy('next_reminder_date', 'ASC')
		);
	}

	/**
	 * Finde Mahnung nach ID
	 */
	public function findById(int $id) {
		$qb = $this->db->getQueryBuilder();

		return $this->findEntity(
			$qb->select('*')
				->from($this->getTableName())
				->where($qb->expr()->eq('id', $qb->createNamedParameter($id)))
		);
	}

	/**
	 * Finde Mahnungen für Mitglied
	 */
	public function findByMemberId(int $memberId): array {
		$qb = $this->db->getQueryBuilder();

		return $this->findEntities(
			$qb->select('*')
				->from($this->getTableName())
				->where($qb->expr()->eq('member_id', $qb->createNamedParameter($memberId)))
				->orderBy('due_date', 'DESC')
		);
	}

	/**
	 * Finde Mahnungen für Gebühr
	 */
	public function findByFeeId(int $feeId): array {
		$qb = $this->db->getQueryBuilder();

		return $this->findEntities(
			$qb->select('*')
				->from($this->getTableName())
				->where($qb->expr()->eq('fee_id', $qb->createNamedParameter($feeId)))
				->orderBy('created_at', 'DESC')
		);
	}

	/**
	 * Finde fällige Mahnungen nach Status
	 */
	public function findByStatus(string $status): array {
		$qb = $this->db->getQueryBuilder();

		return $this->findEntities(
			$qb->select('*')
				->from($this->getTableName())
				->where($qb->expr()->eq('status', $qb->createNamedParameter($status)))
				->orderBy('due_date', 'ASC')
		);
	}

	/**
	 * Zähle offene Mahnungen für Mitglied
	 */
	public function countOpenForMember(int $memberId): int {
		$qb = $this->db->getQueryBuilder();

		$result = $qb->select($qb->func()->count('id', 'count'))
			->from($this->getTableName())
			->where($qb->expr()->eq('member_id', $qb->createNamedParameter($memberId)))
			->andWhere($qb->expr()->eq('status', $qb->createNamedParameter('pending')))
			->execute();

		return (int)$result->fetchOne()['count'];
	}
}
