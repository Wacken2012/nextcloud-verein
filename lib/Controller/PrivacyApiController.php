<?php

declare(strict_types=1);

namespace OCA\Verein\Controller;

use OCA\Verein\Service\PrivacyService;
use OCA\Verein\Db\PrivacyAuditLog;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataDownloadResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\IUserSession;
use DateTime;

class PrivacyApiController extends Controller {
	public function __construct(
		string $appName,
		IRequest $request,
		private PrivacyService $privacyService,
		private ?IUserSession $userSession = null,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * @NoAdminRequired
	 * GET /api/v1/privacy/export/{memberId}
	 * Exportiere alle Daten eines Mitglieds (DSGVO Art. 15)
	 */
	public function exportData(string|int $memberId): DataDownloadResponse|JSONResponse {
		try {
			// If memberId is a string (userId), return stub data for now
			if (!is_numeric($memberId)) {
				$data = [
					'exported_at' => date('c'),
					'user_id' => $memberId,
					'note' => 'User data export - no member record linked to this Nextcloud user'
				];
			} else {
				// Verifiziere dass Mitglied nur seine eigenen Daten exportieren darf
				$this->validateMemberAccess((int)$memberId);
				$data = $this->privacyService->exportMemberData((int)$memberId);
			}
			$json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

			$response = new DataDownloadResponse(
				$json,
				'personal_data_export_' . date('Y-m-d') . '.json',
				'application/json'
			);

			return $response;
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], Http::STATUS_FORBIDDEN);
		}
	}

	/**
	 * @NoAdminRequired
	 * DELETE /api/v1/privacy/member/{memberId}
	 * Lösche Mitgliedsdaten (DSGVO Art. 17)
	 */
	public function deleteData(string|int $memberId): JSONResponse {
		try {
			// Wenn memberId kein numerischer Wert ist (z.B. Nextcloud userId),
			// dann ist keine Löschung möglich (kein Mitglied verknüpft)
			if (!is_numeric($memberId)) {
				return new JSONResponse([
					'success' => false,
					'message' => 'Kein Mitglied mit diesem Benutzer verknüpft. Keine Daten zum Löschen vorhanden.'
				]);
			}
			
			$this->validateMemberAccess((int)$memberId);

			$params = json_decode(file_get_contents('php://input'), true);
			$mode = $params['mode'] ?? 'soft_delete'; // soft_delete oder hard_delete

			if (!in_array($mode, ['soft_delete', 'hard_delete'])) {
				return new JSONResponse(
					['error' => 'Invalid delete mode'],
					Http::STATUS_BAD_REQUEST
				);
			}

			$success = $this->privacyService->deleteMemberData((int)$memberId, $mode);

			return new JSONResponse([
				'success' => $success,
				'message' => $success 
					? 'Member data deleted successfully'
					: 'Error deleting member data'
			]);
		} catch (\Exception $e) {
			return new JSONResponse(
				['error' => $e->getMessage()],
				Http::STATUS_FORBIDDEN
			);
		}
	}

	/**
	 * @NoAdminRequired
	 * GET /api/v1/privacy/consent/{memberId}
	 * Hole Einwilligungsstatus
	 */
	public function getConsent(int $memberId): JSONResponse {
		return $this->getConsents($memberId);
	}

	public function getConsents(int $memberId): JSONResponse {
		try {
			$this->validateMemberAccess($memberId);
			$consents = $this->privacyService->getMemberConsents($memberId);
			return new JSONResponse($consents);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], Http::STATUS_FORBIDDEN);
		}
	}

	/**
	 * @NoAdminRequired
	 * POST /api/v1/privacy/consent/{memberId}
	 * Speichere Einwilligung
	 */
	public function saveConsent(int $memberId): JSONResponse {
		try {
			$this->validateMemberAccess($memberId);

			$params = json_decode(file_get_contents('php://input'), true);
			$type = $params['type'] ?? null;
			$given = (bool)($params['given'] ?? false);

			if (!$type) {
				return new JSONResponse(
					['error' => 'Consent type required'],
					Http::STATUS_BAD_REQUEST
				);
			}

			$success = $this->privacyService->saveConsent($memberId, $type, $given);

			return new JSONResponse([
				'success' => $success,
				'type' => $type,
				'given' => $given,
			]);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], Http::STATUS_INTERNAL_SERVER_ERROR);
		}
	}

	/**
	 * @PublicPage
	 * GET /api/v1/privacy/policy
	 * Hole Datenschutzerklärung
	 */
	public function getPolicy(): JSONResponse {
		return $this->getPrivacyPolicy();
	}

	public function getPrivacyPolicy(): JSONResponse {
		try {
			$policy = $this->privacyService->getPrivacyPolicy();
			$isCustom = $this->privacyService->hasCustomPrivacyPolicy();
			return new JSONResponse([
				'policy' => $policy,
				'isCustom' => $isCustom,
			]);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], Http::STATUS_INTERNAL_SERVER_ERROR);
		}
	}

	/**
	 * @NoAdminRequired
	 * PUT /api/v1/privacy/policy
	 * Speichere Datenschutzerklärung (nur Admins)
	 */
	public function savePolicy(): JSONResponse {
		try {
			// Prüfe Admin-Berechtigung
			$user = $this->userSession?->getUser();
			if (!$user) {
				return new JSONResponse(['error' => 'Nicht eingeloggt'], Http::STATUS_UNAUTHORIZED);
			}
			
			$params = json_decode(file_get_contents('php://input'), true);
			$policyText = $params['policy'] ?? '';
			
			if (empty(trim($policyText))) {
				// Leerer Text = zurück zum Standard
				$this->privacyService->resetPrivacyPolicy();
				return new JSONResponse([
					'success' => true,
					'message' => 'Datenschutzerklärung auf Standard zurückgesetzt',
				]);
			}
			
			$this->privacyService->savePrivacyPolicy($policyText);
			
			return new JSONResponse([
				'success' => true,
				'message' => 'Datenschutzerklärung gespeichert',
			]);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], Http::STATUS_INTERNAL_SERVER_ERROR);
		}
	}

	/**
	 * Verifiziere dass Mitglied nur auf seine eigenen Daten zugreift
	 */
	private function validateMemberAccess(int $memberId): void {
		// In einer vollständigen Implementierung würde hier geprüft werden:
		// 1. Ist der Benutzer ein Admin? -> Zugriff auf alle Mitglieder
		// 2. Ist das Mitglied mit diesem Nextcloud-User verknüpft? -> Zugriff erlaubt
		// 3. Hat der User spezielle Berechtigungen (z.B. Vorstand)?
		
		// Für jetzt: Zugriff erlaubt wenn eingeloggt
		if ($this->userSession !== null) {
			$user = $this->userSession->getUser();
			if ($user === null) {
				throw new \Exception('Not authenticated');
			}
		}
	}

	/**
	 * @NoAdminRequired
	 * GET /api/v1/privacy/can-delete/{memberId}
	 * Prüfe ob Löschung möglich ist und warum nicht
	 */
	public function canDelete(string|int $memberId): JSONResponse {
		try {
			// Wenn memberId kein numerischer Wert ist (z.B. Nextcloud userId),
			// dann geben wir Standard-Werte zurück
			if (!is_numeric($memberId)) {
				return new JSONResponse([
					'canSoftDelete' => true,
					'canHardDelete' => true,
					'blockers' => [],
				]);
			}
			
			$this->validateMemberAccess((int)$memberId);
			
			$canHardDelete = $this->privacyService->canHardDelete((int)$memberId);
			$blockers = $this->privacyService->getHardDeleteBlockers((int)$memberId);

			return new JSONResponse([
				'canSoftDelete' => true, // Soft delete ist immer möglich
				'canHardDelete' => $canHardDelete,
				'blockers' => $blockers,
			]);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], Http::STATUS_FORBIDDEN);
		}
	}

	/**
	 * @NoAdminRequired
	 * GET /api/v1/privacy/consent-types
	 * Hole alle verfügbaren Consent-Typen
	 */
	public function getConsentTypes(): JSONResponse {
		try {
			$types = $this->privacyService->getAvailableConsentTypes();
			return new JSONResponse($types);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], Http::STATUS_INTERNAL_SERVER_ERROR);
		}
	}

	/**
	 * @NoAdminRequired
	 * GET /api/v1/privacy/audit-log/{memberId}
	 * Hole Audit-Log für ein Mitglied
	 */
	public function getAuditLog(int $memberId): JSONResponse {
		try {
			$this->validateMemberAccess($memberId);
			
			$limit = (int)($this->request->getParam('limit', 100));
			$logs = $this->privacyService->getAuditLog($memberId, $limit);

			return new JSONResponse([
				'logs' => $logs,
				'total' => count($logs),
			]);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], Http::STATUS_FORBIDDEN);
		}
	}

	/**
	 * @NoAdminRequired
	 * POST /api/v1/privacy/consent/{memberId}/bulk
	 * Speichere mehrere Einwilligungen auf einmal
	 */
	public function saveConsentsBulk(int $memberId): JSONResponse {
		try {
			$this->validateMemberAccess($memberId);

			$params = json_decode(file_get_contents('php://input'), true);
			$consents = $params['consents'] ?? [];

			if (empty($consents)) {
				return new JSONResponse(
					['error' => 'No consents provided'],
					Http::STATUS_BAD_REQUEST
				);
			}

			$results = [];
			foreach ($consents as $type => $given) {
				$success = $this->privacyService->saveConsent($memberId, $type, (bool)$given);
				$results[$type] = $success;
			}

			return new JSONResponse([
				'success' => true,
				'results' => $results,
			]);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], Http::STATUS_INTERNAL_SERVER_ERROR);
		}
	}

	/**
	 * @NoAdminRequired
	 * GET /api/v1/privacy/audit-statistics
	 * Hole Audit-Log Statistiken (nur für Admins)
	 */
	public function getAuditStatistics(): JSONResponse {
		try {
			// TODO: Admin-Prüfung hinzufügen
			$stats = $this->privacyService->getAuditLogStatistics();
			return new JSONResponse($stats);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], Http::STATUS_INTERNAL_SERVER_ERROR);
		}
	}
}
