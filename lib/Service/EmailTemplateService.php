<?php

declare(strict_types=1);

namespace OCA\Verein\Service;

use OCP\IConfig;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Service für E-Mail-Template-Einstellungen
 * 
 * Ermöglicht die Konfiguration von:
 * - Vereinslogo (URL)
 * - Briefkopf
 * - Fußzeile
 * - Farben
 * - Texte für jede Mahnstufe
 */
class EmailTemplateService {
    private const CONFIG_PREFIX = 'email_template_';
    private const DEFAULT_APP_ID = 'verein';

    public function __construct(
        private ?IConfig $config = null,
        private ?LoggerInterface $logger = null,
        private string $appId = self::DEFAULT_APP_ID,
    ) {
        $this->logger = $this->logger ?? new NullLogger();
    }

    /**
     * Hole alle E-Mail-Template-Einstellungen
     */
    public function getAllSettings(): array {
        return [
            // Vereins-Branding
            'club_name' => $this->getClubName(),
            'club_logo_url' => $this->getClubLogoUrl(),
            'club_address' => $this->getClubAddress(),
            'club_email' => $this->getClubEmail(),
            'club_phone' => $this->getClubPhone(),
            'club_website' => $this->getClubWebsite(),
            
            // Farben
            'primary_color' => $this->getPrimaryColor(),
            'secondary_color' => $this->getSecondaryColor(),
            
            // Briefkopf & Fußzeile
            'header_text' => $this->getHeaderText(),
            'footer_text' => $this->getFooterText(),
            
            // Bank-Details
            'bank_name' => $this->getBankName(),
            'bank_iban' => $this->getBankIban(),
            'bank_bic' => $this->getBankBic(),
            'bank_account_holder' => $this->getBankAccountHolder(),
            
            // Mahnstufen-Texte
            'reminder_subject_1' => $this->getReminderSubject(1),
            'reminder_subject_2' => $this->getReminderSubject(2),
            'reminder_subject_3' => $this->getReminderSubject(3),
            'reminder_intro_1' => $this->getReminderIntro(1),
            'reminder_intro_2' => $this->getReminderIntro(2),
            'reminder_intro_3' => $this->getReminderIntro(3),
            'reminder_closing_1' => $this->getReminderClosing(1),
            'reminder_closing_2' => $this->getReminderClosing(2),
            'reminder_closing_3' => $this->getReminderClosing(3),
            
            // Signatur
            'signature_name' => $this->getSignatureName(),
            'signature_title' => $this->getSignatureTitle(),
        ];
    }

    /**
     * Aktualisiere mehrere Einstellungen gleichzeitig
     */
    public function updateSettings(array $settings): void {
        $allowedKeys = array_keys($this->getAllSettings());
        
        foreach ($settings as $key => $value) {
            if (in_array($key, $allowedKeys, true)) {
                $this->setConfigValue($key, (string)$value);
            }
        }
        
        $this->logger->info('Email template settings updated');
    }

    // === Vereins-Branding ===
    
    public function getClubName(): string {
        return $this->getConfigValue('club_name', 'Verein');
    }

    public function setClubName(string $name): void {
        $this->setConfigValue('club_name', $name);
    }

    public function getClubLogoUrl(): string {
        return $this->getConfigValue('club_logo_url', '');
    }

    public function setClubLogoUrl(string $url): void {
        $this->setConfigValue('club_logo_url', $url);
    }

    public function getClubAddress(): string {
        return $this->getConfigValue('club_address', '');
    }

    public function setClubAddress(string $address): void {
        $this->setConfigValue('club_address', $address);
    }

    public function getClubEmail(): string {
        return $this->getConfigValue('club_email', '');
    }

    public function setClubEmail(string $email): void {
        $this->setConfigValue('club_email', $email);
    }

    public function getClubPhone(): string {
        return $this->getConfigValue('club_phone', '');
    }

    public function setClubPhone(string $phone): void {
        $this->setConfigValue('club_phone', $phone);
    }

    public function getClubWebsite(): string {
        return $this->getConfigValue('club_website', '');
    }

    public function setClubWebsite(string $website): void {
        $this->setConfigValue('club_website', $website);
    }

    // === Farben ===
    
    public function getPrimaryColor(): string {
        return $this->getConfigValue('primary_color', '#0082c9');
    }

    public function setPrimaryColor(string $color): void {
        $this->setConfigValue('primary_color', $color);
    }

    public function getSecondaryColor(): string {
        return $this->getConfigValue('secondary_color', '#e8f4f8');
    }

    public function setSecondaryColor(string $color): void {
        $this->setConfigValue('secondary_color', $color);
    }

    // === Briefkopf & Fußzeile ===
    
    public function getHeaderText(): string {
        return $this->getConfigValue('header_text', '');
    }

    public function setHeaderText(string $text): void {
        $this->setConfigValue('header_text', $text);
    }

