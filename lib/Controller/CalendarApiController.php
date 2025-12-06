<?php

declare(strict_types=1);

namespace OCA\Verein\Controller;

use OCA\Verein\Service\CalendarService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

/**
 * API Controller for calendar events
 */
class CalendarApiController extends Controller {

	public function __construct(
		string $appName,
		IRequest $request,
		private CalendarService $calendarService,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * GET /api/v1/calendar/events
	 * Get all events or filtered by date range
	 */
	public function getEvents(): JSONResponse {
		try {
			$year = $this->request->getParam('year');
			$month = $this->request->getParam('month');
			$from = $this->request->getParam('from');
			$to = $this->request->getParam('to');
			$limit = $this->request->getParam('limit');
			
			if ($year && $month) {
				$events = $this->calendarService->getEventsByMonth((int)$year, (int)$month);
			} elseif ($from && $to) {
				$events = $this->calendarService->getEventsByDateRange(
					new \DateTime($from),
					new \DateTime($to)
				);
			} else {
				$events = $this->calendarService->getEvents(
					$limit ? (int)$limit : null
				);
			}
			
			return new JSONResponse($events);
		} catch (\Exception $e) {
			return new JSONResponse(
				['error' => $e->getMessage()],
				Http::STATUS_INTERNAL_SERVER_ERROR
			);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * GET /api/v1/calendar/events/upcoming
	 * Get upcoming events
	 */
	public function getUpcoming(): JSONResponse {
		try {
			$limit = (int)($this->request->getParam('limit', 10));
			$events = $this->calendarService->getUpcomingEvents($limit);
			return new JSONResponse($events);
		} catch (\Exception $e) {
			return new JSONResponse(
				['error' => $e->getMessage()],
				Http::STATUS_INTERNAL_SERVER_ERROR
			);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * GET /api/v1/calendar/events/{id}
	 * Get single event
	 */
	public function getEvent(int $id): JSONResponse {
		try {
			$event = $this->calendarService->getEvent($id);
			return new JSONResponse($event);
		} catch (\OCP\AppFramework\Db\DoesNotExistException $e) {
			return new JSONResponse(
				['error' => 'Event nicht gefunden'],
				Http::STATUS_NOT_FOUND
			);
		} catch (\Exception $e) {
			return new JSONResponse(
				['error' => $e->getMessage()],
				Http::STATUS_INTERNAL_SERVER_ERROR
			);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * POST /api/v1/calendar/events
	 * Create new event
	 */
	public function createEvent(): JSONResponse {
		// DEBUG: This method was called
		error_log("CalendarApiController::createEvent() called");
		
		try {
			$data = json_decode(file_get_contents('php://input'), true);
			
			// DEBUG: Log received data
			error_log("createEvent data: " . json_encode($data));
			
			if (empty($data['title'])) {
				return new JSONResponse(
					['error' => 'Titel ist erforderlich', 'debug' => 'createEvent called'],
					Http::STATUS_BAD_REQUEST
				);
			}
			if (empty($data['startDate'])) {
				return new JSONResponse(
					['error' => 'Startdatum ist erforderlich'],
					Http::STATUS_BAD_REQUEST
				);
			}
			
			$event = $this->calendarService->createEvent($data);
			return new JSONResponse($event->toArray(), Http::STATUS_CREATED);
		} catch (\Exception $e) {
			return new JSONResponse(
				['error' => $e->getMessage()],
				Http::STATUS_INTERNAL_SERVER_ERROR
			);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * PUT /api/v1/calendar/events/{id}
	 * Update event
	 */
	public function updateEvent(int $id): JSONResponse {
		try {
			$data = json_decode(file_get_contents('php://input'), true);
			$event = $this->calendarService->updateEvent($id, $data);
			return new JSONResponse($event->toArray());
		} catch (\OCP\AppFramework\Db\DoesNotExistException $e) {
			return new JSONResponse(
				['error' => 'Event nicht gefunden'],
				Http::STATUS_NOT_FOUND
			);
		} catch (\Exception $e) {
			return new JSONResponse(
				['error' => $e->getMessage()],
				Http::STATUS_INTERNAL_SERVER_ERROR
			);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * DELETE /api/v1/calendar/events/{id}
	 * Delete event
	 */
	public function deleteEvent(int $id): JSONResponse {
		try {
			$this->calendarService->deleteEvent($id);
			return new JSONResponse(['success' => true]);
		} catch (\OCP\AppFramework\Db\DoesNotExistException $e) {
			return new JSONResponse(
				['error' => 'Event nicht gefunden'],
				Http::STATUS_NOT_FOUND
			);
		} catch (\Exception $e) {
			return new JSONResponse(
				['error' => $e->getMessage()],
				Http::STATUS_INTERNAL_SERVER_ERROR
			);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * GET /api/v1/calendar/event-types
	 * Get available event types
	 */
	public function getEventTypes(): JSONResponse {
		return new JSONResponse($this->calendarService->getEventTypes());
	}

	// ==================== RSVP Endpoints ====================

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * POST /api/v1/calendar/events/{id}/rsvp
	 * Submit RSVP response
	 */
	public function submitRsvp(int $id): JSONResponse {
		try {
			$data = json_decode(file_get_contents('php://input'), true);
			
			$response = $data['response'] ?? null;
			if (!in_array($response, ['yes', 'no', 'maybe'])) {
				return new JSONResponse(
					['error' => 'Ungültige Antwort. Erlaubt: yes, no, maybe'],
					Http::STATUS_BAD_REQUEST
				);
			}
			
			$rsvp = $this->calendarService->submitRsvp(
				$id,
				$response,
				$data['comment'] ?? null,
				$data['memberId'] ?? null
			);
			
			return new JSONResponse($rsvp->toArray());
		} catch (\InvalidArgumentException $e) {
			return new JSONResponse(
				['error' => $e->getMessage()],
				Http::STATUS_BAD_REQUEST
			);
		} catch (\Exception $e) {
			return new JSONResponse(
				['error' => $e->getMessage()],
				Http::STATUS_INTERNAL_SERVER_ERROR
			);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * GET /api/v1/calendar/events/{id}/rsvp
	 * Get RSVPs for event
	 */
	public function getEventRsvps(int $id): JSONResponse {
		try {
			$rsvps = $this->calendarService->getEventRsvps($id);
			$stats = $this->calendarService->getRsvpStatistics($id);
			
			return new JSONResponse([
				'responses' => $rsvps,
				'statistics' => $stats,
			]);
		} catch (\Exception $e) {
			return new JSONResponse(
				['error' => $e->getMessage()],
				Http::STATUS_INTERNAL_SERVER_ERROR
			);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * GET /api/v1/calendar/events/{id}/my-rsvp
	 * Get current user's RSVP for event
	 */
	public function getMyRsvp(int $id): JSONResponse {
		try {
			$rsvp = $this->calendarService->getUserRsvp($id);
			return new JSONResponse($rsvp ?? ['response' => null]);
		} catch (\Exception $e) {
			return new JSONResponse(
				['error' => $e->getMessage()],
				Http::STATUS_INTERNAL_SERVER_ERROR
			);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * GET /api/v1/calendar/pending-rsvp
	 * Get events needing RSVP from current user
	 */
	public function getPendingRsvp(): JSONResponse {
		try {
			$events = $this->calendarService->getEventsNeedingRsvp();
			return new JSONResponse($events);
		} catch (\Exception $e) {
			return new JSONResponse(
				['error' => $e->getMessage()],
				Http::STATUS_INTERNAL_SERVER_ERROR
			);
		}
	}
}
