<?php

declare(strict_types=1);

namespace OCA\Verein\Service;

use OCA\Verein\Db\Event;
use OCA\Verein\Db\EventMapper;
use OCA\Verein\Db\EventRsvp;
use OCA\Verein\Db\EventRsvpMapper;
use OCP\IUserSession;

/**
 * Service for calendar event management
 */
class CalendarService {

	public function __construct(
		private EventMapper $eventMapper,
		private EventRsvpMapper $rsvpMapper,
		private ?IUserSession $userSession = null,
	) {
	}

	/**
	 * Get all events
	 */
	public function getEvents(int $limit = null, int $offset = null): array {
		$events = $this->eventMapper->findAll($limit, $offset);
		return array_map(fn(Event $e) => $this->enrichEventWithRsvp($e), $events);
	}

	/**
	 * Get events for a specific month
	 */
	public function getEventsByMonth(int $year, int $month): array {
		$events = $this->eventMapper->findByMonth($year, $month);
		return array_map(fn(Event $e) => $this->enrichEventWithRsvp($e), $events);
	}

	/**
	 * Get events in date range
	 */
	public function getEventsByDateRange(\DateTime $from, \DateTime $to): array {
		$events = $this->eventMapper->findByDateRange($from, $to);
		return array_map(fn(Event $e) => $this->enrichEventWithRsvp($e), $events);
	}

	/**
	 * Get upcoming events
	 */
	public function getUpcomingEvents(int $limit = 10): array {
		$events = $this->eventMapper->findUpcoming($limit);
		return array_map(fn(Event $e) => $this->enrichEventWithRsvp($e), $events);
	}

	/**
	 * Get event by ID
	 */
	public function getEvent(int $id): array {
		$event = $this->eventMapper->find($id);
		return $this->enrichEventWithRsvp($event);
	}

	/**
	 * Create a new event
	 */
	public function createEvent(array $data): Event {
		$event = new Event();
		$event->setTitle($data['title']);
		$event->setDescription($data['description'] ?? null);
		$event->setLocation($data['location'] ?? null);
		$event->setEventType($data['eventType'] ?? 'event');
		$event->setStartDate(new \DateTime($data['startDate']));
		
		if (!empty($data['endDate'])) {
			$event->setEndDate(new \DateTime($data['endDate']));
		}
		
		$event->setAllDay(($data['allDay'] ?? false) ? 1 : 0);
		$event->setRecurring($data['recurring'] ?? null);
		
		if (!empty($data['recurringUntil'])) {
			$event->setRecurringUntil(new \DateTime($data['recurringUntil']));
		}
		
		$event->setRsvpEnabled(($data['rsvpEnabled'] ?? false) ? 1 : 0);
		
		if (!empty($data['rsvpDeadline'])) {
			$event->setRsvpDeadline(new \DateTime($data['rsvpDeadline']));
		}
		
		$event->setMaxParticipants($data['maxParticipants'] ?? null);
		$event->setCreatedBy($this->getCurrentUserId());
		$event->setCreatedAt(new \DateTime());
		
		return $this->eventMapper->insert($event);
	}

	/**
	 * Update an event
	 */
	public function updateEvent(int $id, array $data): Event {
		$event = $this->eventMapper->find($id);
		
		if (isset($data['title'])) {
			$event->setTitle($data['title']);
		}
		if (array_key_exists('description', $data)) {
			$event->setDescription($data['description']);
		}
		if (array_key_exists('location', $data)) {
			$event->setLocation($data['location']);
		}
		if (isset($data['eventType'])) {
			$event->setEventType($data['eventType']);
		}
		if (isset($data['startDate'])) {
			$event->setStartDate(new \DateTime($data['startDate']));
		}
		if (array_key_exists('endDate', $data)) {
			$event->setEndDate($data['endDate'] ? new \DateTime($data['endDate']) : null);
		}
		if (isset($data['allDay'])) {
			$event->setAllDay($data['allDay'] ? 1 : 0);
		}
		if (array_key_exists('recurring', $data)) {
			$event->setRecurring($data['recurring']);
		}
		if (array_key_exists('recurringUntil', $data)) {
			$event->setRecurringUntil($data['recurringUntil'] ? new \DateTime($data['recurringUntil']) : null);
		}
		if (isset($data['rsvpEnabled'])) {
			$event->setRsvpEnabled($data['rsvpEnabled'] ? 1 : 0);
		}
		if (array_key_exists('rsvpDeadline', $data)) {
			$event->setRsvpDeadline($data['rsvpDeadline'] ? new \DateTime($data['rsvpDeadline']) : null);
		}
		if (array_key_exists('maxParticipants', $data)) {
			$event->setMaxParticipants($data['maxParticipants']);
		}
		
		$event->setUpdatedAt(new \DateTime());
		
		return $this->eventMapper->update($event);
	}

	/**
	 * Delete an event
	 */
	public function deleteEvent(int $id): void {
		// Delete all RSVPs first
		$this->rsvpMapper->deleteByEvent($id);
		// Then delete the event
		$this->eventMapper->deleteById($id);
	}

