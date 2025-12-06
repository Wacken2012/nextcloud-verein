<?php

declare(strict_types=1);

namespace OCA\Verein\Service;

use OCA\Verein\Db\ReminderMapper;
use OCP\Mail\IMailer;
use OCP\Mail\IEMailTemplate;
use DateTime;
use DateInterval;
use OCP\IConfig;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use OCA\Verein\Service\MemberService;

class ReminderService {
	private const REMINDER_CONFIG_KEY_PREFIX = 'reminder_';
	private const REMINDER_ENABLED = 'enabled';
	private const REMINDER_INTERVAL_LEVEL_1 = 'interval_level_1'; // Tage vor Fälligkeit
	private const REMINDER_INTERVAL_LEVEL_2 = 'interval_level_2'; // Tage nach Fälligkeit
	private const REMINDER_INTERVAL_LEVEL_3 = 'interval_level_3'; // Tage nach Level 2
	private const REMINDER_DAYS_BETWEEN = 'days_between_reminders';
	private const DEFAULT_APP_ID = 'verein';

	public function __construct(
		private ?ReminderMapper $reminderMapper = null,
		private ?IMailer $mailer = null,
		private ?LoggerInterface $logger = null,
		private ?MemberService $memberService = null,
		private ?IConfig $config = null,
		private string $appId = self::DEFAULT_APP_ID,
	) {
		$this->logger = $this->logger ?? new NullLogger();
	}

	/**
	 * Aktiviere automatische Mahnungen
	 */
	public function enableReminders(bool $enabled = true): void {
		$this->setConfigValue(self::REMINDER_ENABLED, $enabled ? '1' : '0');
	}

	/**
	 * Prüfe, ob Mahnungen aktiviert sind
	 */
	public function isEnabled(): bool {
		$setting = $this->getConfigValue(self::REMINDER_ENABLED, '0');
		return $setting === 'true' || $setting === '1';
	}

	/**
	 * Setze Mahnstufen-Intervalle (in Tagen)
	 */
	public function setReminderIntervals(int $level1, int $level2, int $level3): void {
		$this->setConfigValue(self::REMINDER_INTERVAL_LEVEL_1, (string)$level1);
		$this->setConfigValue(self::REMINDER_INTERVAL_LEVEL_2, (string)$level2);
		$this->setConfigValue(self::REMINDER_INTERVAL_LEVEL_3, (string)$level3);
	}

	/**
	 * Hole Mahnstufen-Intervalle
	 */
	public function getReminderIntervals(): array {
		return [
			'level_1' => (int)$this->getConfigValue(self::REMINDER_INTERVAL_LEVEL_1, '7'),
			'level_2' => (int)$this->getConfigValue(self::REMINDER_INTERVAL_LEVEL_2, '3'),
			'level_3' => (int)$this->getConfigValue(self::REMINDER_INTERVAL_LEVEL_3, '7'),
		];
	}

	/**
	 * Setze Tage zwischen wiederholten Mahnungen
	 */
	public function setDaysBetweenReminders(int $days): void {
		$this->setConfigValue(self::REMINDER_DAYS_BETWEEN, (string)$days);
	}

	/**
	 * Hole Tage zwischen wiederholten Mahnungen
	 */
	public function getDaysBetweenReminders(): int {
		return (int)$this->getConfigValue(self::REMINDER_DAYS_BETWEEN, '3');
	}

