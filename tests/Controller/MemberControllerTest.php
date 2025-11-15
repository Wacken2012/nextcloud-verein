<?php
declare(strict_types=1);

namespace OCA\Verein\Tests\Controller;

use OCA\Verein\Controller\MemberController;
use OCA\Verein\Service\MemberService;
use OCA\Verein\Db\Member;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class MemberControllerTest extends TestCase {
    private MemberController $controller;
    private MemberService|MockObject $memberService;
    private string $userId = 'testuser';

    protected function setUp(): void {
        parent::setUp();

        // Mock des MemberService
        $this->memberService = $this->createMock(MemberService::class);

        // Controller mit Mock-Service instantiieren
        $this->controller = new MemberController(
            'verein',
            $this->memberService,
            $this->userId
        );
    }

    protected function tearDown(): void {
        parent::tearDown();
        $this->memberService = null;
        $this->controller = null;
    }

    /**
     * Test: GET /members liefert alle Mitglieder als JSON
     */
    public function testGetMembersReturnsAllMembers(): void {
        // Arrange: Mock-Mitglieder erstellen
        $mockMembers = [
            $this->createMockMember(1, 'Max Mustermann', 'max@example.com'),
            $this->createMockMember(2, 'Erika Musterfrau', 'erika@example.com'),
            $this->createMockMember(3, 'Test Member', 'test@example.com'),
        ];

        // Service so konfigurieren, dass er Mock-Daten zurückgibt
        $this->memberService->expects($this->once())
            ->method('findAll')
            ->willReturn($mockMembers);

        // Act: GET /members aufrufen
        $response = $this->controller->index();

        // Assert: Rückgabe überprüfen
        $this->assertIsArray($response);
        $this->assertCount(3, $response);
        $this->assertEquals(1, $response[0]['id']);
        $this->assertEquals('Max Mustermann', $response[0]['name']);
        $this->assertEquals('max@example.com', $response[0]['email']);
    }

    /**
     * Test: GET /members liefert leeres Array wenn keine Mitglieder
     */
    public function testGetMembersReturnsEmptyArrayWhenNoMembers(): void {
        // Service so konfigurieren, dass er leeres Array zurückgibt
        $this->memberService->expects($this->once())
            ->method('findAll')
            ->willReturn([]);

        // Act: GET /members aufrufen
        $response = $this->controller->index();

        // Assert: Rückgabe überprüfen
        $this->assertIsArray($response);
        $this->assertEmpty($response);
        $this->assertCount(0, $response);
    }

    /**
     * Test: GET /members/{id} liefert einzelnen Mitglied
     */
    public function testGetMemberByIdReturnsSingleMember(): void {
        // Arrange: Mock-Mitglied erstellen
        $mockMember = $this->createMockMember(42, 'John Doe', 'john@example.com');

        // Service so konfigurieren
        $this->memberService->expects($this->once())
            ->method('find')
            ->with($this->equalTo(42))
            ->willReturn($mockMember);

        // Act: GET /members/42 aufrufen
        $response = $this->controller->show(42);

        // Assert: Rückgabe überprüfen
        $this->assertIsArray($response);
        $this->assertEquals(42, $response['id']);
        $this->assertEquals('John Doe', $response['name']);
        $this->assertEquals('john@example.com', $response['email']);
    }

    /**
     * Test: POST /members mit gültigen Daten erstellt neues Mitglied
     */
    public function testCreateMemberWithValidDataReturnsNewMember(): void {
        // Arrange: Gültige Eingabedaten
        $name = 'New Member';
        $email = 'new@example.com';
        $address = 'Test Street 1';
        $iban = 'DE89370400440532013000';
        $bic = 'COBADEFFXXX';
        $role = 'Mitglied';

        // Mock: Neues Mitglied wird erstellt
        $newMember = $this->createMockMember(99, $name, $email, $address, $iban, $bic, $role);

        $this->memberService->expects($this->once())
            ->method('create')
            ->with(
                $this->equalTo($name),
                $this->equalTo($email),
                $this->equalTo($address),
                $this->equalTo($iban),
                $this->equalTo($bic),
                $this->equalTo($role)
            )
            ->willReturn($newMember);

        // Act: POST /members aufrufen
        $response = $this->controller->create($name, $email, $address, $iban, $bic, $role);

        // Assert: Rückgabe überprüfen
        $this->assertIsArray($response);
        $this->assertEquals(99, $response['id']);
        $this->assertEquals($name, $response['name']);
        $this->assertEquals($email, $response['email']);
    }

    /**
     * Test: PUT /members/{id} aktualisiert Mitglied
     */
    public function testUpdateMemberWithValidDataReturnsUpdatedMember(): void {
        // Arrange: Aktualisierungsdaten
        $id = 5;
        $name = 'Updated Name';
        $email = 'updated@example.com';

        $updatedMember = $this->createMockMember($id, $name, $email);

        $this->memberService->expects($this->once())
            ->method('update')
            ->with($this->equalTo($id))
            ->willReturn($updatedMember);

        // Act: PUT /members/5 aufrufen
        $response = $this->controller->update($id, $name, $email);

        // Assert: Rückgabe überprüfen
        $this->assertEquals($name, $response['name']);
        $this->assertEquals($email, $response['email']);
    }

    /**
     * Test: DELETE /members/{id} löscht Mitglied
     */
    public function testDeleteMemberRemovesMemberFromDatabase(): void {
        // Arrange
        $id = 10;

        $this->memberService->expects($this->once())
            ->method('delete')
            ->with($this->equalTo($id));

        // Act: DELETE /members/10 aufrufen
        $response = $this->controller->destroy($id);

        // Assert: Response überprüfen (Erfolg zurück)
        $this->assertTrue($response);
    }

    /**
     * Hilfsmethod: Erstellt ein Mock-Mitglied-Array
     */
    private function createMockMember(
        int $id,
        string $name,
        string $email,
        string $address = 'Test Address',
        string $iban = 'DE89370400440532013000',
        string $bic = 'COBADEFF',
        string $role = 'Mitglied'
    ): array {
        return [
            'id' => $id,
            'name' => $name,
            'email' => $email,
            'address' => $address,
            'iban' => $iban,
            'bic' => $bic,
            'role' => $role,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
    }
}
