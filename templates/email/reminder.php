<?php
/**
 * E-Mail-Template für Zahlungserinnerungen
 * 
 * Variablen:
 * - $_['memberName'] - Name des Mitglieds
 * - $_['memberEmail'] - E-Mail des Mitglieds
 * - $_['feeType'] - Art des Beitrags (z.B. "Mitgliedsbeitrag 2024")
 * - $_['amount'] - Betrag
 * - $_['dueDate'] - Fälligkeitsdatum
 * - $_['reminderLevel'] - Mahnstufe (1, 2 oder 3)
 * - $_['clubName'] - Name des Vereins
 * - $_['bankDetails'] - Bankverbindung
 */

$reminderLevel = $_['reminderLevel'] ?? 1;
$memberName = $_['memberName'] ?? 'Mitglied';
$feeType = $_['feeType'] ?? 'Mitgliedsbeitrag';
$amount = $_['amount'] ?? '0,00 €';
$dueDate = $_['dueDate'] ?? '';
$clubName = $_['clubName'] ?? 'Verein';
$bankDetails = $_['bankDetails'] ?? '';

// Betreffzeilen je nach Mahnstufe
$subjects = [
    1 => 'Freundliche Zahlungserinnerung',
    2 => 'Zweite Zahlungserinnerung',
    3 => 'Letzte Zahlungsaufforderung'
];

// Begrüßungen je nach Mahnstufe
$greetings = [
    1 => 'Sehr geehrte/r ' . htmlspecialchars($memberName) . ',',
    2 => 'Sehr geehrte/r ' . htmlspecialchars($memberName) . ',',
    3 => 'Sehr geehrte/r ' . htmlspecialchars($memberName) . ','
];

// Einleitungen je nach Mahnstufe
$intros = [
    1 => 'wir möchten Sie freundlich daran erinnern, dass folgende Zahlung noch aussteht:',
    2 => 'leider haben wir Ihre Zahlung trotz unserer ersten Erinnerung noch nicht erhalten. Wir bitten Sie erneut um Begleichung folgender Rechnung:',
    3 => 'trotz unserer vorherigen Mahnungen ist die folgende Zahlung nach wie vor offen. Wir fordern Sie hiermit letztmalig auf, den ausstehenden Betrag umgehend zu begleichen:'
];

// Abschlüsse je nach Mahnstufe
$closings = [
    1 => 'Falls Sie die Zahlung bereits getätigt haben, betrachten Sie dieses Schreiben bitte als gegenstandslos.',
    2 => 'Sollten Sie zwischenzeitlich die Zahlung veranlasst haben, bitten wir Sie, diese Mahnung als gegenstandslos zu betrachten. Bei Fragen stehen wir Ihnen gerne zur Verfügung.',
    3 => 'Bei Nichtzahlung bis zum angegebenen Datum behalten wir uns weitere Schritte vor. Bei finanziellen Schwierigkeiten setzen Sie sich bitte umgehend mit uns in Verbindung.'
];
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($subjects[$reminderLevel]); ?></title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            border-bottom: 2px solid #0082c9;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .club-name {
            font-size: 24px;
            font-weight: bold;
            color: #0082c9;
        }
        .reminder-level {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            margin-top: 10px;
        }
        .level-1 { background-color: #e8f4f8; color: #0082c9; }
        .level-2 { background-color: #fff3cd; color: #856404; }
        .level-3 { background-color: #f8d7da; color: #721c24; }
        .payment-details {
            background-color: #f5f5f5;
            border-left: 4px solid #0082c9;
            padding: 15px;
            margin: 20px 0;
        }
        .payment-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .payment-details td {
            padding: 8px 0;
        }
        .payment-details td:first-child {
            font-weight: bold;
            width: 40%;
        }
        .amount {
            font-size: 18px;
            font-weight: bold;
            color: #0082c9;
        }
        .bank-details {
            background-color: #e8f4f8;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .bank-details h3 {
            margin-top: 0;
            color: #0082c9;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
        }
        .signature {
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="club-name"><?php echo htmlspecialchars($clubName); ?></div>
        <span class="reminder-level level-<?php echo $reminderLevel; ?>">
            <?php echo $reminderLevel; ?>. Mahnung
        </span>
    </div>

    <p><?php echo $greetings[$reminderLevel]; ?></p>
    
    <p><?php echo $intros[$reminderLevel]; ?></p>

    <div class="payment-details">
        <table>
            <tr>
                <td>Beitragsart:</td>
                <td><?php echo htmlspecialchars($feeType); ?></td>
            </tr>
            <tr>
                <td>Betrag:</td>
                <td class="amount"><?php echo htmlspecialchars($amount); ?></td>
            </tr>
            <tr>
                <td>Fällig seit:</td>
                <td><?php echo htmlspecialchars($dueDate); ?></td>
            </tr>
        </table>
    </div>

    <?php if (!empty($bankDetails)): ?>
    <div class="bank-details">
        <h3>Bankverbindung</h3>
        <?php echo nl2br(htmlspecialchars($bankDetails)); ?>
    </div>
    <?php endif; ?>

    <p><?php echo $closings[$reminderLevel]; ?></p>

    <div class="signature">
        <p>Mit freundlichen Grüßen,</p>
        <p><strong><?php echo htmlspecialchars($clubName); ?></strong></p>
    </div>

    <div class="footer">
        <p>Diese E-Mail wurde automatisch von der Vereinsverwaltung generiert.</p>
        <p>Bei Fragen wenden Sie sich bitte an den Vorstand.</p>
    </div>
</body>
</html>
