<?php
/**
 * SepaXmlExporterTest.php - Unit Tests für SEPA XML Export
 * 
 * v0.2.0 Tests: SEPA pain.001 XML Export
 * Target: 30+ Tests (ISO 20022 Compliance)
 * 
 * @category Test
 * @package Verein\Tests\Unit\Service\Export\SEPA
 * @license AGPL-3.0
 */

namespace OCA\Verein\Tests\Unit\Service\Export\SEPA;

use OCA\Verein\Service\Export\SEPA\SepaXmlExporter;
use OCA\Verein\Exception\ValidationException;
use OCA\Verein\Exception\ExportException;
use PHPUnit\Framework\TestCase;

class SepaXmlExporterTest extends TestCase {
    
    private SepaXmlExporter $exporter;
    
    protected function setUp(): void {
        $this->exporter = new SepaXmlExporter();
    }
    
    /**
     * Test 1: Basic SEPA XML generation
     */
    public function testBasicSepaXmlGeneration(): void {
        $clubData = [
            'name' => 'Musikverein Musterstadt',
            'country' => 'DE',
            'identifier' => 'DE-MV-2025-001'
        ];
        
        $transactions = [
            [
                'member_id' => 1,
                'member_name' => 'Max Mustermann',
                'member_iban' => 'DE89370400440532013000',
                'member_bic' => 'COBADEHHXXX',
                'amount' => 25.00,
                'purpose' => 'Mitgliedsbeitrag November 2025',
                'due_date' => '2025-11-30'
            ]
        ];
        
        $xml = $this->exporter->generateCreditTransferXml(
            $clubData,
            $transactions,
            'DE89370400440532013000',
            'COBADEHHXXX'
        );
        
        $this->assertIsString($xml);
        $this->assertStringContainsString('<?xml', $xml);
        $this->assertStringContainsString('CstmCdtTrfInitn', $xml);
    }
    
    /**
     * Test 2: XML contains correct namespace
     */
    public function testXmlNamespaceCorrect(): void {
        $xml = $this->getBasicXml();
        
        $this->assertStringContainsString('urn:iso:std:iso:20022:tech:xsd:pain.001.003.02', $xml);
    }
    
    /**
     * Test 3: Group header contains message ID
     */
    public function testGroupHeaderHasMessageId(): void {
        $xml = $this->getBasicXml();
        
        $this->assertStringContainsString('MsgId', $xml);
        $this->assertStringContainsString('MSG-', $xml);
    }
    
    /**
     * Test 4: Group header contains creation time
     */
    public function testGroupHeaderHasCreationTime(): void {
        $xml = $this->getBasicXml();
        
        $this->assertStringContainsString('CreDtTm', $xml);
        $this->assertStringContainsString('T', $xml); // ISO 8601 format
    }
    
    /**
     * Test 5: Transaction count in header correct
     */
    public function testTransactionCountCorrect(): void {
        $clubData = ['name' => 'Test Club', 'identifier' => 'TEST-001'];
        
        $transactions = [
            ['member_name' => 'Person 1', 'member_iban' => 'DE89370400440532013000', 
             'member_bic' => 'COBADEHHXXX', 'amount' => 10.00, 'purpose' => 'Test 1', 'due_date' => '2025-12-31'],
            ['member_name' => 'Person 2', 'member_iban' => 'DE89370400440532013001', 
             'member_bic' => 'COBADEHHXXX', 'amount' => 20.00, 'purpose' => 'Test 2', 'due_date' => '2025-12-31'],
            ['member_name' => 'Person 3', 'member_iban' => 'DE89370400440532013002', 
             'member_bic' => 'COBADEHHXXX', 'amount' => 30.00, 'purpose' => 'Test 3', 'due_date' => '2025-12-31'],
        ];
        
        $xml = $this->exporter->generateCreditTransferXml(
            $clubData,
            $transactions,
            'DE89370400440532013000',
            'COBADEHHXXX'
        );
        
        $this->assertStringContainsString('NbOfTxns', $xml);
        $this->assertStringContainsString('>3<', $xml); // Count = 3
    }
    
    /**
     * Test 6: Total amount calculated correctly
     */
    public function testTotalAmountCorrect(): void {
        $clubData = ['name' => 'Test Club', 'identifier' => 'TEST-001'];
        
        $transactions = [
            ['member_name' => 'P1', 'member_iban' => 'DE89370400440532013000', 
             'member_bic' => 'COBADEHHXXX', 'amount' => 100.00, 'purpose' => 'Test', 'due_date' => '2025-12-31'],
            ['member_name' => 'P2', 'member_iban' => 'DE89370400440532013001', 
             'member_bic' => 'COBADEHHXXX', 'amount' => 200.00, 'purpose' => 'Test', 'due_date' => '2025-12-31'],
        ];
        
        $xml = $this->exporter->generateCreditTransferXml(
            $clubData,
            $transactions,
            'DE89370400440532013000',
            'COBADEHHXXX'
        );
        
        $this->assertStringContainsString('CtrlSum', $xml);
        $this->assertStringContainsString('300.00', $xml); // 100 + 200
    }
    
    /**
     * Test 7: Invalid IBAN throws ValidationException
     */
    public function testInvalidIbanThrowsException(): void {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Ungültige IBAN');
        
        $this->exporter->generateCreditTransferXml(
            ['name' => 'Test', 'identifier' => 'TEST'],
            [['member_name' => 'Test', 'member_iban' => 'INVALID', 
              'member_bic' => 'COBADEHHXXX', 'amount' => 10.00, 'purpose' => 'Test', 'due_date' => '2025-12-31']],
            'INVALID_IBAN',
            'COBADEHHXXX'
        );
    }
    
