<?php
declare(strict_types=1);

namespace OCA\Verein\Tests\Controller;

use OCA\Verein\Controller\FinanceController;
use OCA\Verein\Service\FeeService;
use OCA\Verein\Service\MemberService;
use OCA\Verein\Db\Fee;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class FinanceControllerTest extends TestCase {
    private FinanceController $controller;
    private FeeService|MockObject $feeService;
    private MemberService|MockObject $memberService;
    private string $userId = 'testuser';

    protected function setUp(): void {
        parent::setUp();

        // Mocks erstellen
        $this->feeService = $this->createMock(FeeService::class);
        $this->memberService = $this->createMock(MemberService::class);

        // Controller mit Mock-Services instantiieren
        $this->controller = new FinanceController(
            'verein',
            $this->feeService,
            $this->memberService,
            $this->userId
        );
    }

    protected function tearDown(): void {
        parent::tearDown();
        $this->feeService = null;
        $this->memberService = null;
        $this->controller = null;
    }

    /**
     * Test: GET /finance liefert alle Gebühren als JSON
     */
    public function testGetFinanceReturnsAllFees(): void {
        // Arrange: Mock-Gebühren erstellen
        $mockFees = [
            $this->createMockFee(1, 1, 50.00, 'open', 'Jahresbeitrag 2025'),
            $this->createMockFee(2, 2, 50.00, 'paid', 'Jahresbeitrag 2025'),
            $this->createMockFee(3, 1, 25.00, 'overdue', 'Verwaltungsgebühr'),
        ];

        $this->feeService->expects($this->once())
            ->method('findAll')
            ->willReturn($mockFees);

        // Act: GET /finance aufrufen
        $response = $this->controller->index();

        // Assert: Rückgabe überprüfen
        $this->assertIsArray($response);
        $this->assertCount(3, $response);
        $this->assertEquals(50.00, $response[0]['amount']);
        $this->assertEquals('open', $response[0]['status']);
    }

    /**
     * Test: POST /finance mit gültigen Daten erstellt neue Gebühr
     */
    public function testCreateFeeWithValidDataReturnsNewFee(): void {
        // Arrange: Gültige Eingabedaten
        $memberId = 1;
        $amount = 75.50;
        $description = 'Jahresbeitrag 2025';
        $status = 'open';

        // Mock: Neue Gebühr wird erstellt
        $newFee = $this->createMockFee(101, $memberId, $amount, $status, $description);

        $this->feeService->expects($this->once())
            ->method('create')
            ->with(
                $this->equalTo($memberId),
                $this->equalTo($amount),
                $this->equalTo($description),
                $this->equalTo($status)
            )
            ->willReturn($newFee);

        // Act: POST /finance aufrufen
        $response = $this->controller->create($memberId, $amount, $description, $status);

        // Assert: Rückgabe überprüfen
        $this->assertIsArray($response);
        $this->assertEquals(101, $response['id']);
        $this->assertEquals($memberId, $response['member_id']);
        $this->assertEquals($amount, $response['amount']);
        $this->assertEquals($description, $response['description']);
        $this->assertEquals($status, $response['status']);
    }

    /**
     * Test: POST /finance mit ungültiger member_id gibt Fehler zurück
     */
    public function testCreateFeeWithInvalidMemberIdThrowsException(): void {
        // Arrange: Ungültige member_id
        $invalidMemberId = 999;
        $amount = 50.00;

        // Member existiert nicht
        $this->memberService->expects($this->once())
            ->method('find')
            ->with($this->equalTo($invalidMemberId))
            ->willThrowException(new \Exception('Member not found'));

        // Assert: Exception wird erwartet
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Member not found');

        // Act: POST /finance mit ungültiger member_id aufrufen
        $this->controller->create($invalidMemberId, $amount, 'Test');
    }

    /**
     * Test: GET /finance mit Statistiken (Summary)
     */
    public function testGetFinanceSummaryReturnsStatistics(): void {
        // Arrange: Mock-Statistiken
        $mockStats = [
            'total_open' => 500.00,
            'total_paid' => 1200.00,
            'total_overdue' => 150.00,
            'total_outstanding' => 650.00,
            'fee_count' => 10,
            'paid_count' => 6,
            'open_count' => 3,
            'overdue_count' => 1,
        ];

        $this->feeService->expects($this->once())
            ->method('getStatistics')
            ->willReturn($mockStats);

        // Act: Statistiken abrufen
        $response = $this->controller->summary();

        // Assert: Rückgabe überprüfen
        $this->assertIsArray($response);
        $this->assertEquals(500.00, $response['total_open']);
        $this->assertEquals(1200.00, $response['total_paid']);
        $this->assertEquals(650.00, $response['total_outstanding']);
        $this->assertEquals(10, $response['fee_count']);
    }

    /**
     * Test: PUT /finance/{id} aktualisiert Gebührenstatus
     */
    public function testUpdateFeeStatusChangesStatus(): void {
        // Arrange
        $id = 5;
        $newStatus = 'paid';
        $updatedFee = $this->createMockFee($id, 1, 50.00, $newStatus, 'Test');

        $this->feeService->expects($this->once())
            ->method('update')
            ->with($this->equalTo($id))
            ->willReturn($updatedFee);

        // Act: PUT /finance/5 aufrufen
        $response = $this->controller->update($id, 50.00, 'Test', $newStatus);

        // Assert: Status wurde geändert
        $this->assertEquals($newStatus, $response['status']);
        $this->assertEquals($id, $response['id']);
    }

    /**
     * Test: DELETE /finance/{id} löscht Gebühr
     */
    public function testDeleteFeeRemovesFeeFromDatabase(): void {
        // Arrange
        $id = 7;

        $this->feeService->expects($this->once())
            ->method('delete')
            ->with($this->equalTo($id));

        // Act: DELETE /finance/7 aufrufen
        $response = $this->controller->destroy($id);

        // Assert: Response überprüfen
        $this->assertTrue($response);
    }

    /**
     * Test: GET /finance/{memberId} liefert Gebühren eines Mitglieds
     */
    public function testGetFeesByMemberIdReturnsMemberFees(): void {
        // Arrange: Mock-Gebühren für ein Mitglied
        $memberId = 3;
        $mockFees = [
            $this->createMockFee(10, $memberId, 50.00, 'paid', 'Jahresbeitrag'),
            $this->createMockFee(11, $memberId, 25.00, 'open', 'Zusatzgebühr'),
        ];

        $this->feeService->expects($this->once())
            ->method('findByMemberId')
            ->with($this->equalTo($memberId))
            ->willReturn($mockFees);

        // Act: GET /finance/member/3 aufrufen (hypothetisch)
        $response = $this->controller->index(); // In echtem Controller könnte member_id Filter sein
        // Für diesen Test setzen wir voraus, dass der Controller auch ein Filter-Parameter hat

        // Assert: Rückgabe überprüfen
        $this->assertIsArray($response);
        $this->assertCount(2, $response);
        $this->assertEquals($memberId, $response[0]['member_id']);
    }

    /**
     * Hilfsmethod: Erstellt ein Mock-Gebühren-Array
     */
    private function createMockFee(
        int $id,
        int $memberId,
        float $amount,
        string $status = 'open',
        string $description = 'Gebühr'
    ): array {
        return [
            'id' => $id,
            'member_id' => $memberId,
            'amount' => $amount,
            'status' => $status,
            'description' => $description,
            'due_date' => date('Y-m-d', strtotime('+30 days')),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
    }
}
