<?php
namespace OCA\Verein\Tests\Unit;

use PHPUnit\Framework\TestCase;
use OCA\Verein\Service\FeeService;
use OCA\Verein\Db\Fee;
use OCA\Verein\Db\FeeMapper;

/**
 * Create PHPUnit test for FeeService CRUD operations
 */
class FeeServiceTest extends TestCase {
    private $feeMapper;
    private $feeService;

    protected function setUp(): void {
        parent::setUp();
        
        // Create mock for FeeMapper
        $this->feeMapper = $this->createMock(FeeMapper::class);
        $this->feeService = new FeeService($this->feeMapper);
    }

    public function testFindAll() {
        // Arrange
        $fee1 = new Fee();
        $fee1->setId(1);
        $fee1->setMemberId(1);
        $fee1->setAmount(50.00);
        $fee1->setStatus('open');
        
        $fee2 = new Fee();
        $fee2->setId(2);
        $fee2->setMemberId(2);
        $fee2->setAmount(75.00);
        $fee2->setStatus('paid');
        
        $expectedFees = [$fee1, $fee2];
        
        $this->feeMapper->expects($this->once())
            ->method('findAll')
            ->willReturn($expectedFees);
        
        // Act
        $result = $this->feeService->findAll();
        
        // Assert
        $this->assertCount(2, $result);
        $this->assertEquals(50.00, $result[0]->getAmount());
        $this->assertEquals(75.00, $result[1]->getAmount());
    }

    public function testFind() {
        // Arrange
        $fee = new Fee();
        $fee->setId(1);
        $fee->setMemberId(1);
        $fee->setAmount(50.00);
        $fee->setStatus('open');
        $fee->setDueDate('2024-12-31');
        
        $this->feeMapper->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($fee);
        
        // Act
        $result = $this->feeService->find(1);
        
        // Assert
        $this->assertEquals(1, $result->getId());
        $this->assertEquals(50.00, $result->getAmount());
        $this->assertEquals('open', $result->getStatus());
    }

    public function testCreate() {
        // Arrange
        $fee = new Fee();
        $fee->setId(1);
        $fee->setMemberId(1);
        $fee->setAmount(50.00);
        $fee->setStatus('open');
        $fee->setDueDate('2024-12-31');
        
        $this->feeMapper->expects($this->once())
            ->method('insert')
            ->willReturn($fee);
        
        // Act
        $result = $this->feeService->create(
            1,
            50.00,
            'open',
            '2024-12-31'
        );
        
        // Assert
        $this->assertEquals(1, $result->getMemberId());
        $this->assertEquals(50.00, $result->getAmount());
        $this->assertEquals('open', $result->getStatus());
    }

    public function testUpdate() {
        // Arrange
        $fee = new Fee();
        $fee->setId(1);
        $fee->setMemberId(1);
        $fee->setAmount(50.00);
        $fee->setStatus('open');
        $fee->setDueDate('2024-12-31');
        
        $this->feeMapper->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($fee);
        
        $this->feeMapper->expects($this->once())
            ->method('update')
            ->willReturn($fee);
        
        // Act
        $result = $this->feeService->update(
            1,
            1,
            75.00,
            'paid',
            '2024-12-31'
        );
        
        // Assert
        $this->assertEquals(75.00, $result->getAmount());
        $this->assertEquals('paid', $result->getStatus());
    }

    public function testDelete() {
        // Arrange
        $fee = new Fee();
        $fee->setId(1);
        $fee->setMemberId(1);
        
        $this->feeMapper->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($fee);
        
        $this->feeMapper->expects($this->once())
            ->method('delete')
            ->with($fee)
            ->willReturn($fee);
        
        // Act
        $result = $this->feeService->delete(1);
        
        // Assert
        $this->assertEquals(1, $result->getId());
    }

    public function testExportToCsv() {
        // Arrange
        $fee1 = new Fee();
        $fee1->setId(1);
        $fee1->setMemberId(1);
        $fee1->setAmount(50.00);
        $fee1->setStatus('open');
        $fee1->setDueDate('2024-12-31');
        
        $fee2 = new Fee();
        $fee2->setId(2);
        $fee2->setMemberId(2);
        $fee2->setAmount(75.00);
        $fee2->setStatus('paid');
        $fee2->setDueDate('2024-11-30');
        
        $this->feeMapper->expects($this->once())
            ->method('findAll')
            ->willReturn([$fee1, $fee2]);
        
        // Act
        $result = $this->feeService->exportToCsv();
        
        // Assert
        $this->assertStringContainsString('ID,Member ID,Amount,Status,Due Date', $result);
        $this->assertStringContainsString('1,1,50.00,open,2024-12-31', $result);
        $this->assertStringContainsString('2,2,75.00,paid,2024-11-30', $result);
    }

    public function testFindThrowsExceptionWhenNotFound() {
        // Arrange
        $this->feeMapper->expects($this->once())
            ->method('find')
            ->with(999)
            ->willThrowException(new \Exception('Not found'));
        
        // Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Fee not found');
        
        // Act
        $this->feeService->find(999);
    }

    /**
     * Test for overdue fees functionality
     */
    public function testFindOverdueFees() {
        // Arrange - Create fees with different due dates
        $overdueFee = new Fee();
        $overdueFee->setId(1);
        $overdueFee->setMemberId(1);
        $overdueFee->setAmount(50.00);
        $overdueFee->setStatus('open');
        $overdueFee->setDueDate(date('Y-m-d', strtotime('-10 days')));
        
        $currentFee = new Fee();
        $currentFee->setId(2);
        $currentFee->setMemberId(2);
        $currentFee->setAmount(75.00);
        $currentFee->setStatus('open');
        $currentFee->setDueDate(date('Y-m-d', strtotime('+10 days')));
        
        $this->feeMapper->expects($this->once())
            ->method('findAll')
            ->willReturn([$overdueFee, $currentFee]);
        
        // Act
        $allFees = $this->feeService->findAll();
        
        // Assert - Check that we can identify overdue fees
        $today = new Date();
        $overdueCount = 0;
        foreach ($allFees as $fee) {
            if ($fee->getStatus() === 'open' && strtotime($fee->getDueDate()) < strtotime('today')) {
                $overdueCount++;
            }
        }
        
        $this->assertEquals(1, $overdueCount);
    }

    public function testExportToCsvIncludesAllFields() {
        // Arrange
        $fee = new Fee();
        $fee->setId(1);
        $fee->setMemberId(1);
        $fee->setAmount(50.00);
        $fee->setStatus('open');
        $fee->setDueDate('2024-12-31');
        
        $this->feeMapper->expects($this->once())
            ->method('findAll')
            ->willReturn([$fee]);
        
        // Act
        $csv = $this->feeService->exportToCsv();
        
        // Assert - Check CSV format and content
        $lines = explode("\n", trim($csv));
        $this->assertCount(2, $lines); // Header + 1 data row
        $this->assertEquals('ID,Member ID,Amount,Status,Due Date', $lines[0]);
        $this->assertStringContainsString('1,1,50.00,open,2024-12-31', $lines[1]);
    }
}