    public function getFooterText(): string {
        $default = "Diese E-Mail wurde automatisch von der Vereinsverwaltung generiert.\nBei Fragen wenden Sie sich bitte an den Vorstand.";
        return $this->getConfigValue('footer_text', $default);
    }

    public function setFooterText(string $text): void {
        $this->setConfigValue('footer_text', $text);
    }

    // === Bank-Details ===
    
    public function getBankName(): string {
        return $this->getConfigValue('bank_name', '');
    }

    public function setBankName(string $name): void {
        $this->setConfigValue('bank_name', $name);
    }

    public function getBankIban(): string {
        return $this->getConfigValue('bank_iban', '');
    }

    public function setBankIban(string $iban): void {
        $this->setConfigValue('bank_iban', $iban);
    }

    public function getBankBic(): string {
        return $this->getConfigValue('bank_bic', '');
    }

    public function setBankBic(string $bic): void {
        $this->setConfigValue('bank_bic', $bic);
    }

    public function getBankAccountHolder(): string {
        return $this->getConfigValue('bank_account_holder', '');
    }

    public function setBankAccountHolder(string $holder): void {
        $this->setConfigValue('bank_account_holder', $holder);
    }

    /**
     * Formatierte Bankverbindung als String
     */
    public function getFormattedBankDetails(): string {
        $lines = [];
        
        if ($name = $this->getBankName()) {
            $lines[] = $name;
        }
        if ($holder = $this->getBankAccountHolder()) {
            $lines[] = "Kontoinhaber: " . $holder;
        }
        if ($iban = $this->getBankIban()) {
            $lines[] = "IBAN: " . $iban;
        }
        if ($bic = $this->getBankBic()) {
            $lines[] = "BIC: " . $bic;
        }
        
        return implode("\n", $lines);
    }

    // === Mahnstufen-Texte ===
    
    public function getReminderSubject(int $level): string {
        $defaults = [
            1 => 'Freundliche Zahlungserinnerung',
            2 => 'Zweite Zahlungserinnerung',
            3 => 'Letzte Zahlungsaufforderung',
        ];
        return $this->getConfigValue("reminder_subject_{$level}", $defaults[$level] ?? 'Zahlungserinnerung');
    }

    public function setReminderSubject(int $level, string $subject): void {
        $this->setConfigValue("reminder_subject_{$level}", $subject);
    }

    public function getReminderIntro(int $level): string {
        $defaults = [
            1 => 'wir möchten Sie freundlich daran erinnern, dass folgende Zahlung noch aussteht:',
            2 => 'leider haben wir Ihre Zahlung trotz unserer ersten Erinnerung noch nicht erhalten. Wir bitten Sie erneut um Begleichung folgender Rechnung:',
            3 => 'trotz unserer vorherigen Mahnungen ist die folgende Zahlung nach wie vor offen. Wir fordern Sie hiermit letztmalig auf, den ausstehenden Betrag umgehend zu begleichen:',
        ];
        return $this->getConfigValue("reminder_intro_{$level}", $defaults[$level] ?? '');
    }

    public function setReminderIntro(int $level, string $intro): void {
        $this->setConfigValue("reminder_intro_{$level}", $intro);
    }

    public function getReminderClosing(int $level): string {
        $defaults = [
            1 => 'Falls Sie die Zahlung bereits getätigt haben, betrachten Sie dieses Schreiben bitte als gegenstandslos.',
            2 => 'Sollten Sie zwischenzeitlich die Zahlung veranlasst haben, bitten wir Sie, diese Mahnung als gegenstandslos zu betrachten. Bei Fragen stehen wir Ihnen gerne zur Verfügung.',
            3 => 'Bei Nichtzahlung bis zum angegebenen Datum behalten wir uns weitere Schritte vor. Bei finanziellen Schwierigkeiten setzen Sie sich bitte umgehend mit uns in Verbindung.',
        ];
        return $this->getConfigValue("reminder_closing_{$level}", $defaults[$level] ?? '');
    }

    public function setReminderClosing(int $level, string $closing): void {
        $this->setConfigValue("reminder_closing_{$level}", $closing);
    }

    // === Signatur ===
    
    public function getSignatureName(): string {
        return $this->getConfigValue('signature_name', 'Der Vereinsvorstand');
    }

    public function setSignatureName(string $name): void {
        $this->setConfigValue('signature_name', $name);
    }

    public function getSignatureTitle(): string {
        return $this->getConfigValue('signature_title', '');
    }

    public function setSignatureTitle(string $title): void {
        $this->setConfigValue('signature_title', $title);
    }

    // === Private Helpers ===
    
    private function getConfigValue(string $key, string $default): string {
        if (!$this->config) {
            return $default;
        }
        return $this->config->getAppValue($this->appId, self::CONFIG_PREFIX . $key, $default);
    }

    private function setConfigValue(string $key, string $value): void {
        if (!$this->config) {
            return;
        }
        $this->config->setAppValue($this->appId, self::CONFIG_PREFIX . $key, $value);
    }
}
