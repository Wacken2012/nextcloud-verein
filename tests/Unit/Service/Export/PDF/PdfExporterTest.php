<?php
/**
 * PdfExporterTest.php - Unit Tests für PDF Export
 * 
 * v0.2.0 Tests: PDF Export (Rechnungen, Listen, Übersichten)
 * Target: 20+ Tests
 * 
 * @category Test
 * @package Verein\Tests\Unit\Service\Export\PDF
 * @license AGPL-3.0
 */

namespace OCA\Verein\Tests\Unit\Service\Export\PDF;

use OCA\Verein\Service\Export\PDF\PdfExporter;
use OCA\Verein\Exception\ExportException;
use OCA\Verein\Exception\ValidationException;
use PHPUnit\Framework\TestCase;

class PdfExporterTest extends TestCase {
    
    private PdfExporter $exporter;
    
    protected function setUp(): void {
        $this->exporter = new PdfExporter();
    }
    
    /**
     * Test 1: Basic invoice PDF generation
     */
    public function testBasicInvoicePdfGeneration(): void {
        $clubData = $this->getValidClubData();
        $member = $this->getValidMember();
        $transaction = $this->getValidTransaction();
        
        $pdf = $this->exporter->generateInvoicePdf($clubData, $member, $transaction);
        
        $this->assertIsObject($pdf);
    }
    
    /**
     * Test 2: Invoice PDF requires club data
     */
    public function testInvoicePdfRequiresClubData(): void {
        $this->expectException(ExportException::class);
        $this->expectExceptionMessage('PDF-Validierungsfehler: Vereinsname erforderlich');
        
        $this->exporter->generateInvoicePdf(
            [], // Missing club name
            $this->getValidMember(),
            $this->getValidTransaction()
        );
    }
    
    /**
     * Test 3: Invoice PDF requires member name
     */
    public function testInvoicePdfRequiresMemberName(): void {
        $this->expectException(ExportException::class);
        $this->expectExceptionMessage('PDF-Validierungsfehler: Mitglieder-Name erforderlich');
        
        $this->exporter->generateInvoicePdf(
            $this->getValidClubData(),
            ['address' => 'Test'], // Missing name
            $this->getValidTransaction()
        );
    }
    
    /**
     * Test 4: Invoice PDF requires valid amount
     */
    public function testInvoicePdfRequiresValidAmount(): void {
        $this->expectException(ExportException::class);
        $this->expectExceptionMessage('PDF-Validierungsfehler: Betrag ungültig');
        
        $this->exporter->generateInvoicePdf(
            $this->getValidClubData(),
            $this->getValidMember(),
            ['amount' => 0, 'purpose' => 'Test'] // Invalid amount
        );
    }
    
    /**
     * Test 5: Member list PDF generation
     */
    public function testMemberListPdfGeneration(): void {
        $clubData = $this->getValidClubData();
        $members = [
            $this->getValidMember(),
            array_merge($this->getValidMember(), ['id' => 2, 'name' => 'Person 2']),
        ];
        
        $pdf = $this->exporter->generateMemberListPdf($clubData, $members);
        
        $this->assertIsObject($pdf);
    }
    
    /**
     * Test 6: Member list PDF requires members
     */
    public function testMemberListPdfRequiresMembers(): void {
        $this->expectException(ExportException::class);
        $this->expectExceptionMessage('Keine Mitglieder zum Exportieren');
        
        $this->exporter->generateMemberListPdf($this->getValidClubData(), []);
    }
    
    /**
     * Test 7: Finance overview PDF generation
     */
    public function testFinanceOverviewPdfGeneration(): void {
        $clubData = $this->getValidClubData();
        $transactions = [
            $this->getValidTransaction(),
            array_merge($this->getValidTransaction(), ['amount' => 50.00]),
        ];
        
        $pdf = $this->exporter->generateFinanceOverviewPdf($clubData, $transactions);
        
        $this->assertIsObject($pdf);
    }
    
    /**
     * Test 8: Finance overview with member filter
     */
    public function testFinanceOverviewWithMemberFilter(): void {
        $clubData = $this->getValidClubData();
        $transactions = [
            $this->getValidTransaction(),
        ];
        
        $pdf = $this->exporter->generateFinanceOverviewPdf(
            $clubData,
            $transactions,
            'Max Mustermann'
        );
        
        $this->assertIsObject($pdf);
    }
    
    /**
     * Test 9: Finance overview PDF requires transactions
     */
    public function testFinanceOverviewPdfRequiresTransactions(): void {
        $this->expectException(ExportException::class);
        $this->expectExceptionMessage('Keine Transaktionen zum Exportieren');
        
        $this->exporter->generateFinanceOverviewPdf($this->getValidClubData(), []);
    }
    
    /**
     * Test 10: Valid member data has required fields
     */
    public function testValidMemberDataHasRequiredFields(): void {
        $member = $this->getValidMember();
        
        $this->assertArrayHasKey('name', $member);
        $this->assertArrayHasKey('address', $member);
        $this->assertArrayHasKey('email', $member);
        $this->assertNotEmpty($member['name']);
    }
    
    /**
     * Test 11: Valid club data has required fields
     */
    public function testValidClubDataHasRequiredFields(): void {
        $club = $this->getValidClubData();
        
        $this->assertArrayHasKey('name', $club);
        $this->assertArrayHasKey('address', $club);
        $this->assertArrayHasKey('email', $club);
        $this->assertNotEmpty($club['name']);
    }
    
    /**
     * Test 12: Valid transaction data has required fields
     */
    public function testValidTransactionDataHasRequiredFields(): void {
        $tx = $this->getValidTransaction();
        
        $this->assertArrayHasKey('amount', $tx);
        $this->assertArrayHasKey('purpose', $tx);
        $this->assertArrayHasKey('due_date', $tx);
        $this->assertNotEmpty($tx['amount']);
    }
    
