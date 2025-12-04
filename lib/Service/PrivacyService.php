<?php

declare(strict_types=1);

namespace OCA\Verein\Service;

use OCA\Verein\Db\MemberMapper;
use Psr\Log\LoggerInterface;
use OCP\IConfig;
use DateTime;
use DateInterval;

/**
 * Privacy Service für DSGVO-Compliance (Art. 15, 17, 20)
 * - Datenexport (Right of Access, Art. 15)
 * - Kontolöschung (Right to be Forgotten, Art. 17)
 * - Datenschutzerklärung & Consent Management
 */
class PrivacyService {
	private const CONSENT_KEY_PREFIX = 'privacy_consent_';
	private const AUDIT_LOG_PREFIX = 'privacy_audit_';

	public function __construct(
		private MemberMapper $memberMapper,
		private LoggerInterface $logger,
		private IConfig $config,
	) {
	}

	/**
	 * Hole alle persönlichen Daten eines Mitglieds (DSGVO Art. 15)
	 */
	public function exportMemberData(int $memberId): array {
		if (!$this->memberMapper) {
			return ['error' => 'Member service not available'];
		}
		try {
			$member = $this->memberMapper->find($memberId);

			$data = [
				'exported_at' => (new DateTime())->format('c'),
				'member' => [
					'id' => $member->getId(),
					'firstName' => $member->getFirstName(),
					'lastName' => $member->getLastName(),
					'email' => $member->getEmail(),
					'phone' => $member->getPhone(),
					'dateOfBirth' => $member->getDateOfBirth(),
					'address' => $member->getAddress(),
					'memberSince' => $member->getMemberSince(),
					'status' => $member->getStatus(),
					'iban' => $member->getIban(),
					'bic' => $member->getBic(),
				],
				'fees' => $this->getMemberFees($memberId),
				'transactions' => $this->getMemberTransactions($memberId),
				'roles' => $this->getMemberRoles($memberId),
				'consents' => $this->getMemberConsents($memberId),
				'audit_log' => $this->getAuditLog($memberId),
			];

			// Protokolliere Datenexport
			$this->logPrivacyAction($memberId, 'data_export', 'Member data exported');

			return $data;
		} catch (\Exception $e) {
			$this->logger->error('Error exporting member data: ' . $e->getMessage());
			throw $e;
		}
	}

	/**
	 * Lösche alle Daten eines Mitglieds (DSGVO Art. 17 - Right to be Forgotten)
	 * Unterscheidung:
	 * - soft_delete: Anonymisierung der Daten
	 * - hard_delete: Komplettes Löschen (nur unter bestimmten Bedingungen)
	 */
	public function deleteMemberData(int $memberId, string $mode = 'soft_delete'): bool {
		try {
			$this->logger->info("Deleting member data (mode: $mode): $memberId");

			if ($mode === 'hard_delete') {
				// Prüfe ob Löschung erlaubt ist (z.B. keine offenen Zahlungen)
				if (!$this->canHardDelete($memberId)) {
					throw new \Exception('Member has pending fees - hard delete not allowed');
				}
				return $this->hardDeleteMember($memberId);
			} else {
				// Soft Delete: Anonymisiere persönliche Daten
				return $this->softDeleteMember($memberId);
			}
		} catch (\Exception $e) {
			$this->logger->error('Error deleting member data: ' . $e->getMessage());
			throw $e;
		}
	}

	/**
	 * Anonymisiere Mitgliedsdaten (soft delete)
	 */
	private function softDeleteMember(int $memberId): bool {
		try {
			$member = $this->memberMapper->find($memberId);

			// Anonymisiere persönliche Daten
			$member->setFirstName('Anonymisiert');
			$member->setLastName('Anonymisiert');
			$member->setEmail('anonymized-' . $memberId . '@privacy.local');
			$member->setPhone(null);
			$member->setDateOfBirth(null);
			$member->setAddress(null);
			$member->setIban(null);
			$member->setBic(null);
			$member->setStatus('deleted');

			$this->memberMapper->update($member);

			// Protokolliere Soft Delete
			$this->logPrivacyAction($memberId, 'soft_delete', 'Member data anonymized');

			return true;
		} catch (\Exception $e) {
			$this->logger->error('Error soft deleting member: ' . $e->getMessage());
			return false;
		}
	}

	/**
	 * Lösche Mitgliedsdaten komplett (hard delete)
	 */
	private function hardDeleteMember(int $memberId): bool {
		try {
			// Lösche alle verwandten Daten
			$this->memberMapper->delete($memberId);

			// Protokolliere Hard Delete
			$this->logPrivacyAction($memberId, 'hard_delete', 'Member completely deleted');

			return true;
		} catch (\Exception $e) {
			$this->logger->error('Error hard deleting member: ' . $e->getMessage());
			return false;
		}
	}

	/**
	 * Prüfe ob Hard Delete erlaubt ist
	 */
	private function canHardDelete(int $memberId): bool {
		try {
			// Prüfe auf offene Gebühren
			// Prüfe auf laufende Transaktionen
			// Weitere Geschäftsregeln...
			return true; // Placeholder
		} catch (\Exception $e) {
			return false;
		}
	}