	/**
	 * Cronjob: Verarbeite fällige Mahnungen
	 */
	public function processDueReminders(): void {
		if (!$this->isEnabled()) {
			$this->logger->info('Reminders are disabled, skipping processing');
			return;
		}

		if (!$this->reminderMapper) {
			$this->logger->warning('ReminderMapper not available, skipping processing');
			return;
		}

		try {
			$now = new DateTime();
			$intervals = $this->getReminderIntervals();
			$daysBetween = $this->getDaysBetweenReminders();
			$maxLevel = $this->getMaxReminderLevel();

			// Hole alle fälligen Mahnungen
			$pendingReminders = $this->reminderMapper->findPending();
			$processedCount = 0;
			$sentCount = 0;
			$errorCount = 0;

			$this->logger->info('Processing ' . count($pendingReminders) . ' pending reminders');

			foreach ($pendingReminders as $reminder) {
				$processedCount++;
				
				// Prüfe, ob bereits maximale Mahnstufe erreicht
				$currentLevel = $reminder->getReminderLevel();
				if ($currentLevel >= $maxLevel) {
					$this->logger->info('Reminder #' . $reminder->getId() . ' already at max level ' . $maxLevel);
					continue;
				}
				
				// Berechne, ob Mahnung fällig ist
				$nextReminderDate = $reminder->getNextReminderDate();
				
				if ($nextReminderDate === null || $nextReminderDate <= $now) {
					// Mahnung versenden
					$success = $this->sendReminder($reminder);
					
					if ($success) {
						$sentCount++;
						
						// Eskaliere zur nächsten Mahnstufe
						$newLevel = $currentLevel + 1;
						$reminder->setReminderLevel($newLevel);
						
						// Berechne nächstes Mahndatum basierend auf Intervallen
						$nextInterval = $this->getIntervalForLevel($newLevel, $intervals);
						$nextReminderDate = (clone $now)->add(new DateInterval('P' . $nextInterval . 'D'));
						$reminder->setNextReminderDate($nextReminderDate);
						
						// Bei maximaler Stufe: Status auf "escalated" setzen
						if ($newLevel >= $maxLevel) {
							$reminder->setStatus('escalated');
							$this->logger->info('Reminder #' . $reminder->getId() . ' escalated to final level');
						}
						
						$this->reminderMapper->update($reminder);
						$this->logger->info('Reminder #' . $reminder->getId() . ' sent, now at level ' . $newLevel);
					} else {
						$errorCount++;
					}
				}
			}
			
			$this->logger->info("Reminder processing complete: {$processedCount} processed, {$sentCount} sent, {$errorCount} errors");
		} catch (\Exception $e) {
			$this->logger->error('Error processing reminders: ' . $e->getMessage());
		}
	}

	/**
	 * Hole das Intervall für eine bestimmte Mahnstufe
	 */
	private function getIntervalForLevel(int $level, array $intervals): int {
		return match($level) {
			1 => $intervals['level_1'] ?? 7,
			2 => $intervals['level_2'] ?? 14,
			3 => $intervals['level_3'] ?? 21,
			default => $this->getDaysBetweenReminders(),
		};
	}

	/**
	 * Hole maximale Mahnstufe (konfigurierbar)
	 */
	public function getMaxReminderLevel(): int {
		return (int)$this->getConfigValue('max_level', '3');
	}

	/**
	 * Setze maximale Mahnstufe
	 */
	public function setMaxReminderLevel(int $level): void {
		$this->setConfigValue('max_level', (string)$level);
	}

	/**
	 * Versende eine Mahnung an ein Mitglied
	 */
	public function sendReminder(object $reminder): bool {
		if (!$this->memberService || !$this->mailer) {
			$this->logger->warning('Mailer or MemberService missing, skipping reminder send');
			return false;
		}

		try {
			$memberId = $reminder->getMemberId();
			$member = $this->memberService->getById($memberId);

			if (!$member || empty($member->getEmail())) {
				$this->logReminder($reminder->getId(), $memberId, 'email_missing', false, 'Email address missing');
				return false;
			}

			// Hole Vereins-Konfiguration
			$clubName = $this->getClubName();
			$clubEmail = $this->getClubEmail();
			$bankDetails = $this->getBankDetails();

			// Baue Email
			$template = $this->buildReminderTemplate($member, $reminder, $clubName, $bankDetails);
			$message = $this->mailer->createMessage();
			
			// Empfänger
			$recipientName = trim($member->getFirstName() . ' ' . $member->getLastName());
			$message->setTo([$member->getEmail() => $recipientName ?: 'Mitglied']);
			
			// Absender (falls konfiguriert)
			if (!empty($clubEmail)) {
				$message->setFrom([$clubEmail => $clubName ?: 'Vereinsverwaltung']);
			}
			
			$message->useTemplate($template);

			// Versende
			$this->mailer->send($message);

			// Protokolliere erfolgreiche Mahnung
			$this->logReminder($reminder->getId(), $memberId, 'reminder_sent', true);
			$this->logger->info('Reminder sent to member #' . $memberId . ' (Level ' . $reminder->getReminderLevel() . ')');
			return true;
		} catch (\Exception $e) {
			$this->logger->error('Error sending reminder: ' . $e->getMessage());
			$this->logReminder($reminder->getId(), $memberId ?? 0, 'send_error', false, $e->getMessage());
			return false;
		}
	}

	/**
	 * Hole Vereinsname aus Konfiguration
	 */
	public function getClubName(): string {
		return $this->getConfigValue('club_name', 'Verein');
	}

	/**
	 * Setze Vereinsname
	 */
	public function setClubName(string $name): void {
		$this->setConfigValue('club_name', $name);
	}

