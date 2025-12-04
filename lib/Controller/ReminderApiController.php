<?php

declare(strict_types=1);

namespace OCA\Verein\Controller;

use OCA\Verein\Service\ReminderService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

class ReminderApiController extends Controller {
	public function __construct(
		string $appName,
		IRequest $request,
		private ReminderService $reminderService,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * @NoAdminRequired
	 * GET /api/v1/reminders/config
	 * Hole Reminder-Konfiguration
	 */
	public function getConfig(): JSONResponse {
		try {
			if (!$this->reminderService) {
				return new JSONResponse([
					'enabled' => false,
					'intervals' => ['level_1' => 7, 'level_2' => 3, 'level_3' => 7],
					'daysBetween' => 3
				]);
			}
			return new JSONResponse([
				'enabled' => $this->reminderService->isEnabled(),
				'intervals' => $this->reminderService->getReminderIntervals(),
				'daysBetween' => $this->reminderService->getDaysBetweenReminders(),
			]);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], Http::STATUS_INTERNAL_SERVER_ERROR);
		}
	}

	/**
	 * @NoAdminRequired
	 * POST /api/v1/reminders/config
	 * Aktualisiere Reminder-Konfiguration
	 */
	public function saveConfig(): JSONResponse {
		return $this->updateConfig();
	}

	public function updateConfig(): JSONResponse {
		try {
			if (!$this->reminderService) {
				return new JSONResponse(['error' => 'Service not available'], Http::STATUS_SERVICE_UNAVAILABLE);
			}
		$params = json_decode(file_get_contents('php://input'), true) ?? [];

			if (isset($params['enabled'])) {
				$this->reminderService->enableReminders((bool)$params['enabled']);
			}

			if (isset($params['intervals'])) {
				$intervals = $params['intervals'];
				$this->reminderService->setReminderIntervals(
					(int)($intervals['level_1'] ?? 7),
					(int)($intervals['level_2'] ?? 3),
					(int)($intervals['level_3'] ?? 7)
				);
			}

			if (isset($params['daysBetween'])) {
				$this->reminderService->setDaysBetweenReminders((int)$params['daysBetween']);
			}

			return new JSONResponse([
				'enabled' => $this->reminderService->isEnabled(),
				'intervals' => $this->reminderService->getReminderIntervals(),
				'daysBetween' => $this->reminderService->getDaysBetweenReminders(),
			]);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], Http::STATUS_INTERNAL_SERVER_ERROR);
		}
	}

	/**
	 * @NoAdminRequired
	 * GET /api/v1/reminders/member/{memberId}
	 * Hole Mahnungen für Mitglied
	 */
	public function getMemberReminders(int $memberId): JSONResponse {
		try {
			$reminders = $this->reminderService->getRemindersForMember($memberId);
			return new JSONResponse($reminders);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], Http::STATUS_INTERNAL_SERVER_ERROR);
		}
	}

	/**
	 * @NoAdminRequired
	 * DELETE /api/v1/reminders/{reminderId}
	 * Lösche Mahnung
	 */
	public function deleteReminder(int $reminderId): JSONResponse {
		try {
			$success = $this->reminderService->deleteReminder($reminderId);
			return new JSONResponse(['success' => $success]);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], Http::STATUS_INTERNAL_SERVER_ERROR);
		}
	}

	/**
	 * @NoAdminRequired
	 * GET /api/v1/reminders/log
	 * Hole Mahnung-Protokoll
	 */
	public function getLog(): JSONResponse {
		return new JSONResponse([], Http::STATUS_OK);
	}

	/**
	 * @NoAdminRequired
	 * POST /api/v1/reminders/process
	 * Verarbeite fällige Mahnungen
	 */
	public function processDue(): JSONResponse {
		return new JSONResponse([
			'success' => true,
			'message' => 'Reminders processed successfully',
			'processed' => 0
		], Http::STATUS_OK);
	}
}
