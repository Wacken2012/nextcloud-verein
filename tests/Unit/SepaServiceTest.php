<?php
namespace OCA\Verein\Tests\Unit;

use PHPUnit\Framework\TestCase;
use OCA\Verein\Service\SepaService;
use OCA\Verein\Db\Fee;
use OCA\Verein\Db\FeeMapper;
use OCA\Verein\Db\Member;
use OCA\Verein\Db\MemberMapper;

/**
 * Create PHPUnit test for SepaService XML validation
 */
class SepaServiceTest extends TestCase {
    private $feeMapper;
    private $memberMapper;
    private $sepaService;

    protected function setUp(): void {
        parent::setUp();
        
        $this->feeMapper = $this->createMock(FeeMapper::class);
        $this->memberMapper = $this->createMock(MemberMapper::class);
        $this->sepaService = new SepaService($this->feeMapper, $this->memberMapper);
    }

    public function testGenerateSepaXmlWithOpenFees() {
        // Arrange
        $member = new Member();
        $member->setId(1);
        $member->setName('Max Mustermann');
        $member->setIban('DE89370400440532013000');
        $member->setBic('COBADEFFXXX');
        
        $fee = new Fee();
        $fee->setId(1);
        $fee->setMemberId(1);
        $fee->setAmount(50.00);
        $fee->setStatus('open');
        $fee->setDueDate('2024-12-31');
        
        $this->feeMapper->expects($this->once())
            ->method('findByStatus')
            ->with('open')
            ->willReturn([$fee]);
        
        $this->memberMapper->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($member);
        
        // Act
        $xml = $this->sepaService->generateSepaXml(
            'Test Verein',
            'DE89370400440532013000',
            'COBADEFFXXX',
            'DE98ZZZ09999999999'
        );
        
        // Assert
        $this->assertIsString($xml);
        $this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $xml);
        $this->assertStringContainsString('<Document xmlns="urn:iso:std:iso:20022:tech:xsd:pain.008.001.02"', $xml);
        $this->assertStringContainsString('<Cdtr><Nm>Test Verein</Nm></Cdtr>', $xml);
        $this->assertStringContainsString('<IBAN>DE89370400440532013000</IBAN>', $xml);
        $this->assertStringContainsString('<Dbtr><Nm>Max Mustermann</Nm></Dbtr>', $xml);
        $this->assertStringContainsString('<InstdAmt Ccy="EUR">50.00</InstdAmt>', $xml);
    }

    public function testGenerateSepaXmlThrowsExceptionWhenNoOpenFees() {
        // Arrange
        $this->feeMapper->expects($this->once())
            ->method('findByStatus')
            ->with('open')
            ->willReturn([]);
        
        // Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No open fees found for SEPA export');
        
        // Act
        $this->sepaService->generateSepaXml(
            'Test Verein',
            'DE89370400440532013000',
            'COBADEFFXXX',
            'DE98ZZZ09999999999'
        );
    }

    public function testGenerateSepaXmlSkipsMembersWithoutIban() {
        // Arrange
        $member1 = new Member();
        $member1->setId(1);
        $member1->setName('Member Without IBAN');
        $member1->setIban(''); // No IBAN
        
        $member2 = new Member();
        $member2->setId(2);
        $member2->setName('Member With IBAN');
        $member2->setIban('DE89370400440532013000');
        $member2->setBic('COBADEFFXXX');
        
        $fee1 = new Fee();
        $fee1->setId(1);
        $fee1->setMemberId(1);
        $fee1->setAmount(50.00);
        $fee1->setStatus('open');
        
        $fee2 = new Fee();
        $fee2->setId(2);
        $fee2->setMemberId(2);
        $fee2->setAmount(75.00);
        $fee2->setStatus('open');
        
        $this->feeMapper->expects($this->once())
            ->method('findByStatus')
            ->with('open')
            ->willReturn([$fee1, $fee2]);
        
        $this->memberMapper->expects($this->exactly(2))
            ->method('find')
            ->willReturnCallback(function($id) use ($member1, $member2) {
                return $id === 1 ? $member1 : $member2;
            });
        
        // Act
        $xml = $this->sepaService->generateSepaXml(
            'Test Verein',
            'DE89370400440532013000',
            'COBADEFFXXX',
            'DE98ZZZ09999999999'
        );
        
        // Assert - Should only contain member 2
        $this->assertStringContainsString('Member With IBAN', $xml);
        $this->assertStringNotContainsString('Member Without IBAN', $xml);
        $this->assertStringContainsString('<NbOfTxs>1</NbOfTxs>', $xml);
        $this->assertStringContainsString('<InstdAmt Ccy="EUR">75.00</InstdAmt>', $xml);
    }

    public function testPreviewSepaExport() {
        // Arrange
        $member = new Member();
        $member->setId(1);
        $member->setName('Max Mustermann');
        $member->setIban('DE89370400440532013000');
        
        $fee = new Fee();
        $fee->setId(1);
        $fee->setMemberId(1);
        $fee->setAmount(50.00);
        $fee->setDueDate('2024-12-31');
        
        $this->feeMapper->expects($this->once())
            ->method('findByStatus')
            ->with('open')
            ->willReturn([$fee]);
        
        $this->memberMapper->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($member);
        
        // Act
        $preview = $this->sepaService->previewSepaExport(
            'Test Verein',
            'DE89370400440532013000',
            'COBADEFFXXX',
            'DE98ZZZ09999999999'
        );
        
        // Assert
        $this->assertIsArray($preview);
        $this->assertEquals('Test Verein', $preview['creditorName']);
        $this->assertEquals('DE89370400440532013000', $preview['creditorIban']);
        $this->assertEquals(50.00, $preview['totalAmount']);
        $this->assertEquals(1, $preview['transactionCount']);
        $this->assertCount(1, $preview['transactions']);
        $this->assertEquals('Max Mustermann', $preview['transactions'][0]['memberName']);
    }

    public function testSepaXmlContainsValidMessageId() {
        // Arrange
        $member = new Member();
        $member->setId(1);
        $member->setName('Test Member');
        $member->setIban('DE89370400440532013000');
        $member->setBic('COBADEFFXXX');
        
        $fee = new Fee();
        $fee->setId(1);
        $fee->setMemberId(1);
        $fee->setAmount(50.00);
        
        $this->feeMapper->method('findByStatus')->willReturn([$fee]);
        $this->memberMapper->method('find')->willReturn($member);
        
        // Act
        $xml = $this->sepaService->generateSepaXml(
            'Test Verein',
            'DE89370400440532013000',
            'COBADEFFXXX',
            'DE98ZZZ09999999999'
        );
        
        // Assert - Message ID should start with VEREIN-
        $this->assertMatchesRegularExpression('/<MsgId>VEREIN-\d{14}<\/MsgId>/', $xml);
    }

    public function testSepaXmlContainsCorrectTotalAmount() {
        // Arrange
        $member1 = new Member();
        $member1->setId(1);
        $member1->setName('Member 1');
        $member1->setIban('DE89370400440532013000');
        $member1->setBic('COBADEFFXXX');
        
        $member2 = new Member();
        $member2->setId(2);
        $member2->setName('Member 2');
        $member2->setIban('DE89370400440532013001');
        $member2->setBic('COBADEFFXXX');
        
        $fee1 = new Fee();
        $fee1->setId(1);
        $fee1->setMemberId(1);
        $fee1->setAmount(50.00);
        
        $fee2 = new Fee();
        $fee2->setId(2);
        $fee2->setMemberId(2);
        $fee2->setAmount(75.50);
        
        $this->feeMapper->method('findByStatus')->willReturn([$fee1, $fee2]);
        $this->memberMapper->method('find')->willReturnCallback(function($id) use ($member1, $member2) {
            return $id === 1 ? $member1 : $member2;
        });
        
        // Act
        $xml = $this->sepaService->generateSepaXml(
            'Test Verein',
            'DE89370400440532013000',
            'COBADEFFXXX',
            'DE98ZZZ09999999999'
        );
        
        // Assert - Total should be 125.50
        $this->assertStringContainsString('<CtrlSum>125.50</CtrlSum>', $xml);
        $this->assertStringContainsString('<NbOfTxs>2</NbOfTxs>', $xml);
    }
}
