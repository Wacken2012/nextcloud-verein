<?php
/**
 * Klartext E-Mail-Template für Zahlungserinnerungen
 * 
 * Variablen:
 * - $_['memberName'] - Name des Mitglieds
 * - $_['feeType'] - Art des Beitrags
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

$intros = [
    1 => 'wir möchten Sie freundlich daran erinnern, dass folgende Zahlung noch aussteht:',
    2 => 'leider haben wir Ihre Zahlung trotz unserer ersten Erinnerung noch nicht erhalten:',
    3 => 'trotz unserer vorherigen Mahnungen ist folgende Zahlung nach wie vor offen:'
];

$closings = [
    1 => 'Falls Sie die Zahlung bereits getätigt haben, betrachten Sie dieses Schreiben bitte als gegenstandslos.',
    2 => 'Bei Fragen stehen wir Ihnen gerne zur Verfügung.',
    3 => 'Bei Nichtzahlung bis zum angegebenen Datum behalten wir uns weitere Schritte vor.'
];

echo $clubName . "\n";
echo str_repeat('=', strlen($clubName)) . "\n\n";

echo $reminderLevel . ". Zahlungserinnerung\n\n";

echo "Sehr geehrte/r " . $memberName . ",\n\n";

echo $intros[$reminderLevel] . "\n\n";

echo "Beitragsart: " . $feeType . "\n";
echo "Betrag:      " . $amount . "\n";
echo "Fällig seit: " . $dueDate . "\n\n";

if (!empty($bankDetails)) {
    echo "Bankverbindung:\n";
    echo $bankDetails . "\n\n";
}

echo $closings[$reminderLevel] . "\n\n";

echo "Mit freundlichen Grüßen,\n";
echo $clubName . "\n\n";

echo "---\n";
echo "Diese E-Mail wurde automatisch von der Vereinsverwaltung generiert.\n";