	/**
	 * Hole Vereins-E-Mail aus Konfiguration
	 */
	public function getClubEmail(): string {
		return $this->getConfigValue('club_email', '');
	}

	/**
	 * Setze Vereins-E-Mail
	 */
	public function setClubEmail(string $email): void {
		$this->setConfigValue('club_email', $email);
	}

	/**
	 * Hole Bankverbindung aus Konfiguration
	 */
	public function getBankDetails(): string {
		return $this->getConfigValue('bank_details', '');
	}

	/**
	 * Setze Bankverbindung
	 */
	public function setBankDetails(string $details): void {
		$this->setConfigValue('bank_details', $details);
	}

	/**
	 * Erstelle eine neue Mahnung für ein Mitglied
	 */
	public function createReminder(int $memberId, int $feeId, DateTime $dueDate): ?object {
		if (!$this->reminderMapper) {
			$this->logger->warning('ReminderMapper not available, cannot create reminder');
			return null;
		}

		try {
			$reminder = new class {
				public $memberId;
				public $feeId;
				public $reminderLevel = 1;
				public $status = 'pending';
				public $dueDate;
				public $nextReminderDate;
				public $createdAt;

				public function getMemberId() { return $this->memberId; }
				public function getFeeId() { return $this->feeId; }
				public function getReminderLevel() { return $this->reminderLevel; }
				public function getStatus() { return $this->status; }
				public function getDueDate() { return $this->dueDate; }
				public function getNextReminderDate() { return $this->nextReminderDate; }
				public function getCreatedAt() { return $this->createdAt; }

				public function setMemberId($v) { $this->memberId = $v; return $this; }
				public function setFeeId($v) { $this->feeId = $v; return $this; }
				public function setReminderLevel($v) { $this->reminderLevel = $v; return $this; }
				public function setStatus($v) { $this->status = $v; return $this; }
				public function setDueDate($v) { $this->dueDate = $v; return $this; }
				public function setNextReminderDate($v) { $this->nextReminderDate = $v; return $this; }
			};

			$reminder->memberId = $memberId;
			$reminder->feeId = $feeId;
			$reminder->dueDate = $dueDate;
			$reminder->nextReminderDate = $dueDate; // Erste Mahnung am Fälligkeitstag
			$reminder->createdAt = new DateTime();

			return $this->reminderMapper->insert($reminder);
		} catch (\Exception $e) {
			$this->logger->error('Error creating reminder: ' . $e->getMessage());
			return null;
		}
	}

	/**
	 * Lösche Mahnung
	 */
	public function deleteReminder(int $reminderId): bool {
		if (!$this->reminderMapper) {
			$this->logger->warning('ReminderMapper not available, cannot delete reminder');
			return false;
		}
		try {
			$this->reminderMapper->delete($reminderId);
			return true;
		} catch (\Exception $e) {
			$this->logger->error('Error deleting reminder: ' . $e->getMessage());
			return false;
		}
	}

	/**
	 * Hole Mahnung
	 */
	public function getReminder(int $reminderId): ?object {
		if (!$this->reminderMapper) {
			return null;
		}
		try {
			return $this->reminderMapper->findById($reminderId);
		} catch (\Exception $e) {
			return null;
		}
	}

	/**
	 * Hole alle Mahnungen für ein Mitglied
	 */
	public function getRemindersForMember(int $memberId): array {
		if (!$this->reminderMapper) {
			return [];
		}
		return $this->reminderMapper->findByMemberId($memberId);
	}

