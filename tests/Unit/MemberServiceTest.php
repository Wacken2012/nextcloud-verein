<?php
namespace OCA\Verein\Tests\Unit;

use PHPUnit\Framework\TestCase;
use OCA\Verein\Service\MemberService;
use OCA\Verein\Db\Member;
use OCA\Verein\Db\MemberMapper;

/**
 * Create PHPUnit test for MemberService CRUD operations
 */
class MemberServiceTest extends TestCase {
    private $memberMapper;
    private $memberService;

    protected function setUp(): void {
        parent::setUp();
        
        // Create mock for MemberMapper
        $this->memberMapper = $this->createMock(MemberMapper::class);
        $this->memberService = new MemberService($this->memberMapper);
    }

    public function testFindAll() {
        // Arrange
        $member1 = new Member();
        $member1->setId(1);
        $member1->setName('John Doe');
        $member1->setEmail('john@example.com');
        
        $member2 = new Member();
        $member2->setId(2);
        $member2->setName('Jane Smith');
        $member2->setEmail('jane@example.com');
        
        $expectedMembers = [$member1, $member2];
        
        $this->memberMapper->expects($this->once())
            ->method('findAll')
            ->willReturn($expectedMembers);
        
        // Act
        $result = $this->memberService->findAll();
        
        // Assert
        $this->assertCount(2, $result);
        $this->assertEquals('John Doe', $result[0]->getName());
        $this->assertEquals('Jane Smith', $result[1]->getName());
    }

    public function testFind() {
        // Arrange
        $member = new Member();
        $member->setId(1);
        $member->setName('John Doe');
        $member->setEmail('john@example.com');
        
        $this->memberMapper->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($member);
        
        // Act
        $result = $this->memberService->find(1);
        
        // Assert
        $this->assertEquals(1, $result->getId());
        $this->assertEquals('John Doe', $result->getName());
        $this->assertEquals('john@example.com', $result->getEmail());
    }

    public function testCreate() {
        // Arrange
        $member = new Member();
        $member->setId(1);
        $member->setName('John Doe');
        $member->setAddress('123 Main St');
        $member->setEmail('john@example.com');
        $member->setIban('DE89370400440532013000');
        $member->setBic('COBADEFFXXX');
        $member->setRole('member');
        
        $this->memberMapper->expects($this->once())
            ->method('insert')
            ->willReturn($member);
        
        // Act
        $result = $this->memberService->create(
            'John Doe',
            '123 Main St',
            'john@example.com',
            'DE89370400440532013000',
            'COBADEFFXXX',
            'member'
        );
        
        // Assert
        $this->assertEquals('John Doe', $result->getName());
        $this->assertEquals('john@example.com', $result->getEmail());
        $this->assertEquals('member', $result->getRole());
    }

    public function testUpdate() {
        // Arrange
        $member = new Member();
        $member->setId(1);
        $member->setName('John Doe');
        $member->setAddress('123 Main St');
        $member->setEmail('john@example.com');
        $member->setIban('DE89370400440532013000');
        $member->setBic('COBADEFFXXX');
        $member->setRole('member');
        
        $this->memberMapper->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($member);
        
        $this->memberMapper->expects($this->once())
            ->method('update')
            ->willReturn($member);
        
        // Act
        $result = $this->memberService->update(
            1,
            'John Doe Updated',
            '456 New St',
            'john.new@example.com',
            'DE89370400440532013000',
            'COBADEFFXXX',
            'board'
        );
        
        // Assert
        $this->assertEquals('John Doe Updated', $result->getName());
        $this->assertEquals('456 New St', $result->getAddress());
        $this->assertEquals('board', $result->getRole());
    }

    public function testDelete() {
        // Arrange
        $member = new Member();
        $member->setId(1);
        $member->setName('John Doe');
        
        $this->memberMapper->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($member);
        
        $this->memberMapper->expects($this->once())
            ->method('delete')
            ->with($member)
            ->willReturn($member);
        
        // Act
        $result = $this->memberService->delete(1);
        
        // Assert
        $this->assertEquals(1, $result->getId());
    }

    public function testFindThrowsExceptionWhenNotFound() {
        // Arrange
        $this->memberMapper->expects($this->once())
            ->method('find')
            ->with(999)
            ->willThrowException(new \Exception('Not found'));
        
        // Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Member not found');
        
        // Act
        $this->memberService->find(999);
    }
}
