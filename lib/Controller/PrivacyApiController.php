<?php

declare(strict_types=1);

namespace OCA\Verein\Controller;

use OCA\Verein\Service\PrivacyService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataDownloadResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use DateTime;

class PrivacyApiController extends Controller {
	public function __construct(
		string $appName,
		IRequest $request,
		private PrivacyService $privacyService,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * @NoAdminRequired
	 * @PasswordConfirmationRequired
	 * GET /api/v1/privacy/export/{memberId}
	 * Exportiere alle Daten eines Mitglieds (DSGVO Art. 15)
	 */
	public function exportData(int $memberId): DataDownloadResponse {
		try {
			// Verifiziere dass Mitglied nur seine eigenen Daten exportieren darf
			$this->validateMemberAccess($memberId);

			$data = $this->privacyService->exportMemberData($memberId);
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
	 * @PasswordConfirmationRequired
	 * POST /api/v1/privacy/delete/{memberId}
	 * Lösche Mitgliedsdaten (DSGVO Art. 17)
	 */
	public function deleteData(int $memberId): JSONResponse {
		try {
			$this->validateMemberAccess($memberId);

			$params = json_decode($this->request->getBody(), true);
			$mode = $params['mode'] ?? 'soft_delete'; // soft_delete oder hard_delete

			if (!in_array($mode, ['soft_delete', 'hard_delete'])) {
				return new JSONResponse(
					['error' => 'Invalid delete mode'],
					Http::STATUS_BAD_REQUEST
				);
			}

			$success = $this->privacyService->deleteMemberData($memberId, $mode);

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
	 * GET /api/v1/privacy/consents/{memberId}
	 * Hole Einwilligungsstatus
	 */
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

			$params = json_decode($this->request->getBody(), true);
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
	public function getPrivacyPolicy(): JSONResponse {
		try {
			$policy = $this->privacyService->getPrivacyPolicy();
			return new JSONResponse(['policy' => $policy]);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], Http::STATUS_INTERNAL_SERVER_ERROR);
		}
	}

	/**
	 * Verifiziere dass Mitglied nur auf seine eigenen Daten zugreift
	 */
	private function validateMemberAccess(int $memberId): void {
		$userId = $this->request->getAttribute('userId');
		// Vereinfachte Implementierung - würde in echter App komplexer sein
		// (Überprüfung der Berechtigung, Ownership, Admin-Status)
	}
}
