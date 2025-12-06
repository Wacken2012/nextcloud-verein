<?php

declare(strict_types=1);

namespace OCA\Verein\Service;

use OCA\Verein\Db\MemberMapper;
use OCA\Verein\Db\FeeMapper;
use OCA\Verein\Db\ConsentMapper;
use OCA\Verein\Db\Consent;
use OCA\Verein\Db\PrivacyAuditLogMapper;
use OCA\Verein\Db\PrivacyAuditLog;
use Psr\Log\LoggerInterface;
use OCP\IConfig;
use OCP\IRequest;
use OCP\IUserSession;
use DateTime;
use DateInterval;

/**
 * Privacy Service für DSGVO-Compliance (Art. 15, 17, 20)
 * - Datenexport (Right of Access, Art. 15)
 * - Kontolöschung (Right to be Forgotten, Art. 17)
 * - Datenschutzerklärung & Consent Management
 * - Audit-Log für alle Datenschutz-relevanten Aktionen
 */
class PrivacyService {
	private const CONSENT_KEY_PREFIX = 'privacy_consent_';
	private const AUDIT_LOG_PREFIX = 'privacy_audit_';

	public function __construct(
		private MemberMapper $memberMapper,
		private LoggerInterface $logger,
		private IConfig $config,
		private ?FeeMapper $feeMapper = null,
		private ?ConsentMapper $consentMapper = null,
		private ?PrivacyAuditLogMapper $auditLogMapper = null,
		private ?IRequest $request = null,
		private ?IUserSession $userSession = null,
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
	 * Blockiert Löschung wenn:
	 * - Offene Gebühren existieren
	 * - Aufbewahrungsfristen gelten (z.B. für Steuer)
	 */
	public function canHardDelete(int $memberId): bool {
		try {
			// Prüfe auf offene Gebühren
			if ($this->feeMapper !== null) {
				$fees = $this->feeMapper->findByMemberId($memberId);
				foreach ($fees as $fee) {
					if ($fee->getStatus() === 'open' || $fee->getStatus() === 'overdue') {
						$this->logger->info("Hard delete blocked for member $memberId: Open fees exist");
						return false;
					}
				}
			}
			
			// Prüfe Aufbewahrungsfrist (Standard: 10 Jahre nach letzter Zahlung)
			$retentionYears = (int)$this->config->getAppValue('verein', 'data_retention_years', '10');
			if ($this->feeMapper !== null && $retentionYears > 0) {
				$fees = $this->feeMapper->findByMemberId($memberId);
				$cutoffDate = new DateTime("-{$retentionYears} years");
				
				foreach ($fees as $fee) {
					$paidDate = $fee->getPaidDate();
					if ($paidDate !== null) {
						$paidDateTime = new DateTime($paidDate);
						if ($paidDateTime > $cutoffDate) {
							$this->logger->info("Hard delete blocked for member $memberId: Retention period not expired");
							return false;
						}
					}
				}
			}
			
			return true;
		} catch (\Exception $e) {
			$this->logger->error('Error checking hard delete eligibility: ' . $e->getMessage());
			return false;
		}
	}

	/**
	 * Hole Gründe warum Hard Delete nicht möglich ist
	 */
	public function getHardDeleteBlockers(int $memberId): array {
		$blockers = [];
		
		try {
			// Prüfe auf offene Gebühren
			if ($this->feeMapper !== null) {
				$fees = $this->feeMapper->findByMemberId($memberId);
				$openFees = array_filter($fees, fn($f) => in_array($f->getStatus(), ['open', 'overdue']));
				if (count($openFees) > 0) {
					$totalOpen = array_sum(array_map(fn($f) => (float)$f->getAmount(), $openFees));
					$blockers[] = [
						'type' => 'open_fees',
						'message' => sprintf('%d offene Gebühren (%.2f €)', count($openFees), $totalOpen),
					];
				}
			}
			
			// Prüfe Aufbewahrungsfrist
			$retentionYears = (int)$this->config->getAppValue('verein', 'data_retention_years', '10');
			if ($this->feeMapper !== null && $retentionYears > 0) {
				$fees = $this->feeMapper->findByMemberId($memberId);
				$cutoffDate = new DateTime("-{$retentionYears} years");
				
				foreach ($fees as $fee) {
					$paidDate = $fee->getPaidDate();
					if ($paidDate !== null) {
						$paidDateTime = new DateTime($paidDate);
						if ($paidDateTime > $cutoffDate) {
							$expiresAt = (clone $paidDateTime)->modify("+{$retentionYears} years");
							$blockers[] = [
								'type' => 'retention_period',
								'message' => sprintf('Aufbewahrungsfrist läuft bis %s', $expiresAt->format('d.m.Y')),
							];
							break;
						}
					}
				}
			}
		} catch (\Exception $e) {
			$blockers[] = [
				'type' => 'error',
				'message' => 'Fehler bei der Prüfung: ' . $e->getMessage(),
			];
		}
		
		return $blockers;
	}

	/**
	 * Speichere Einwilligung (DSGVO Art. 6, 7 - Consent Management)
	 */
	public function saveConsent(int $memberId, string $consentType, bool $given, ?string $ipAddress = null): bool {
		try {
			// Hole IP-Adresse aus Request wenn nicht angegeben
			if ($ipAddress === null && $this->request !== null) {
				$ipAddress = $this->request->getRemoteAddress();
			}

			// Nutze neuen ConsentMapper wenn verfügbar
			if ($this->consentMapper !== null) {
				$source = Consent::SOURCE_WEB;
				$this->consentMapper->setConsent($memberId, $consentType, $given, $ipAddress, $source);
			} else {
				// Fallback auf alte Config-Speicherung
				$consentData = [
					'member_id' => $memberId,
					'type' => $consentType,
					'given' => $given,
					'timestamp' => (new DateTime())->format('c'),
					'ip_address' => $ipAddress ?? 'unknown',
				];

				$this->config->setAppValue('verein', 
					self::CONSENT_KEY_PREFIX . $memberId . '_' . $consentType,
					json_encode($consentData)
				);
			}

			// Protokolliere
			$action = $given ? PrivacyAuditLog::ACTION_CONSENT_GIVEN : PrivacyAuditLog::ACTION_CONSENT_REVOKED;
			$this->logPrivacyAction($memberId, $action, null, ['consent_type' => $consentType]);

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
			// Zuerst aus neuer Consent-Tabelle prüfen
			if ($this->consentMapper !== null) {
				$consent = $this->consentMapper->findByMemberAndType($memberId, $consentType);
				if ($consent !== null) {
					return $consent->toArray();
				}
			}
			
			// Fallback auf alte Config-Speicherung
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
		// Nutze neuen ConsentMapper wenn verfügbar
		if ($this->consentMapper !== null) {
			return $this->consentMapper->getConsentsMap($memberId);
		}
		
		// Fallback auf alte Implementierung
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
	 * Hole alle verfügbaren Consent-Typen mit Beschreibungen
	 */
	public function getAvailableConsentTypes(): array {
		return [
			'newsletter' => [
				'label' => 'Newsletter',
				'description' => 'Ich möchte den Vereinsnewsletter per E-Mail erhalten.',
				'required' => false,
			],
			'marketing' => [
				'label' => 'Marketing und Werbung',
				'description' => 'Meine Daten dürfen für Werbung und Marketing verwendet werden.',
				'required' => false,
			],
			'analytics' => [
				'label' => 'Anonyme Nutzungsstatistiken',
				'description' => 'Anonymisierte Nutzungsdaten dürfen für Statistiken verwendet werden.',
				'required' => false,
			],
			'partners' => [
				'label' => 'Weitergabe an Partner',
				'description' => 'Meine Daten dürfen an Partnerorganisationen weitergegeben werden.',
				'required' => false,
			],
			'photos' => [
				'label' => 'Veröffentlichung von Fotos',
				'description' => 'Fotos von mir dürfen auf der Vereinswebsite und in sozialen Medien veröffentlicht werden.',
				'required' => false,
			],
			'internal_communication' => [
				'label' => 'Interne Vereinskommunikation',
				'description' => 'Ich möchte über Vereinsaktivitäten per E-Mail informiert werden.',
				'required' => false,
			],
			'birthday_list' => [
				'label' => 'Geburtstagsliste',
				'description' => 'Mein Geburtstag darf in der internen Geburtstagsliste erscheinen.',
				'required' => false,
			],
			'member_directory' => [
				'label' => 'Mitgliederverzeichnis',
				'description' => 'Meine Kontaktdaten dürfen im internen Mitgliederverzeichnis erscheinen.',
				'required' => false,
			],
		];
	}

	/**
	 * Hole Audit-Log für Mitglied
	 */
	public function getAuditLog(int $memberId, ?int $limit = 100): array {
		// Nutze neuen AuditLogMapper wenn verfügbar
		if ($this->auditLogMapper !== null) {
			$logs = $this->auditLogMapper->findByMemberId($memberId, $limit);
			return array_map(fn($log) => $log->toArray(), $logs);
		}

		// Fallback: Leeres Array
		return [];
	}

	/**
	 * Hole Audit-Log Statistiken (für Admin)
	 */
	public function getAuditLogStatistics(): array {
		if ($this->auditLogMapper !== null) {
			return $this->auditLogMapper->getStatistics();
		}
		return ['total' => 0, 'last30Days' => 0, 'byAction' => []];
	}

	/**
	 * Protokolliere Privacy-Aktion für Audit-Trail
	 */
	public function logPrivacyAction(int $memberId, string $action, ?string $description = null, array $details = []): void {
		try {
			// Hole Request-Informationen
			$ipAddress = null;
			$userAgent = null;
			$userId = null;

			if ($this->request !== null) {
				$ipAddress = $this->request->getRemoteAddress();
				$userAgent = $this->request->getHeader('User-Agent');
			}

			if ($this->userSession !== null) {
				$user = $this->userSession->getUser();
				$userId = $user?->getUID();
			}

			// Beschreibung zu Details hinzufügen
			if ($description !== null) {
				$details['description'] = $description;
			}

			// Nutze neuen AuditLogMapper wenn verfügbar
			if ($this->auditLogMapper !== null) {
				$this->auditLogMapper->log(
					$memberId,
					$action,
					$userId,
					$details,
					$ipAddress,
					$userAgent
				);
			}

			// Immer auch ins Log schreiben
			$this->logger->info('Privacy action: ' . json_encode([
				'member_id' => $memberId,
				'action' => $action,
				'user_id' => $userId,
				'details' => $details,
			]));
		} catch (\Exception $e) {
			$this->logger->error('Error logging privacy action: ' . $e->getMessage());
		}
	}

	/**
	 * Hole Gebühren für Mitglied
	 */
	private function getMemberFees(int $memberId): array {
		if ($this->feeMapper !== null) {
			try {
				$fees = $this->feeMapper->findByMemberId($memberId);
				return array_map(function($fee) {
					return [
						'id' => $fee->getId(),
						'amount' => $fee->getAmount(),
						'description' => $fee->getDescription(),
						'status' => $fee->getStatus(),
						'dueDate' => $fee->getDueDate(),
						'paidDate' => $fee->getPaidDate(),
					];
				}, $fees);
			} catch (\Exception $e) {
				$this->logger->error('Error getting member fees: ' . $e->getMessage());
			}
		}
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
		$customPolicy = $this->config->getAppValue('verein', 'privacy_policy_text', '');
		if (!empty($customPolicy)) {
			return $customPolicy;
		}
		return $this->getDefaultPrivacyPolicy();
	}

	/**
	 * Prüfe ob eine individuelle Datenschutzerklärung gesetzt ist
	 */
	public function hasCustomPrivacyPolicy(): bool {
		$customPolicy = $this->config->getAppValue('verein', 'privacy_policy_text', '');
		return !empty($customPolicy);
	}

	/**
	 * Speichere individuelle Datenschutzerklärung
	 */
	public function savePrivacyPolicy(string $policyText): void {
		$this->config->setAppValue('verein', 'privacy_policy_text', $policyText);
	}

	/**
	 * Setze Datenschutzerklärung auf Standard zurück
	 */
	public function resetPrivacyPolicy(): void {
		$this->config->deleteAppValue('verein', 'privacy_policy_text');
	}

	/**
	 * Standard Datenschutzerklärung
	 */
	private function getDefaultPrivacyPolicy(): string {
		// Vereinsdaten aus App-Einstellungen laden
		$vereinName = $this->config->getAppValue('verein', 'club_name', '[Vereinsname eintragen]');
		$vereinAddress = $this->config->getAppValue('verein', 'club_address', '[Adresse eintragen]');
		$vereinEmail = $this->config->getAppValue('verein', 'club_email', '[E-Mail eintragen]');
		
		return <<<HTML
<h1>Datenschutzerklärung</h1>

<h2>1. Verantwortlicher</h2>
<p>
<strong>{$vereinName}</strong><br>
{$vereinAddress}
</p>

<h2>2. Erhobene Daten</h2>
<p>Im Rahmen der Vereinsverwaltung werden folgende Daten erhoben und verarbeitet:</p>
<ul>
<li><strong>Persönliche Daten:</strong> Name, Vorname, E-Mail-Adresse, Telefonnummer, Geburtsdatum</li>
<li><strong>Adressdaten:</strong> Straße, Hausnummer, Postleitzahl, Stadt</li>
<li><strong>Bankdaten:</strong> IBAN, BIC (für SEPA-Lastschrifteinzug der Mitgliedsbeiträge)</li>
<li><strong>Mitgliedschaftsdaten:</strong> Eintrittsdatum, Mitgliedsstatus, Rollen im Verein</li>
<li><strong>Finanzdaten:</strong> Gebühren, Zahlungsstatus, Transaktionshistorie</li>
</ul>

<h2>3. Zweck der Datenverarbeitung</h2>
<p>Die Daten werden ausschließlich für folgende Zwecke verarbeitet:</p>
<ul>
<li>Verwaltung der Vereinsmitgliedschaft</li>
<li>Einzug von Mitgliedsbeiträgen per SEPA-Lastschrift</li>
<li>Kommunikation mit Vereinsmitgliedern</li>
<li>Erstellung von Mitgliederstatistiken (anonymisiert)</li>
</ul>

<h2>4. Rechtliche Grundlage (DSGVO)</h2>
<ul>
<li><strong>Art. 6 Abs. 1 lit. a DSGVO:</strong> Einwilligung (z.B. Newsletter, Marketing)</li>
<li><strong>Art. 6 Abs. 1 lit. b DSGVO:</strong> Vertragserfüllung (Mitgliedschaftsvertrag)</li>
<li><strong>Art. 6 Abs. 1 lit. f DSGVO:</strong> Berechtigte Interessen des Vereins</li>
</ul>

<h2>5. Ihre Rechte nach DSGVO</h2>
<p>Als betroffene Person haben Sie folgende Rechte:</p>
<ul>
<li><strong>Art. 15 DSGVO - Auskunftsrecht:</strong> Sie können jederzeit Ihre gespeicherten Daten exportieren</li>
<li><strong>Art. 16 DSGVO - Berichtigung:</strong> Sie können fehlerhafte Daten korrigieren lassen</li>
<li><strong>Art. 17 DSGVO - Löschung:</strong> Sie können die Löschung Ihrer Daten verlangen</li>
<li><strong>Art. 18 DSGVO - Einschränkung:</strong> Sie können die Verarbeitung einschränken lassen</li>
<li><strong>Art. 20 DSGVO - Datenportabilität:</strong> Sie können Ihre Daten in maschinenlesbarem Format erhalten</li>
<li><strong>Art. 21 DSGVO - Widerspruch:</strong> Sie können der Verarbeitung widersprechen</li>
</ul>

<h2>6. Speicherdauer</h2>
<p>Ihre Daten werden für die Dauer der Mitgliedschaft gespeichert. Nach Beendigung der Mitgliedschaft werden die Daten entsprechend der gesetzlichen Aufbewahrungsfristen (in der Regel 10 Jahre für steuerrelevante Unterlagen) aufbewahrt und anschließend gelöscht.</p>

<h2>7. Datensicherheit</h2>
<p>Wir setzen technische und organisatorische Sicherheitsmaßnahmen ein, um Ihre Daten gegen Manipulation, Verlust, Zerstörung oder gegen den Zugriff unberechtigter Personen zu schützen. Die Daten werden verschlüsselt auf Servern innerhalb der EU gespeichert.</p>

<h2>8. Kontakt</h2>
<p>Bei Fragen zum Datenschutz wenden Sie sich bitte an:<br>
<strong>Datenschutzbeauftragter:</strong> {$vereinEmail}</p>

<p><em>Stand: Dezember 2025</em></p>
HTML;
	}
}