	/**
	 * Speichere Einwilligung (DSGVO Art. 6, 7 - Consent Management)
	 */
	public function saveConsent(int $memberId, string $consentType, bool $given, ?string $ipAddress = null): bool {
		try {
			$consentData = [
				'member_id' => $memberId,
				'type' => $consentType,
				'given' => $given,
				'timestamp' => (new DateTime())->format('c'),
				'ip_address' => $ipAddress ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown',
				'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
			];

			// Speichere in Datenbank (vereinfacht - würde in echtem Code zu Tabelle)
			$this->config->setAppValue('verein', 
				self::CONSENT_KEY_PREFIX . $memberId . '_' . $consentType,
				json_encode($consentData)
			);

			// Protokolliere
			$this->logPrivacyAction($memberId, 'consent_' . ($given ? 'given' : 'withdrawn'), "Consent: $consentType");

			return true;
		} catch (\Exception $e) {
			$this->logger->error('Error saving consent: ' . $e->getMessage());
			return false;
		}
	}

	/**
	 * Hole Einwilligungsstatus
	 */
	public function getConsent(int $memberId, string $consentType): ?array {
		try {
			$consentJson = $this->config->getAppValue('verein', 
				self::CONSENT_KEY_PREFIX . $memberId . '_' . $consentType
			);

			return $consentJson ? json_decode($consentJson, true) : null;
		} catch (\Exception $e) {
			return null;
		}
	}

	/**
	 * Hole alle Einwilligungen eines Mitglieds
	 */
	public function getMemberConsents(int $memberId): array {
		$consents = [];
		$consentTypes = ['newsletter', 'marketing', 'analytics', 'partners'];

		foreach ($consentTypes as $type) {
			$consent = $this->getConsent($memberId, $type);
			if ($consent) {
				$consents[$type] = $consent;
			}
		}

		return $consents;
	}

	/**
	 * Hole Audit-Log für Mitglied
	 */
	public function getAuditLog(int $memberId, ?DateTime $since = null): array {
		$since = $since ?? (new DateTime())->sub(new DateInterval('P1Y')); // Default: letzte 12 Monate

		$logs = [];
		// Würde in echtem Code aus audit_logs Tabelle kommen
		// Placeholder Implementation:
		try {
			// Sammle alle Privacy-Aktionen
			// Implementierung abhängig von Datenspeicherung
		} catch (\Exception $e) {
			$this->logger->error('Error getting audit log: ' . $e->getMessage());
		}

		return $logs;
	}

	/**
	 * Protokolliere Privacy-Aktion für Audit-Trail
	 */
	private function logPrivacyAction(int $memberId, string $action, string $description): void {
		try {
			$logEntry = [
				'member_id' => $memberId,
				'action' => $action,
				'description' => $description,
				'timestamp' => (new DateTime())->format('c'),
				'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
				'user_id' => $GLOBALS['OC_USER'] ?? 'system',
			];

			// Speichere in audit_logs Tabelle (vereinfacht)
			$this->logger->info('Privacy action: ' . json_encode($logEntry));
		} catch (\Exception $e) {
			$this->logger->error('Error logging privacy action: ' . $e->getMessage());
		}
	}

	/**
	 * Hole Gebühren für Mitglied
	 */
	private function getMemberFees(int $memberId): array {
		// Placeholder - würde echte Daten aus FeeMapper holen
		return [];
	}

	/**
	 * Hole Transaktionen für Mitglied
	 */
	private function getMemberTransactions(int $memberId): array {
		// Placeholder - würde echte Daten holen
		return [];
	}

	/**
	 * Hole Rollen für Mitglied
	 */
	private function getMemberRoles(int $memberId): array {
		// Placeholder - würde echte Daten holen
		return [];
	}

	/**
	 * Gebe Datenschutzerklärung zurück
	 */
	public function getPrivacyPolicy(): string {
		return $this->config->getAppValue('verein', 'privacy_policy_text') ?? $this->getDefaultPrivacyPolicy();
	}

	/**
	 * Standard Datenschutzerklärung
	 */
	private function getDefaultPrivacyPolicy(): string {
		return <<<'HTML'
# Datenschutzerklärung

## 1. Verantwortlicher
[Vereinsname]
[Adresse]

## 2. Erhobene Daten
- Persönliche Daten: Name, Email, Telefon, Geburtsdatum
- Adressdaten: Straße, PLZ, Stadt
- Bankdaten: IBAN, BIC (für SEPA-Zahlungen)
- Aktivitätsdaten: Gebühren, Transaktionen, Rollen

## 3. Rechtliche Grundlage (DSGVO)
- Art. 6 Abs. 1 lit. a: Einwilligung
- Art. 6 Abs. 1 lit. b: Vertragserfüllung
- Art. 6 Abs. 1 lit. f: Berechtigte Interessen

## 4. Ihre Rechte
- **Art. 15 DSGVO**: Recht auf Auskunft (Datenexport)
- **Art. 16 DSGVO**: Recht auf Berichtigung
- **Art. 17 DSGVO**: Recht auf Löschung
- **Art. 18 DSGVO**: Recht auf Einschränkung
- **Art. 20 DSGVO**: Recht auf Datenportabilität
- **Art. 21 DSGVO**: Recht auf Widerspruch

## 5. Datensicherheit
Wir nutzen moderne Verschlüsselung und Sicherheitsmaßnahmen zum Schutz Ihrer Daten.

## 6. Kontakt
Datenschutzbeauftragter: [Email]
HTML;
	}
}
