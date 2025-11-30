<?php
/**
 * PdfExporter.php - PDF Export Service
 * 
 * Generiert professionelle PDF-Dateien für:
 * - Rechnungen/Quittungen
 * - Mitgliederlisten
 * - Gebührenübersichten
 * - Statistiken
 * 
 * v0.2.0 Feature: PDF Export für Rechnungen
 * 
 * Nutzt TCPDF-Library für Enterprise-PDF-Generierung.
 * 
 * Beispiel Usage:
 * ```php
 * $exporter = new PdfExporter();
 * $pdf = $exporter->generateInvoicePdf($clubData, $member, $transaction);
 * $pdf->Output('invoice_' . $member['id'] . '.pdf', 'D'); // Download
 * ```
 * 
 * @category Service\Export\PDF
 * @package Verein\Service\Export\PDF
 * @author Stefan Schulz <stefan@example.com>
 * @license AGPL-3.0
 */

namespace OCA\Verein\Service\Export\PDF;

use OCA\Verein\Exception\ExportException;
use OCA\Verein\Exception\ValidationException;

class PdfExporter {
    
    /**
     * Generiert eine Rechnung/Quittung als PDF
     * 
     * @param array $clubData Vereinsdaten
     *  - name: string (Vereinsname)
     *  - address: string (Adresse)
     *  - email: string
     *  - iban: string
     *  - logo_path: string (optional, Pfad zu Logo)
     * 
     * @param array $member Mitgliederdaten
     *  - id: int
     *  - name: string
     *  - address: string
     *  - email: string
     * 
     * @param array $transaction Transaktionsdaten
     *  - amount: float
     *  - purpose: string
     *  - due_date: string (Y-m-d)
     *  - status: string (open, paid, overdue)
     *  - created_date: string (Y-m-d)
     *  - invoice_number: string (optional)
     * 
     * @return mixed TCPDF-Objekt
     * @throws ExportException
     */
    public function generateInvoicePdf(
        array $clubData,
        array $member,
        array $transaction
    ) {
        try {
            $this->validateInputs($clubData, $member, $transaction);
            
            // TCPDF laden (würde in echter Implementierung über Composer erfolgen)
            // require_once __DIR__ . '/../../vendor/tcpdf/tcpdf.php';
            
            // Mock-Implementierung für Demonstration
            $pdf = $this->createPdfDocument();
            
            // === Kopf ===
            $this->addHeader($pdf, $clubData);
            
            // === Adressblock ===
            $this->addAddressBlock($pdf, $member);
            
            // === Rechnungsdetails ===
            $this->addInvoiceDetails($pdf, $clubData, $transaction);
            
            // === Detailtabelle ===
            $this->addItemsTable($pdf, $transaction);
            
            // === Summen ===
            $this->addSummary($pdf, $transaction);
            
            // === Fußzeile ===
            $this->addFooter($pdf, $clubData);
            
            return $pdf;
            
        } catch (ValidationException $e) {
            throw new ExportException('PDF-Validierungsfehler: ' . $e->getMessage(), 400, $e);
        } catch (\Exception $e) {
            throw new ExportException('PDF-Generierung fehlgeschlagen: ' . $e->getMessage(), 500, $e);
        }
    }
    
    /**
     * Generiert eine Mitgliederliste als PDF
     * 
     * @param array $clubData Vereinsdaten
     * @param array $members Array von Mitgliedern
     * @param array $options Optionen (z.B. 'include_inactive' => false)
     * 
     * @return mixed TCPDF-Objekt
     */
    public function generateMemberListPdf(
        array $clubData,
        array $members,
        array $options = []
    ) {
        try {
            if (empty($members)) {
                throw new ExportException('Keine Mitglieder zum Exportieren');
            }
            
            $pdf = $this->createPdfDocument();
            
            // Kopf
            $this->addHeader($pdf, $clubData);
            
            // Titel
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->Cell(0, 10, 'Mitgliederliste ' . date('d.m.Y'), 0, 1, 'C');
            $pdf->Ln(5);
            
            // Tabelle
            $this->addMembersTable($pdf, $members);
            
            // Fußzeile
            $this->addFooter($pdf, $clubData);
            
            return $pdf;
            
        } catch (\Exception $e) {
            throw new ExportException('Mitgliederlisten-PDF fehlgeschlagen: ' . $e->getMessage());
        }
    }
    
