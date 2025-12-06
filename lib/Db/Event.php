<?php

declare(strict_types=1);

namespace OCA\Verein\Db;

use OCP\AppFramework\Db\Entity;

/**
 * Event Entity for calendar events
 * 
 * @method int getId()
 * @method void setId(int $id)
 * @method string getTitle()
 * @method void setTitle(string $title)
 * @method string|null getDescription()
 * @method void setDescription(?string $description)
 * @method string|null getLocation()
 * @method void setLocation(?string $location)
 * @method string getEventType()
 * @method void setEventType(string $eventType)
 * @method \DateTime getStartDate()
 * @method void setStartDate(\DateTime $startDate)
 * @method \DateTime|null getEndDate()
 * @method void setEndDate(?\DateTime $endDate)
 * @method int getAllDay()
 * @method void setAllDay(int $allDay)
 * @method string|null getRecurring()
 * @method void setRecurring(?string $recurring)
 * @method \DateTime|null getRecurringUntil()
 * @method void setRecurringUntil(?\DateTime $recurringUntil)
 * @method int getRsvpEnabled()
 * @method void setRsvpEnabled(int $rsvpEnabled)
 * @method \DateTime|null getRsvpDeadline()
 * @method void setRsvpDeadline(?\DateTime $rsvpDeadline)
 * @method int|null getMaxParticipants()
 * @method void setMaxParticipants(?int $maxParticipants)
 * @method string getCreatedBy()
 * @method void setCreatedBy(string $createdBy)
 * @method \DateTime getCreatedAt()
 * @method void setCreatedAt(\DateTime $createdAt)
 * @method \DateTime|null getUpdatedAt()
 * @method void setUpdatedAt(?\DateTime $updatedAt)
 * @method string|null getCalendarUri()
 * @method void setCalendarUri(?string $calendarUri)
 */
class Event extends Entity {
	protected ?string $title = null;
	protected ?string $description = null;
	protected ?string $location = null;
	protected ?string $eventType = 'event';
	protected ?\DateTime $startDate = null;
	protected ?\DateTime $endDate = null;
	protected int $allDay = 0;
	protected ?string $recurring = null;
	protected ?\DateTime $recurringUntil = null;
	protected int $rsvpEnabled = 0;
	protected ?\DateTime $rsvpDeadline = null;
	protected ?int $maxParticipants = null;
	protected ?string $createdBy = null;
	protected ?\DateTime $createdAt = null;
	protected ?\DateTime $updatedAt = null;
	protected ?string $calendarUri = null;

	public function __construct() {
		$this->addType('id', 'integer');
		$this->addType('allDay', 'integer');
		$this->addType('rsvpEnabled', 'integer');
		$this->addType('maxParticipants', 'integer');
		// ensure DateTime fields are properly converted when persisting via QBMapper
		$this->addType('startDate', 'datetime');
		$this->addType('endDate', 'datetime');
		$this->addType('recurringUntil', 'datetime');
		$this->addType('rsvpDeadline', 'datetime');
		$this->addType('createdAt', 'datetime');
		$this->addType('updatedAt', 'datetime');
	}

	/**
	 * Check if event is all day
	 */
	public function isAllDay(): bool {
		return $this->allDay === 1;
	}

	/**
	 * Check if RSVP is enabled
	 */
	public function isRsvpEnabled(): bool {
		return $this->rsvpEnabled === 1;
	}

	/**
	 * Get event type label
	 */
	public function getEventTypeLabel(): string {
		$types = [
			'meeting' => 'Versammlung',
			'rehearsal' => 'Probe',
			'concert' => 'Konzert/Auftritt',
			'event' => 'Veranstaltung',
			'deadline' => 'Frist',
			'other' => 'Sonstiges',
		];
		return $types[$this->eventType] ?? $this->eventType;
	}

	/**
	 * Convert to array for JSON response
	 */
	public function toArray(): array {
		return [
			'id' => $this->getId(),
			'title' => $this->title,
			'description' => $this->description,
			'location' => $this->location,
			'eventType' => $this->eventType,
			'eventTypeLabel' => $this->getEventTypeLabel(),
			'startDate' => $this->startDate?->format('c'),
			'endDate' => $this->endDate?->format('c'),
			'allDay' => $this->isAllDay(),
			'recurring' => $this->recurring,
			'recurringUntil' => $this->recurringUntil?->format('Y-m-d'),
			'rsvpEnabled' => $this->isRsvpEnabled(),
			'rsvpDeadline' => $this->rsvpDeadline?->format('c'),
			'maxParticipants' => $this->maxParticipants,
			'createdBy' => $this->createdBy,
			'createdAt' => $this->createdAt?->format('c'),
			'updatedAt' => $this->updatedAt?->format('c'),
			'calendarUri' => $this->calendarUri,
		];
	}
}
