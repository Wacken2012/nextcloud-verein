<?php
/**
 * RoleServiceTest.php - Unit Tests für RoleService
 * 
 * v0.2.0 Tests: Multi-Role RBAC System
 * Target: 50+ Tests
 * 
 * @category Test
 * @package Verein\Tests\Unit\Service\RBAC
 * @license AGPL-3.0
 */

namespace OCA\Verein\Tests\Unit\Service\RBAC;

use OCA\Verein\Service\RBAC\RoleService;
use OCA\Verein\Exception\ValidationException;
use OCP\IUserSession;
use PHPUnit\Framework\TestCase;

class RoleServiceTest extends TestCase {
    
    private RoleService $service;
    private IUserSession $userSession;
    
    protected function setUp(): void {
        $this->userSession = $this->createMock(IUserSession::class);
        $this->service = new RoleService($this->userSession);
    }
    
    /**
     * Test 1: Music club roles return correct structure
     */
    public function testGetMusicClubRoles(): void {
        $roles = $this->service->getRolesForClubType('music');
        
        $this->assertIsArray($roles);
        $this->assertCount(6, $roles);
        $this->assertArrayHasKey('admin', $roles);
        $this->assertArrayHasKey('treasurer', $roles);
        $this->assertArrayHasKey('musician', $roles);
        $this->assertArrayHasKey('conductor', $roles);
        $this->assertArrayHasKey('secretary', $roles);
        $this->assertArrayHasKey('viewer', $roles);
    }
    
    /**
     * Test 2: Sport club roles return correct structure
     */
    public function testGetSportClubRoles(): void {
        $roles = $this->service->getRolesForClubType('sport');
        
        $this->assertIsArray($roles);
        $this->assertCount(4, $roles);
        $this->assertArrayHasKey('admin', $roles);
        $this->assertArrayHasKey('treasurer', $roles);
        $this->assertArrayHasKey('coach', $roles);
        $this->assertArrayHasKey('member', $roles);
    }
    
    /**
     * Test 3: Admin role has all permissions
     */
    public function testAdminRoleHasAllPermissions(): void {
        $roles = $this->service->getRolesForClubType('music');
        $adminPerms = $roles['admin']['permissions'];
        
        $this->assertContains('member.create', $adminPerms);
        $this->assertContains('member.delete', $adminPerms);
        $this->assertContains('export.sepa', $adminPerms);
        $this->assertContains('export.pdf', $adminPerms);
        $this->assertContains('settings.manage', $adminPerms);
    }
    
    /**
     * Test 4: Viewer role has limited permissions
     */
    public function testViewerRoleHasLimitedPermissions(): void {
        $roles = $this->service->getRolesForClubType('music');
        $viewerPerms = $roles['viewer']['permissions'];
        
        $this->assertContains('member.read', $viewerPerms);
        $this->assertContains('finance.read', $viewerPerms);
        $this->assertNotContains('member.delete', $viewerPerms);
        $this->assertNotContains('export.sepa', $viewerPerms);
    }
    
    /**
     * Test 5: Invalid club type throws ValidationException
     */
    public function testInvalidClubTypeThrowsException(): void {
        $this->expectException(ValidationException::class);
        $this->service->getRolesForClubType('invalid');
    }
    
    /**
     * Test 6: Permission names are case-sensitive
     */
    public function testPermissionNamesAreCaseSensitive(): void {
        // Valid
        $this->assertTrue($this->service->isValidPermission('member.create'));
        
        // Invalid (wrong case)
        $this->assertFalse($this->service->isValidPermission('Member.Create'));
        $this->assertFalse($this->service->isValidPermission('MEMBER.CREATE'));
    }
    
    /**
     * Test 7: All permissions are documented
     */
    public function testAllPermissionsAreDocumented(): void {
        $permissions = [
            'member.create', 'member.read', 'member.update', 'member.delete',
            'finance.create', 'finance.read', 'finance.update', 'finance.delete',
            'export.sepa', 'export.pdf',
            'role.assign', 'role.delete',
            'settings.manage',
            'score.create', 'score.read', 'score.update',
            'musician.assign',
            'athlete.assign', 'training.create', 'training.read', 'training.update',
            'audit.view'
        ];
        
        foreach ($permissions as $perm) {
            $this->assertTrue($this->service->isValidPermission($perm));
            $description = $this->service->getPermissionDescription($perm);
            $this->assertNotEmpty($description);
            $this->assertNotEquals($perm, $description); // Description shouldn't be the same as permission
        }
    }
    
    /**
     * Test 8: Treasurer can export SEPA
     */
    public function testTreasurerCanExportSepa(): void {
        $roles = $this->service->getRolesForClubType('music');
        $treasurerPerms = $roles['treasurer']['permissions'];
        
        $this->assertContains('export.sepa', $treasurerPerms);
    }
    
    /**
     * Test 9: Conductor cannot delete members
     */
    public function testConductorCannotDeleteMembers(): void {
        $roles = $this->service->getRolesForClubType('music');
        $conductorPerms = $roles['conductor']['permissions'];
        
        $this->assertNotContains('member.delete', $conductorPerms);
    }
    
    /**
     * Test 10: Each role has description
     */
    public function testEachRoleHasDescription(): void {
        $roles = $this->service->getRolesForClubType('music');
        
        foreach ($roles as $roleKey => $roleData) {
            $this->assertArrayHasKey('label', $roleData);
            $this->assertArrayHasKey('description', $roleData);
            $this->assertArrayHasKey('permissions', $roleData);
            $this->assertNotEmpty($roleData['label']);
            $this->assertNotEmpty($roleData['description']);
            $this->assertIsArray($roleData['permissions']);
        }
    }
    
    /**
     * Test 11: Musician role (music club) different from member role (sport club)
     */
    public function testMusicianRoleDifferentFromMemberRole(): void {
        $musicRoles = $this->service->getRolesForClubType('music');
        $sportRoles = $this->service->getRolesForClubType('sport');
        
        $musicianPerms = $musicRoles['musician']['permissions'];
        $memberPerms = $sportRoles['member']['permissions'];
        
        // Musician has score permissions
        $this->assertContains('score.read', $musicianPerms);
        // Member doesn't
        $this->assertNotContains('score.read', $memberPerms);
    }
    
    /**
     * Test 12: Coach role can create training
     */
    public function testCoachCanCreateTraining(): void {
        $roles = $this->service->getRolesForClubType('sport');
        $coachPerms = $roles['coach']['permissions'];
        
        $this->assertContains('training.create', $coachPerms);
        $this->assertContains('training.read', $coachPerms);
        $this->assertContains('training.update', $coachPerms);
    }
    
    /**
     * Test 13: Permission description is human-readable
     */
    public function testPermissionDescriptionIsReadable(): void {
        $desc = $this->service->getPermissionDescription('member.create');
        
        $this->assertNotEmpty($desc);
        $this->assertStringContainsString('Mitglied', $desc);
    }
}
