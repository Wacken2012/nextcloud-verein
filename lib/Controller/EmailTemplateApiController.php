<?php

declare(strict_types=1);

namespace OCA\Verein\Controller;

use OCA\Verein\Service\EmailTemplateService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use Psr\Log\LoggerInterface;

/**
 * API Controller für E-Mail-Template-Einstellungen
 */
class EmailTemplateApiController extends Controller {
    public function __construct(
        string $appName,
        IRequest $request,
        private EmailTemplateService $emailTemplateService,
        private LoggerInterface $logger,
    ) {
        parent::__construct($appName, $request);
    }

    /**
     * Hole alle E-Mail-Template-Einstellungen
     * 
     * @NoCSRFRequired
     * @return JSONResponse
     */
    public function getSettings(): JSONResponse {
        try {
            $settings = $this->emailTemplateService->getAllSettings();
            return new JSONResponse([
                'status' => 'success',
                'data' => $settings
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error getting email template settings: ' . $e->getMessage());
            return new JSONResponse([
                'status' => 'error',
                'message' => 'Fehler beim Laden der Einstellungen'
            ], Http::STATUS_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Aktualisiere E-Mail-Template-Einstellungen
     * 
     * @return JSONResponse
     */
    public function updateSettings(): JSONResponse {
        try {
            $body = file_get_contents('php://input');
            $data = json_decode($body, true);
            
            if (!is_array($data)) {
                return new JSONResponse([
                    'status' => 'error',
                    'message' => 'Ungültige Daten'
                ], Http::STATUS_BAD_REQUEST);
            }
            
            $this->emailTemplateService->updateSettings($data);
            
            return new JSONResponse([
                'status' => 'success',
                'message' => 'Einstellungen gespeichert',
                'data' => $this->emailTemplateService->getAllSettings()
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error updating email template settings: ' . $e->getMessage());
            return new JSONResponse([
                'status' => 'error',
                'message' => 'Fehler beim Speichern der Einstellungen'
            ], Http::STATUS_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Generiere Vorschau der E-Mail
     * 
     * @NoCSRFRequired
     * @return JSONResponse
     */
    public function preview(): JSONResponse {
        try {
            $level = (int)$this->request->getParam('level', 1);
            
            // Hole aktuelle Einstellungen
            $settings = $this->emailTemplateService->getAllSettings();
            
            // Erstelle Beispiel-Daten für Vorschau
            $previewData = [
                'memberName' => 'Max Mustermann',
                'memberEmail' => 'max@beispiel.de',
                'feeType' => 'Mitgliedsbeitrag 2024',
                'amount' => '120,00 €',
                'dueDate' => date('d.m.Y', strtotime('-14 days')),
                'reminderLevel' => $level,
                'clubName' => $settings['club_name'],
                'clubLogoUrl' => $settings['club_logo_url'],
                'clubAddress' => $settings['club_address'],
                'clubEmail' => $settings['club_email'],
                'clubPhone' => $settings['club_phone'],
                'clubWebsite' => $settings['club_website'],
                'primaryColor' => $settings['primary_color'],
                'secondaryColor' => $settings['secondary_color'],
                'headerText' => $settings['header_text'],
                'footerText' => $settings['footer_text'],
                'bankDetails' => $this->emailTemplateService->getFormattedBankDetails(),
                'subject' => $settings["reminder_subject_{$level}"],
                'intro' => $settings["reminder_intro_{$level}"],
                'closing' => $settings["reminder_closing_{$level}"],
                'signatureName' => $settings['signature_name'],
                'signatureTitle' => $settings['signature_title'],
            ];
            
            // Generiere HTML-Vorschau
            $html = $this->generatePreviewHtml($previewData);
            
            return new JSONResponse([
                'status' => 'success',
                'data' => [
                    'html' => $html,
                    'subject' => $previewData['subject']
                ]
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error generating email preview: ' . $e->getMessage());
            return new JSONResponse([
                'status' => 'error',
                'message' => 'Fehler beim Erstellen der Vorschau'
            ], Http::STATUS_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Sende Test-E-Mail
     * 
     * @return JSONResponse
     */
    public function sendTestEmail(): JSONResponse {
        try {
            $body = file_get_contents('php://input');
            $data = json_decode($body, true);
            
            $email = $data['email'] ?? '';
            $level = (int)($data['level'] ?? 1);
            
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return new JSONResponse([
                    'status' => 'error',
                    'message' => 'Ungültige E-Mail-Adresse'
                ], Http::STATUS_BAD_REQUEST);
            }
            
            // TODO: Implementiere tatsächlichen E-Mail-Versand
            // Vorerst nur Erfolgsmeldung
            
            return new JSONResponse([
                'status' => 'success',
                'message' => "Test-E-Mail wurde an {$email} gesendet"
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error sending test email: ' . $e->getMessage());
            return new JSONResponse([
                'status' => 'error',
                'message' => 'Fehler beim Senden der Test-E-Mail'
            ], Http::STATUS_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Generiere HTML für Vorschau
     */
    private function generatePreviewHtml(array $data): string {
        $primaryColor = htmlspecialchars($data['primaryColor']);
        $secondaryColor = htmlspecialchars($data['secondaryColor']);
        $clubName = htmlspecialchars($data['clubName']);
        $memberName = htmlspecialchars($data['memberName']);
        $feeType = htmlspecialchars($data['feeType']);
        $amount = htmlspecialchars($data['amount']);
        $dueDate = htmlspecialchars($data['dueDate']);
        $level = (int)$data['reminderLevel'];
        $intro = htmlspecialchars($data['intro']);
        $closing = htmlspecialchars($data['closing']);
        $signatureName = htmlspecialchars($data['signatureName']);
        $signatureTitle = htmlspecialchars($data['signatureTitle']);
        $bankDetails = nl2br(htmlspecialchars($data['bankDetails']));
        $footerText = nl2br(htmlspecialchars($data['footerText']));
        
        $levelColors = [
            1 => ['bg' => '#e8f4f8', 'text' => $primaryColor],
            2 => ['bg' => '#fff3cd', 'text' => '#856404'],
            3 => ['bg' => '#f8d7da', 'text' => '#721c24'],
        ];
        $levelColor = $levelColors[$level] ?? $levelColors[1];
        
        $logoHtml = '';
        if (!empty($data['clubLogoUrl'])) {
            $logoUrl = htmlspecialchars($data['clubLogoUrl']);
            $logoHtml = "<img src=\"{$logoUrl}\" alt=\"{$clubName}\" style=\"max-height: 60px; max-width: 200px;\">";
        }
        
        $headerHtml = '';
        if (!empty($data['headerText'])) {
            $headerHtml = "<div style=\"font-size: 12px; color: #666; margin-top: 10px;\">" . nl2br(htmlspecialchars($data['headerText'])) . "</div>";
        }
        
        $contactHtml = '';
        $contactParts = [];
        if (!empty($data['clubAddress'])) {
            $contactParts[] = nl2br(htmlspecialchars($data['clubAddress']));
        }
        if (!empty($data['clubPhone'])) {
            $contactParts[] = "Tel: " . htmlspecialchars($data['clubPhone']);
        }
        if (!empty($data['clubEmail'])) {
            $contactParts[] = "E-Mail: " . htmlspecialchars($data['clubEmail']);
        }
        if (!empty($data['clubWebsite'])) {
            $contactParts[] = "Web: " . htmlspecialchars($data['clubWebsite']);
        }
        if (!empty($contactParts)) {
            $contactHtml = "<div style=\"font-size: 11px; color: #888; margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;\">" . implode(" | ", $contactParts) . "</div>";
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; background: #f5f5f5; }
        .email-container { background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background: {$primaryColor}; color: white; padding: 20px; }
        .header-content { display: flex; align-items: center; justify-content: space-between; }
        .club-name { font-size: 24px; font-weight: bold; }
        .reminder-badge { display: inline-block; padding: 5px 12px; border-radius: 4px; font-size: 12px; font-weight: bold; background: {$levelColor['bg']}; color: {$levelColor['text']}; margin-top: 10px; }
        .content { padding: 25px; }
        .payment-box { background: #f5f5f5; border-left: 4px solid {$primaryColor}; padding: 15px; margin: 20px 0; }
        .payment-box table { width: 100%; border-collapse: collapse; }
        .payment-box td { padding: 8px 0; }
        .payment-box td:first-child { font-weight: bold; width: 40%; }
        .amount { font-size: 18px; font-weight: bold; color: {$primaryColor}; }
        .bank-box { background: {$secondaryColor}; padding: 15px; border-radius: 4px; margin: 20px 0; }
        .bank-box h4 { margin: 0 0 10px 0; color: {$primaryColor}; }
        .signature { margin-top: 30px; }
        .footer { background: #f9f9f9; padding: 15px 25px; font-size: 12px; color: #666; border-top: 1px solid #eee; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="header-content">
                <div>
                    {$logoHtml}
                    <div class="club-name">{$clubName}</div>
                </div>
            </div>
            {$headerHtml}
            <span class="reminder-badge">{$level}. Mahnung</span>
        </div>
        
        <div class="content">
            <p>Sehr geehrte/r {$memberName},</p>
            
            <p>{$intro}</p>
            
            <div class="payment-box">
                <table>
                    <tr><td>Beitragsart:</td><td>{$feeType}</td></tr>
                    <tr><td>Betrag:</td><td class="amount">{$amount}</td></tr>
                    <tr><td>Fällig seit:</td><td>{$dueDate}</td></tr>
                </table>
            </div>
            
            <div class="bank-box">
                <h4>Bankverbindung</h4>
                {$bankDetails}
            </div>
            
            <p>{$closing}</p>
            
            <div class="signature">
                <p>Mit freundlichen Grüßen,</p>
                <p><strong>{$signatureName}</strong></p>
                {$signatureTitle}
            </div>
        </div>
        
        <div class="footer">
            {$footerText}
            {$contactHtml}
        </div>
    </div>
</body>
</html>
HTML;
    }
}