    /**
     * Test 8: Invalid BIC throws ValidationException
     */
    public function testInvalidBicThrowsException(): void {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Ungültiger BIC');
        
        $this->exporter->generateCreditTransferXml(
            ['name' => 'Test', 'identifier' => 'TEST'],
            [['member_name' => 'Test', 'member_iban' => 'DE89370400440532013000', 
              'member_bic' => 'INVALID', 'amount' => 10.00, 'purpose' => 'Test', 'due_date' => '2025-12-31']],
            'DE89370400440532013000',
            'INVALID'
        );
    }
    
    /**
     * Test 9: Zero amount throws ValidationException
     */
    public function testZeroAmountThrowsException(): void {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Betrag muss > 0 sein');
        
        $this->exporter->generateCreditTransferXml(
            ['name' => 'Test', 'identifier' => 'TEST'],
            [['member_name' => 'Test', 'member_iban' => 'DE89370400440532013000', 
              'member_bic' => 'COBADEHHXXX', 'amount' => 0, 'purpose' => 'Test', 'due_date' => '2025-12-31']],
            'DE89370400440532013000',
            'COBADEHHXXX'
        );
    }
    
    /**
     * Test 10: Empty transactions throws ValidationException
     */
    public function testEmptyTransactionsThrowsException(): void {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Mindestens eine Transaktion erforderlich');
        
        $this->exporter->generateCreditTransferXml(
            ['name' => 'Test', 'identifier' => 'TEST'],
            [],
            'DE89370400440532013000',
            'COBADEHHXXX'
        );
    }
    
    /**
     * Test 11: IBAN format validation (length)
     */
    public function testIbanFormatValidationLength(): void {
        $this->expectException(ValidationException::class);
        
        // Too short IBAN
        $this->exporter->generateCreditTransferXml(
            ['name' => 'Test', 'identifier' => 'TEST'],
            [['member_name' => 'Test', 'member_iban' => 'DE893704', 
              'member_bic' => 'COBADEHHXXX', 'amount' => 10.00, 'purpose' => 'Test', 'due_date' => '2025-12-31']],
            'DE89370400440532013000',
            'COBADEHHXXX'
        );
    }
    
    /**
     * Test 12: BIC format validation (8 or 11 chars)
     */
    public function testBicFormatValidation(): void {
        // Valid 8-char BIC
        $xml = $this->exporter->generateCreditTransferXml(
            ['name' => 'Test', 'identifier' => 'TEST'],
            [['member_name' => 'Test', 'member_iban' => 'DE89370400440532013000', 
              'member_bic' => 'COBADEHH', 'amount' => 10.00, 'purpose' => 'Test', 'due_date' => '2025-12-31']],
            'DE89370400440532013000',
            'COBADEHH'
        );
        $this->assertIsString($xml);
        
        // Valid 11-char BIC
        $xml = $this->exporter->generateCreditTransferXml(
            ['name' => 'Test', 'identifier' => 'TEST'],
            [['member_name' => 'Test', 'member_iban' => 'DE89370400440532013000', 
              'member_bic' => 'COBADEHHXXX', 'amount' => 10.00, 'purpose' => 'Test', 'due_date' => '2025-12-31']],
            'DE89370400440532013000',
            'COBADEHHXXX'
        );
        $this->assertIsString($xml);
    }
    
    /**
     * Test 13: Amount greater than max throws ValidationException
     */
    public function testAmountTooHighThrowsException(): void {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Betrag zu hoch');
        
        $this->exporter->generateCreditTransferXml(
            ['name' => 'Test', 'identifier' => 'TEST'],
            [['member_name' => 'Test', 'member_iban' => 'DE89370400440532013000', 
              'member_bic' => 'COBADEHHXXX', 'amount' => 1000000.00, 'purpose' => 'Test', 'due_date' => '2025-12-31']],
            'DE89370400440532013000',
            'COBADEHHXXX'
        );
    }
    
    /**
     * Test 14: Club name required
     */
    public function testClubNameRequired(): void {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Vereinsname erforderlich');
        
        $this->exporter->generateCreditTransferXml(
            ['identifier' => 'TEST'], // Missing name
            [['member_name' => 'Test', 'member_iban' => 'DE89370400440532013000', 
              'member_bic' => 'COBADEHHXXX', 'amount' => 10.00, 'purpose' => 'Test', 'due_date' => '2025-12-31']],
            'DE89370400440532013000',
            'COBADEHHXXX'
        );
    }
    
    /**
     * Helper: Generate basic valid XML
     */
    private function getBasicXml(): string {
        $clubData = [
            'name' => 'Musikverein Test',
            'country' => 'DE',
            'identifier' => 'TEST-001'
        ];
        
        $transactions = [
            [
                'member_id' => 1,
                'member_name' => 'Test Person',
                'member_iban' => 'DE89370400440532013000',
                'member_bic' => 'COBADEHHXXX',
                'amount' => 50.00,
                'purpose' => 'Test Payment',
                'due_date' => '2025-12-31'
            ]
        ];
        
        return $this->exporter->generateCreditTransferXml(
            $clubData,
            $transactions,
            'DE89370400440532013000',
            'COBADEHHXXX'
        );
    }
}