	/**
	 * Baue Email-Template
	 */
	private function buildReminderTemplate(object $member, object $reminder, string $clubName = 'Verein', string $bankDetails = ''): IEMailTemplate {
		if (!$this->mailer) {
			throw new \RuntimeException('Mailer not configured');
		}
		$template = $this->mailer->createEMailTemplate('reminder_notification');
		
		$reminderLevel = $reminder->getReminderLevel();
		$dueDate = $reminder->getDueDate();
		$now = new DateTime();
		$daysOverdue = $dueDate instanceof DateTime ? $dueDate->diff($now)->days : 0;
		$dueDateStr = $dueDate instanceof DateTime ? $dueDate->format('d.m.Y') : 'unbekannt';

		// Betreff je nach Mahnstufe
		$subject = match($reminderLevel) {
			1 => 'Freundliche Zahlungserinnerung - ' . $clubName,
			2 => 'Zweite Zahlungserinnerung - ' . $clubName,
			3 => 'Letzte Zahlungsaufforderung - ' . $clubName,
			default => 'Zahlungserinnerung - ' . $clubName
		};

		$template->setSubject($subject);
		$template->addHeading($subject);

		// Personalisierte Begrüßung
		$memberName = trim($member->getFirstName() . ' ' . $member->getLastName());
		$template->addBodyText('Sehr geehrte/r ' . ($memberName ?: 'Mitglied') . ',');
		$template->addBodyText('');

		// Mahnstufen-spezifische Texte
		match($reminderLevel) {
			1 => $template->addBodyText(
				'wir möchten Sie freundlich daran erinnern, dass folgende Zahlung noch aussteht:'
			),
			2 => $template->addBodyText(
				'leider haben wir Ihre Zahlung trotz unserer ersten Erinnerung noch nicht erhalten. '
				. 'Die Zahlung war bereits am ' . $dueDateStr . ' fällig und ist damit ' . $daysOverdue . ' Tage überfällig.'
			),
			3 => $template->addBodyText(
				'trotz unserer vorherigen Mahnungen ist Ihre Zahlung nach wie vor offen. '
				. 'Die Zahlung ist ' . $daysOverdue . ' Tage überfällig. '
				. 'Wir fordern Sie hiermit letztmalig auf, den ausstehenden Betrag umgehend zu begleichen.'
			),
			default => $template->addBodyText('Es liegt eine ausstehende Zahlung vor.'),
		};

		$template->addBodyText('');
		
		// Zahlungsdetails
		$template->addBodyText('Fälligkeitsdatum: ' . $dueDateStr);
		
		// Bankverbindung falls vorhanden
		if (!empty($bankDetails)) {
			$template->addBodyText('');
			$template->addBodyText('Bankverbindung:');
			$template->addBodyText($bankDetails);
		}

		$template->addBodyText('');
		
		// Abschlusstext je nach Mahnstufe
		match($reminderLevel) {
			1 => $template->addBodyText(
				'Falls Sie die Zahlung bereits getätigt haben, betrachten Sie diese Erinnerung bitte als gegenstandslos.'
			),
			2 => $template->addBodyText(
				'Bei Fragen stehen wir Ihnen gerne zur Verfügung.'
			),
			3 => $template->addBodyText(
				'Bei Nichtzahlung behalten wir uns weitere Schritte vor. '
				. 'Bei finanziellen Schwierigkeiten setzen Sie sich bitte umgehend mit uns in Verbindung.'
			),
			default => null,
		};

		$template->addBodyText('');
		$template->addBodyText('Mit freundlichen Grüßen,');
		$template->addBodyText($clubName);

		// Footer
		$template->addFooter('Diese E-Mail wurde automatisch von der Vereinsverwaltung generiert.');

		return $template;
	}

	/**
	 * Protokolliere Mahnung
	 */
	private function logReminder(int $reminderId, int $memberId, string $action, bool $emailSent, ?string $error = null): void {
		try {
			$log = new class {
				public $reminderId;
				public $memberId;
				public $action;
				public $emailSent;
				public $emailError;
				public $createdAt;

				public function setReminderId($v) { $this->reminderId = $v; return $this; }
				public function setMemberId($v) { $this->memberId = $v; return $this; }
				public function setAction($v) { $this->action = $v; return $this; }
				public function setEmailSent($v) { $this->emailSent = $v; return $this; }
				public function setEmailError($v) { $this->emailError = $v; return $this; }
				public function setCreatedAt($v) { $this->createdAt = $v; return $this; }
			};

			$log->reminderId = $reminderId;
			$log->memberId = $memberId;
			$log->action = $action;
			$log->emailSent = $emailSent;
			$log->emailError = $error;
			$log->createdAt = new DateTime();

			// Note: Mapper würde in echtem Code verwendet
		} catch (\Exception $e) {
			$this->logger->error('Error logging reminder: ' . $e->getMessage());
		}
	}

	/**
	 * Hole alle Mahnung-Protokoll-Einträge
	 */
	public function getReminderLog(): array {
		if (!$this->reminderMapper) {
			return [];
		}
		try {
			return $this->reminderMapper->findAll();
		} catch (\Exception $e) {
			$this->logger->error('Error retrieving reminder log: ' . $e->getMessage());
			return [];
		}
	}

	private function getConfigValue(string $key, string $default): string {
		if (!$this->config) {
			return $default;
		}
		return $this->config->getAppValue($this->appId, self::REMINDER_CONFIG_KEY_PREFIX . $key, $default);
	}

	private function setConfigValue(string $key, string $value): void {
		if (!$this->config) {
			return;
		}
		$this->config->setAppValue($this->appId, self::REMINDER_CONFIG_KEY_PREFIX . $key, $value);
	}
}