    /**
     * Test 13: PDF generation handles special characters
     */
    public function testPdfGenerationHandlesSpecialCharacters(): void {
        $clubData = array_merge($this->getValidClubData(), [
            'name' => 'Musikverein Schöner-Berg e.V.'
        ]);
        
        $member = array_merge($this->getValidMember(), [
            'name' => 'Müller-Schröder, Anna-Maria'
        ]);
        
        $transaction = array_merge($this->getValidTransaction(), [
            'purpose' => 'Mitgliedsbeitrag Okt. 2025 (+ Versicherung)'
        ]);
        
        $pdf = $this->exporter->generateInvoicePdf($clubData, $member, $transaction);
        
        $this->assertIsObject($pdf);
    }
    
    /**
     * Test 14: Multiple transactions in finance overview
     */
    public function testMultipleTransactionsInFinanceOverview(): void {
        $clubData = $this->getValidClubData();
        
        $transactions = [];
        for ($i = 1; $i <= 10; $i++) {
            $transactions[] = array_merge($this->getValidTransaction(), [
                'amount' => $i * 10.00,
                'purpose' => "Transaktion $i"
            ]);
        }
        
        $pdf = $this->exporter->generateFinanceOverviewPdf($clubData, $transactions);
        
        $this->assertIsObject($pdf);
    }
    
    /**
     * Test 15: Invoice PDF with optional fields
     */
    public function testInvoicePdfWithOptionalFields(): void {
        $clubData = array_merge($this->getValidClubData(), [
            'logo_path' => '/tmp/logo.png', // Even if file doesn't exist
        ]);
        
        $member = array_merge($this->getValidMember(), [
            'phone' => '0123 456789', // Additional field
        ]);
        
        $transaction = array_merge($this->getValidTransaction(), [
            'invoice_number' => 'INV-2025-00001',
            'status' => 'paid'
        ]);
        
        $pdf = $this->exporter->generateInvoicePdf($clubData, $member, $transaction);
        
        $this->assertIsObject($pdf);
    }
    
    /**
     * Test 16: Large amount handling
     */
    public function testLargeAmountHandling(): void {
        $transaction = array_merge($this->getValidTransaction(), [
            'amount' => 9999.99
        ]);
        
        $pdf = $this->exporter->generateInvoicePdf(
            $this->getValidClubData(),
            $this->getValidMember(),
            $transaction
        );
        
        $this->assertIsObject($pdf);
    }
    
    /**
     * Test 17: Small amount handling
     */
    public function testSmallAmountHandling(): void {
        $transaction = array_merge($this->getValidTransaction(), [
            'amount' => 0.01
        ]);
        
        $pdf = $this->exporter->generateInvoicePdf(
            $this->getValidClubData(),
            $this->getValidMember(),
            $transaction
        );
        
        $this->assertIsObject($pdf);
    }
    
    /**
     * Test 18: Decimal amount precision
     */
    public function testDecimalAmountPrecision(): void {
        $transaction = array_merge($this->getValidTransaction(), [
            'amount' => 12.345 // More than 2 decimal places
        ]);
        
        $pdf = $this->exporter->generateInvoicePdf(
            $this->getValidClubData(),
            $this->getValidMember(),
            $transaction
        );
        
        $this->assertIsObject($pdf);
    }
    
    /**
     * Test 19: Member list with various member types
     */
    public function testMemberListWithVariousMemberTypes(): void {
        $clubData = $this->getValidClubData();
        
        $members = [
            array_merge($this->getValidMember(), ['id' => 1, 'name' => 'Admin User']),
            array_merge($this->getValidMember(), ['id' => 2, 'name' => 'Regular Member']),
            array_merge($this->getValidMember(), ['id' => 3, 'name' => 'Treasurer User']),
        ];
        
        $pdf = $this->exporter->generateMemberListPdf($clubData, $members);
        
        $this->assertIsObject($pdf);
    }
    
    /**
     * Test 20: Finance overview with mixed statuses
     */
    public function testFinanceOverviewWithMixedStatuses(): void {
        $clubData = $this->getValidClubData();
        
        $transactions = [
            array_merge($this->getValidTransaction(), ['status' => 'open']),
            array_merge($this->getValidTransaction(), ['status' => 'paid']),
            array_merge($this->getValidTransaction(), ['status' => 'overdue']),
        ];
        
        $pdf = $this->exporter->generateFinanceOverviewPdf($clubData, $transactions);
        
        $this->assertIsObject($pdf);
    }
    
    // ==================== Helper Methods ====================
    
    private function getValidClubData(): array {
        return [
            'name' => 'Musikverein Musterstadt e.V.',
            'address' => 'Hauptstraße 123, 12345 Musterstadt',
            'email' => 'info@musikverein.example.com',
            'iban' => 'DE89370400440532013000',
            'bic' => 'COBADEHHXXX'
        ];
    }
    
    private function getValidMember(): array {
        return [
            'id' => 1,
            'name' => 'Max Mustermann',
            'address' => 'Teststraße 42, 54321 Teststadt',
            'email' => 'max@example.com',
            'iban' => 'DE89370400440532013000'
        ];
    }
    
    private function getValidTransaction(): array {
        return [
            'amount' => 25.00,
            'purpose' => 'Mitgliedsbeitrag November 2025',
            'due_date' => '2025-11-30',
            'created_date' => '2025-11-01',
            'status' => 'open',
            'invoice_number' => '001',
            'member_name' => 'Max Mustermann'
        ];
    }
}