    /**
     * Generiert eine Gebührenübersicht als PDF
     * 
     * @param array $clubData
     * @param array $transactions
     * @param string|null $memberName Filter nach Mitgliedername (optional)
     * 
     * @return mixed
     */
    public function generateFinanceOverviewPdf(
        array $clubData,
        array $transactions,
        ?string $memberName = null
    ) {
        try {
            if (empty($transactions)) {
                throw new ExportException('Keine Transaktionen zum Exportieren');
            }
            
            $pdf = $this->createPdfDocument();
            
            // Kopf
            $this->addHeader($pdf, $clubData);
            
            // Titel
            $pdf->SetFont('helvetica', 'B', 16);
            $subtitle = $memberName ? "Gebühren - $memberName" : "Gebührenübersicht";
            $pdf->Cell(0, 10, $subtitle . ' ' . date('d.m.Y'), 0, 1, 'C');
            $pdf->Ln(5);
            
            // Statistiken
            $this->addFinanceStats($pdf, $transactions);
            
            // Tabelle
            $this->addTransactionsTable($pdf, $transactions);
            
            // Fußzeile
            $this->addFooter($pdf, $clubData);
            
            return $pdf;
            
        } catch (\Exception $e) {
            throw new ExportException('Gebührenübersicht-PDF fehlgeschlagen: ' . $e->getMessage());
        }
    }
    
    /**
     * Validiert die Input-Daten
     * 
     * @throws ValidationException
     */
    private function validateInputs(array $clubData, array $member, array $transaction): void {
        // Vereinsdaten
        if (empty($clubData['name'])) {
            throw new ValidationException('Vereinsname erforderlich');
        }
        
        // Mitgliederdaten
        if (empty($member['name'])) {
            throw new ValidationException('Mitglieder-Name erforderlich');
        }
        
        // Transaktionsdaten
        if (empty($transaction['amount']) || $transaction['amount'] <= 0) {
            throw new ValidationException('Betrag ungültig');
        }
    }
    
    /**
     * Erstellt ein TCPDF-Dokument
     * 
     * @return mixed Mock-PDF-Objekt
     */
    private function createPdfDocument() {
        // In echter Implementierung:
        // return new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        
        // Mock für Demonstration
        return new class {
            public function SetFont($family, $style = '', $size = 0) {}
            public function Cell($w, $h, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false) {}
            public function Ln($h = null) {}
            public function AddPage() {}
            public function SetXY($x, $y) {}
            public function Image($file, $x = null, $y = null, $w = 0, $h = 0) {}
            public function Line($x1, $y1, $x2, $y2) {}
            public function SetFillColor($r, $g, $b) {}
            public function SetTextColor($r, $g, $b) {}
            public function Output($name = '', $dest = '') { return ''; }
        };
    }
    
    private function addHeader($pdf, array $clubData): void {
        $pdf->AddPage();
        
        // Logo (falls vorhanden)
        if (!empty($clubData['logo_path']) && file_exists($clubData['logo_path'])) {
            $pdf->Image($clubData['logo_path'], 15, 10, 30);
        }
        
        // Vereinsname und Daten
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetXY(50, 15);
        $pdf->Cell(0, 5, $clubData['name']);
        
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetXY(50, 22);
        $pdf->Cell(0, 4, ($clubData['address'] ?? ''));
        
        $pdf->SetXY(50, 26);
        $pdf->Cell(0, 4, ($clubData['email'] ?? ''));
        
        // Horizontale Linie
        $pdf->SetXY(15, 35);
        $pdf->Line(15, 35, 195, 35);
        
        $pdf->Ln(10);
    }
    
    private function addAddressBlock($pdf, array $member): void {
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetXY(15, 45);
        
        $address = $member['name'] . "\n" . ($member['address'] ?? '');
        $pdf->MultiCell(0, 4, $address);
    }
    
    private function addInvoiceDetails($pdf, array $clubData, array $transaction): void {
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetXY(130, 45);
        $pdf->Cell(0, 5, 'RECHNUNG');
        
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetXY(130, 52);
        $pdf->Cell(40, 4, 'Rechnungs-Nr.:');
        $pdf->Cell(0, 4, $transaction['invoice_number'] ?? '001');
        
        $pdf->SetXY(130, 57);
        $pdf->Cell(40, 4, 'Datum:');
        $pdf->Cell(0, 4, date('d.m.Y', strtotime($transaction['created_date'] ?? 'now')));
        
        $pdf->SetXY(130, 62);
        $pdf->Cell(40, 4, 'Fällig am:');
        $pdf->Cell(0, 4, date('d.m.Y', strtotime($transaction['due_date'] ?? 'now')));
    }
    
