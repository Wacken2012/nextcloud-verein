<?php

declare(strict_types=1);

namespace OCA\Verein\Db;

use OCP\AppFramework\Db\Entity;

/**
 * EventRsvp Entity for event attendance responses
 * 
 * @method int getId()
 * @method void setId(int $id)
 * @method int getEventId()
 * @method void setEventId(int $eventId)
 * @method int|null getMemberId()
 * @method void setMemberId(?int $memberId)
 * @method string getUserId()
 * @method void setUserId(string $userId)
 * @method string getResponse()
 * @method void setResponse(string $response)
 * @method string|null getComment()
 * @method void setComment(?string $comment)
 * @method \DateTime getRespondedAt()
 * @method void setRespondedAt(\DateTime $respondedAt)
 */
class EventRsvp extends Entity {
	protected ?int $eventId = null;
	protected ?int $memberId = null;
	protected ?string $userId = null;
	protected ?string $response = 'pending';
	protected ?string $comment = null;
	protected ?\DateTime $respondedAt = null;

	// Response types
	public const RESPONSE_YES = 'yes';
	public const RESPONSE_NO = 'no';
	public const RESPONSE_MAYBE = 'maybe';
	public const RESPONSE_PENDING = 'pending';

	public function __construct() {
		$this->addType('id', 'integer');
		$this->addType('eventId', 'integer');
		$this->addType('memberId', 'integer');
		// ensure respond time is converted properly when persisting
		$this->addType('respondedAt', 'datetime');
	}

	/**
	 * Get response label in German
	 */
	public function getResponseLabel(): string {
		$labels = [
			self::RESPONSE_YES => 'Zusage',
			self::RESPONSE_NO => 'Absage',
			self::RESPONSE_MAYBE => 'Vielleicht',
			self::RESPONSE_PENDING => 'Ausstehend',
		];
		return $labels[$this->response] ?? $this->response;
	}

	/**
	 * Check if response is positive
	 */
	public function isAttending(): bool {
		return $this->response === self::RESPONSE_YES;
	}

	/**
	 * Convert to array for JSON response
	 */
	public function toArray(): array {
		return [
			'id' => $this->getId(),
			'eventId' => $this->eventId,
			'memberId' => $this->memberId,
			'userId' => $this->userId,
			'response' => $this->response,
			'responseLabel' => $this->getResponseLabel(),
			'comment' => $this->comment,
			'respondedAt' => $this->respondedAt?->format('c'),
		];
	}
}
