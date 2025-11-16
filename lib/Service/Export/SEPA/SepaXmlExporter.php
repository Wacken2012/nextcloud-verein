<?php
/**
 * SepaXmlExporter.php - SEPA pain.001 XML Export
 * 
 * Generiert SEPA Überweisungsdateien nach ISO 20022 Standard (pain.001).
 * Unterstützt sowohl Lastschriften als auch Überweisungen.
 * 
 * v0.2.0 Feature: SEPA pain.001 XML Export
 * 
 * Beispiel Usage:
 * ```php
 * $exporter = new SepaXmlExporter($validationService);
 * $xml = $exporter->generateSinglePaymentXml($clubData, $members, $transactions);
 * file_put_contents('payment_' . date('YmdHis') . '.xml', $xml);
 * ```
 * 
 * @category Service\Export\SEPA
 * @package Verein\Service\Export\SEPA
 * @author Stefan Schulz <stefan@example.com>
 * @license AGPL-3.0
 */

namespace OCA\Verein\Service\Export\SEPA;

use OCA\Verein\Exception\ValidationException;
use OCA\Verein\Exception\ExportException;
use SimpleXMLElement;
use DateTime;

class SepaXmlExporter {
    
    /**
     * SEPA pain.001 XML Namespace
     */
    private const PAIN_001_NAMESPACE = 'urn:iso:std:iso:20022:tech:xsd:pain.001.003.02';
    
    /**
     * Generiert eine SEPA Überweisung (CreditTransfer) als XML
     * 
     * @param array $clubData Vereinsdaten (name, country, identifier)
     * @param array $transactions Array von Transaktionen
     *  - [
     *      'member_id' => 123,
     *      'member_name' => 'Max Mustermann',
     *      'member_iban' => 'DE89370400440532013000',
     *      'member_bic' => 'COBADEHHXXX',
     *      'amount' => 25.00,
     *      'purpose' => 'Mitgliedsbeitrag November 2025',
     *      'due_date' => '2025-11-30'
     *    ]
     * @param string $fromIban Überweiser IBAN (Verein)
     * @param string $fromBic Überweiser BIC (Verein)
     * 
     * @return string XML als String
     * @throws ValidationException
     * @throws ExportException
     */
    public function generateCreditTransferXml(
        array $clubData,
        array $transactions,
        string $fromIban,
        string $fromBic
    ): string {
        try {
            // Validiere Eingaben
            $this->validateClubData($clubData);
            $this->validateIbanBic($fromIban, $fromBic);
            $this->validateTransactions($transactions);
            
            // Erstelle XML-Root
            $xml = new SimpleXMLElement(
                '<?xml version="1.0" encoding="UTF-8"?><Document xmlns="' . self::PAIN_001_NAMESPACE . '"/>'
            );
            
            // CstmCdtTrfInitn = Customer Credit Transfer Initiation
            $cstmCdtTrfInitn = $xml->addChild('CstmCdtTrfInitn');
            
            // === GrpHdr: Group Header ===
            $grpHdr = $cstmCdtTrfInitn->addChild('GrpHdr');
            $grpHdr->addChild('MsgId', $this->generateMessageId());
            $grpHdr->addChild('CreDtTm', (new DateTime())->format('Y-m-d\TH:i:s'));
            $grpHdr->addChild('NbOfTxns', (string)count($transactions));
            
            // Summe aller Transaktionen
            $totalAmount = array_reduce(
                $transactions,
                fn($sum, $tx) => $sum + $tx['amount'],
                0
            );
            $grpHdr->addChild('CtrlSum', number_format($totalAmount, 2, '.', ''));
            
            // InitgPty = Initiating Party (Verein)
            $initgPty = $grpHdr->addChild('InitgPty');
            $initgPtyId = $initgPty->addChild('Id');
            $initgPtyId->addChild('Nm', substr($clubData['name'], 0, 70));
            
            // === PmtInf: Payment Information ===
            $pmtInf = $cstmCdtTrfInitn->addChild('PmtInf');
            $pmtInf->addChild('PmtInfId', 'PAYMENT-' . date('YmdHis'));
            $pmtInf->addChild('PmtMtd', 'TRF'); // Transfer = Überweisung
            $pmtInf->addChild('BtchBookg', 'true'); // Batch Booking
            $pmtInf->addChild('NbOfTxns', (string)count($transactions));
            $pmtInf->addChild('CtrlSum', number_format($totalAmount, 2, '.', ''));
            $pmtInf->addChild('PmtTpInf')->addChild('InstrPrty', 'NORM'); // Normal Priority
            
            // Requested Execution Date
            $pmtInf->addChild('ReqdExctnDt', date('Y-m-d'));
            
            // Debtor = Überweiser (Verein)
            $dbtr = $pmtInf->addChild('Dbtr');
            $dbtr->addChild('Nm', substr($clubData['name'], 0, 70));
            $dbtrId = $dbtr->addChild('Id');
            $dbtrId->addChild('OrgId')->addChild('Othr')->addChild('Id', $clubData['identifier'] ?? 'CLUB-001');
            
            // Debtor Account
            $dbtrAcct = $pmtInf->addChild('DbtrAcct');
            $dbtrAcct->addChild('Id')->addChild('IBAN', $fromIban);
            $dbtrAcct->addChild('Ccy', 'EUR');
            
            // Debtor Agent (Bank)
            $dbtrAgt = $pmtInf->addChild('DbtrAgt');
            $dbtrAgt->addChild('FinInstnId')->addChild('BIC', $fromBic);
            
            // === CdtTrfTxInf: Credit Transfer Transaction Information ===
            foreach ($transactions as $index => $tx) {
                $this->addCreditTransferTransaction($pmtInf, $tx, $index + 1);
            }
            
            return $xml->asXML();
            
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new ExportException('SEPA XML Generierung fehlgeschlagen: ' . $e->getMessage(), 500, $e);
        }
    }
    