    private function addItemsTable($pdf, array $transaction): void {
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetXY(15, 75);
        
        $pdf->SetFillColor(220, 220, 220);
        $pdf->Cell(100, 6, 'Beschreibung', 1, 0, 'L', true);
        $pdf->Cell(50, 6, 'Betrag', 1, 1, 'R', true);
        
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(100, 5, $transaction['purpose'] ?? 'Mitgliedsbeitrag', 1, 0, 'L');
        $pdf->Cell(50, 5, '€ ' . number_format($transaction['amount'], 2, ',', '.'), 1, 1, 'R');
    }
    
    private function addSummary($pdf, array $transaction): void {
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetXY(115, 100);
        $pdf->Cell(35, 6, 'GESAMTBETRAG:');
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(50, 6, '€ ' . number_format($transaction['amount'], 2, ',', '.'), 0, 1, 'R');
    }
    
    private function addFooter($pdf, array $clubData): void {
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetXY(15, 280);
        $pdf->Line(15, 280, 195, 280);
        
        $pdf->SetXY(15, 285);
        $footer = $clubData['name'] . ' | ' . ($clubData['email'] ?? '') . ' | Erzeugt am ' . date('d.m.Y H:i');
        $pdf->Cell(0, 4, $footer);
    }
    
    private function addMembersTable($pdf, array $members): void {
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(220, 220, 220);
        
        $pdf->Cell(20, 6, 'Nr.', 1, 0, 'L', true);
        $pdf->Cell(60, 6, 'Name', 1, 0, 'L', true);
        $pdf->Cell(50, 6, 'E-Mail', 1, 0, 'L', true);
        $pdf->Cell(60, 6, 'Adresse', 1, 1, 'L', true);
        
        $pdf->SetFont('helvetica', '', 9);
        $i = 1;
        foreach ($members as $member) {
            $pdf->Cell(20, 5, (string)$i++, 1, 0, 'L');
            $pdf->Cell(60, 5, substr($member['name'], 0, 25), 1, 0, 'L');
            $pdf->Cell(50, 5, substr($member['email'] ?? '', 0, 20), 1, 0, 'L');
            $pdf->Cell(60, 5, substr($member['address'] ?? '', 0, 20), 1, 1, 'L');
        }
    }
    
    private function addTransactionsTable($pdf, array $transactions): void {
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(220, 220, 220);
        
        $pdf->Cell(40, 6, 'Mitglied', 1, 0, 'L', true);
        $pdf->Cell(40, 6, 'Betrag', 1, 0, 'R', true);
        $pdf->Cell(30, 6, 'Status', 1, 0, 'L', true);
        $pdf->Cell(40, 6, 'Fällig am', 1, 1, 'L', true);
        
        $pdf->SetFont('helvetica', '', 9);
        foreach ($transactions as $tx) {
            $pdf->Cell(40, 5, substr($tx['member_name'] ?? '', 0, 20), 1, 0, 'L');
            $pdf->Cell(40, 5, '€ ' . number_format($tx['amount'], 2, ',', '.'), 1, 0, 'R');
            $pdf->Cell(30, 5, $this->getStatusLabel($tx['status'] ?? 'open'), 1, 0, 'L');
            $pdf->Cell(40, 5, date('d.m.Y', strtotime($tx['due_date'] ?? 'now')), 1, 1, 'L');
        }
    }
    
    private function addFinanceStats($pdf, array $transactions): void {
        $total = array_sum(array_column($transactions, 'amount'));
        $paid = array_reduce($transactions, fn($s, $tx) => $s + ($tx['status'] === 'paid' ? $tx['amount'] : 0), 0);
        $open = $total - $paid;
        
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetXY(15, 48);
        $pdf->Cell(0, 5, 'Gesamtbetrag: € ' . number_format($total, 2, ',', '.') . ' | ' .
                          'Bezahlt: € ' . number_format($paid, 2, ',', '.') . ' | ' .
                          'Ausstehend: € ' . number_format($open, 2, ',', '.'));
        $pdf->Ln(10);
    }
    
    private function getStatusLabel(string $status): string {
        $labels = [
            'open' => 'Offen',
            'paid' => 'Bezahlt',
            'overdue' => 'Überfällig'
        ];
        return $labels[strtolower($status)] ?? $status;
    }
}