	/**
	 * Get event types
	 */
	public function getEventTypes(): array {
		return [
			['id' => 'meeting', 'label' => 'Versammlung', 'icon' => '👥'],
			['id' => 'rehearsal', 'label' => 'Probe', 'icon' => '🎵'],
			['id' => 'concert', 'label' => 'Konzert/Auftritt', 'icon' => '🎤'],
			['id' => 'event', 'label' => 'Veranstaltung', 'icon' => '📅'],
			['id' => 'deadline', 'label' => 'Frist', 'icon' => '⏰'],
			['id' => 'other', 'label' => 'Sonstiges', 'icon' => '📌'],
		];
	}

	// ==================== RSVP Methods ====================

	/**
	 * Submit RSVP response
	 */
	public function submitRsvp(int $eventId, string $response, ?string $comment = null, ?int $memberId = null): EventRsvp {
		$userId = $this->getCurrentUserId();
		$event = $this->eventMapper->find($eventId);

		if (!$event->isRsvpEnabled()) {
			throw new \InvalidArgumentException('RSVP ist für diesen Termin nicht aktiviert');
		}

		$now = new \DateTime();
		if ($event->getRsvpDeadline() && $now > $event->getRsvpDeadline()) {
			throw new \InvalidArgumentException('RSVP-Deadline ist überschritten');
		}
		
		// Check if RSVP already exists
		$existing = $this->rsvpMapper->findByEventAndUser($eventId, $userId);

		$hasCapacity = function () use ($eventId, $event, $existing, $response): bool {
			if ($response !== EventRsvp::RESPONSE_YES) {
				return true; // Only limit positive responses
			}
			$max = $event->getMaxParticipants();
			if ($max === null) {
				return true;
			}
			$yesCount = $this->rsvpMapper->countByResponse($eventId, EventRsvp::RESPONSE_YES);
			// allow keeping an existing YES even when full
			if ($existing && $existing->getResponse() === EventRsvp::RESPONSE_YES) {
				return true;
			}
			return $yesCount < $max;
		};

		if (!$hasCapacity()) {
			throw new \InvalidArgumentException('Maximale Teilnehmerzahl erreicht');
		}
		
		if ($existing) {
			// Update existing
			$existing->setResponse($response);
			$existing->setComment($comment);
			$existing->setRespondedAt(new \DateTime());
			return $this->rsvpMapper->update($existing);
		}
		
		// Create new
		$rsvp = new EventRsvp();
		$rsvp->setEventId($eventId);
		$rsvp->setUserId($userId);
		$rsvp->setMemberId($memberId);
		$rsvp->setResponse($response);
		$rsvp->setComment($comment);
		$rsvp->setRespondedAt(new \DateTime());
		
		return $this->rsvpMapper->insert($rsvp);
	}

	/**
	 * Get user's RSVP for an event
	 */
	public function getUserRsvp(int $eventId): ?array {
		$userId = $this->getCurrentUserId();
		$rsvp = $this->rsvpMapper->findByEventAndUser($eventId, $userId);
		return $rsvp?->toArray();
	}

	/**
	 * Get all RSVPs for an event
	 */
	public function getEventRsvps(int $eventId): array {
		$rsvps = $this->rsvpMapper->findByEvent($eventId);
		return array_map(fn(EventRsvp $r) => $r->toArray(), $rsvps);
	}

	/**
	 * Get RSVP statistics for an event
	 */
	public function getRsvpStatistics(int $eventId): array {
		return $this->rsvpMapper->getEventStatistics($eventId);
	}

	/**
	 * Get events needing RSVP from current user
	 */
	public function getEventsNeedingRsvp(): array {
		$userId = $this->getCurrentUserId();
		$events = $this->eventMapper->findWithRsvp();
		
		$needsResponse = [];
		foreach ($events as $event) {
			$rsvp = $this->rsvpMapper->findByEventAndUser($event->getId(), $userId);
			if (!$rsvp || $rsvp->getResponse() === EventRsvp::RESPONSE_PENDING) {
				$needsResponse[] = $this->enrichEventWithRsvp($event);
			}
		}
		
		return $needsResponse;
	}

	// ==================== Helper Methods ====================

	/**
	 * Enrich event with RSVP data
	 */
	private function enrichEventWithRsvp(Event $event): array {
		$data = $event->toArray();
		
		if ($event->isRsvpEnabled()) {
			$data['rsvpStats'] = $this->rsvpMapper->getEventStatistics($event->getId());
			$data['userRsvp'] = $this->getUserRsvpForEvent($event->getId());
		}
		
		return $data;
	}

	/**
	 * Get current user's RSVP for event
	 */
	private function getUserRsvpForEvent(int $eventId): ?string {
		$userId = $this->getCurrentUserId();
		if (!$userId) {
			return null;
		}
		
		$rsvp = $this->rsvpMapper->findByEventAndUser($eventId, $userId);
		return $rsvp?->getResponse();
	}

	/**
	 * Get current user ID
	 */
	private function getCurrentUserId(): string {
		return $this->userSession?->getUser()?->getUID() ?? 'system';
	}
}
