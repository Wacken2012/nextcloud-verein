<?php

declare(strict_types=1);

namespace OCA\NextcloudVerein\Controller;

use OCA\NextcloudVerein\Service\ReminderService;
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
	 * @PasswordConfirmationRequired
	 * GET /api/v1/reminders/config
	 * Hole Reminder-Konfiguration
	 */
	public function getConfig(): JSONResponse {
		try {
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
	 * @PasswordConfirmationRequired
	 * POST /api/v1/reminders/config
	 * Aktualisiere Reminder-Konfiguration
	 */
	public function updateConfig(): JSONResponse {
		try {
			$params = json_decode($this->request->getBody(), true);

			if (isset($params['enabled'])) {
				$this->reminderService->enableReminders((bool)$params['enabled']);
			}

			if (isset($params['intervals'])) {
				$intervals = $params['intervals'];
				$this->reminderService->setReminderIntervals(
					(int)$intervals['level_1'] ?? 7,
					(int)$intervals['level_2'] ?? 3,
					(int)$intervals['level_3'] ?? 7
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
}