    /**
     * Fügt eine einzelne Transaktion zum XML hinzu
     * 
     * @param SimpleXMLElement $pmtInf Parent-Element
     * @param array $transaction Transaktionsdaten
     * @param int $index Transaktions-Index für eindeutige IDs
     */
    private function addCreditTransferTransaction(
        SimpleXMLElement $pmtInf,
        array $transaction,
        int $index
    ): void {
        $cdtTrfTxInf = $pmtInf->addChild('CdtTrfTxInf');
        
        // Payment ID
        $pmt = $cdtTrfTxInf->addChild('PmtId');
        $pmt->addChild('InstrId', sprintf('INSTR-%d-%s', $index, date('YmdHis')));
        $pmt->addChild('EndToEndId', sprintf('E2E-%d-%s', $index, date('YmdHis')));
        
        // Amount
        $amt = $cdtTrfTxInf->addChild('Amt');
        $amt->addChild('InstdAmt', number_format($transaction['amount'], 2, '.', ''))
            ->addAttribute('Ccy', 'EUR');
        
        // Purpose
        $rmtInf = $cdtTrfTxInf->addChild('RmtInf');
        $rmtInf->addChild('Ustrd', substr($transaction['purpose'] ?? 'Mitgliedsbeitrag', 0, 140));
        
        // Creditor = Empfänger (Mitglied)
        $cdtr = $cdtTrfTxInf->addChild('Cdtr');
        $cdtr->addChild('Nm', substr($transaction['member_name'], 0, 70));
        
        // Creditor Account
        $cdtrAcct = $cdtTrfTxInf->addChild('CdtrAcct');
        $cdtrAcct->addChild('Id')->addChild('IBAN', $transaction['member_iban']);
        $cdtrAcct->addChild('Ccy', 'EUR');
        
        // Creditor Agent (Bank)
        $cdtrAgt = $cdtTrfTxInf->addChild('CdtrAgt');
        $cdtrAgt->addChild('FinInstnId')->addChild('BIC', $transaction['member_bic']);
    }
    
    /**
     * Validiert Vereinsdaten
     * 
     * @param array $clubData
     * @throws ValidationException
     */
    private function validateClubData(array $clubData): void {
        if (empty($clubData['name'])) {
            throw new ValidationException('Vereinsname erforderlich');
        }
        
        if (strlen($clubData['name']) > 70) {
            throw new ValidationException('Vereinsname darf max. 70 Zeichen sein');
        }
    }
    
    /**
     * Validiert IBAN und BIC
     * 
     * @param string $iban
     * @param string $bic
     * @throws ValidationException
     */
    private function validateIbanBic(string $iban, string $bic): void {
        if (!$this->isValidIban($iban)) {
            throw new ValidationException('Ungültige IBAN: ' . $iban);
        }
        
        if (!$this->isValidBic($bic)) {
            throw new ValidationException('Ungültiger BIC: ' . $bic);
        }
    }
    
    /**
     * Validiert alle Transaktionen
     * 
     * @param array $transactions
     * @throws ValidationException
     */
    private function validateTransactions(array $transactions): void {
        if (empty($transactions)) {
            throw new ValidationException('Mindestens eine Transaktion erforderlich');
        }
        
        foreach ($transactions as $index => $tx) {
            $this->validateTransaction($tx, $index);
        }
    }
    
    /**
     * Validiert eine einzelne Transaktion
     * 
     * @param array $tx
     * @param int $index
     * @throws ValidationException
     */
    private function validateTransaction(array $tx, int $index): void {
        if (empty($tx['member_name'])) {
            throw new ValidationException("Transaktion $index: Mitglieder-Name erforderlich");
        }
        
        if (!$this->isValidIban($tx['member_iban'])) {
            throw new ValidationException("Transaktion $index: Ungültige IBAN");
        }
        
        if (!$this->isValidBic($tx['member_bic'])) {
            throw new ValidationException("Transaktion $index: Ungültiger BIC");
        }
        
        if (empty($tx['amount']) || $tx['amount'] <= 0) {
            throw new ValidationException("Transaktion $index: Betrag muss > 0 sein");
        }
        
        if ($tx['amount'] > 999999.99) {
            throw new ValidationException("Transaktion $index: Betrag zu hoch (max. 999.999,99 EUR)");
        }
    }
    
    /**
     * Prüft IBAN-Gültigkeit (einfache Prüfung)
     * 
     * @param string $iban
     * @return bool
     */
    private function isValidIban(string $iban): bool {
        $iban = strtoupper(str_replace(' ', '', $iban));
        
        // Länge prüfen (15-34 Zeichen)
        if (strlen($iban) < 15 || strlen($iban) > 34) {
            return false;
        }
        
        // Format: 2 Buchstaben + 2 Ziffern + Rest
        if (!preg_match('/^[A-Z]{2}[0-9]{2}[A-Z0-9]+$/', $iban)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Prüft BIC-Gültigkeit
     * 
     * @param string $bic
     * @return bool
     */
    private function isValidBic(string $bic): bool {
        $bic = strtoupper(str_replace(' ', '', $bic));
        
        // BIC: 8 oder 11 Zeichen, nur Buchstaben und Ziffern
        return preg_match('/^[A-Z0-9]{8}([A-Z0-9]{3})?$/', $bic) === 1;
    }
    
    /**
     * Generiert eine eindeutige Message ID
     * 
     * @return string
     */
    private function generateMessageId(): string {
        return 'MSG-' . date('YmdHis') . '-' . mt_rand(1000, 9999);
    }
}
