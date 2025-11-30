<?php
namespace OCA\Verein\Tests\Unit;

use OCA\Verein\Attributes\RequirePermission;
use OCA\Verein\Db\Role;
use OCA\Verein\Db\RoleMapper;
use OCA\Verein\Db\UserRole;
use OCA\Verein\Db\UserRoleMapper;
use OCA\Verein\Service\RBAC\RoleService;
use OCP\IGroupManager;
use OCP\ILogger;
use OCP\IUser;
use OCP\IUserSession;
use PHPUnit\Framework\TestCase;

class RBACTest extends TestCase {
    private RoleService $roleService;
    private RoleMapper $roleMapper;
    private UserRoleMapper $userRoleMapper;
    private IGroupManager $groupManager;
    private IUserSession $userSession;
    private ILogger $logger;

    protected function setUp(): void {
        parent::setUp();

        $this->roleMapper = $this->createMock(RoleMapper::class);
        $this->userRoleMapper = $this->createMock(UserRoleMapper::class);
        $this->groupManager = $this->createMock(IGroupManager::class);
        $this->userSession = $this->createMock(IUserSession::class);
        $this->logger = $this->createMock(ILogger::class);

        $this->roleService = new RoleService(
            $this->roleMapper,
            $this->userRoleMapper,
            $this->groupManager,
            $this->userSession,
            $this->logger
        );
    }

    /**
     * Test: Admin user has all permissions
     */
    public function testAdminUserHasAllPermissions(): void {
        $userId = 'admin_user';
        $this->groupManager->method('isAdmin')->with($userId)->willReturn(true);

        $this->assertTrue($this->roleService->userHasPermission($userId, 'verein.member.view'));
        $this->assertTrue($this->roleService->userHasPermission($userId, 'verein.role.manage'));
        $this->assertTrue($this->roleService->userHasPermission($userId, 'verein.finance.delete'));
    }

    /**
     * Test: Non-admin user without roles has no permissions
     */
    public function testNonAdminUserWithoutRolesHasNoPermissions(): void {
        $userId = 'basic_user';
        $this->groupManager->method('isAdmin')->with($userId)->willReturn(false);
        $this->userRoleMapper->method('findByUserId')->with($userId)->willReturn([]);

        $this->assertFalse($this->roleService->userHasPermission($userId, 'verein.member.view'));
        $this->assertFalse($this->roleService->userHasPermission($userId, 'verein.role.manage'));
    }

    /**
     * Test: User with specific role has correct permissions
     */
    public function testUserWithRoleHasCorrectPermissions(): void {
        $userId = 'treasurer_user';
        $roleId = 2;

        // Mock the role
        $role = new Role();
        $role->setId($roleId);
        $role->setName('Treasurer');
        $role->setPermissionsArray([
            'verein.member.view',
            'verein.finance.read',
            'verein.finance.write',
            'verein.sepa.export',
        ]);

        // Mock the user role assignment
        $userRole = new UserRole();
        $userRole->setUserId($userId);
        $userRole->setRoleId($roleId);

        $this->groupManager->method('isAdmin')->with($userId)->willReturn(false);
        $this->userRoleMapper->method('findByUserId')->with($userId)->willReturn([$userRole]);
        $this->roleMapper->method('find')->with($roleId)->willReturn($role);

        // User should have treasurer permissions
        $this->assertTrue($this->roleService->userHasPermission($userId, 'verein.member.view'));
        $this->assertTrue($this->roleService->userHasPermission($userId, 'verein.finance.read'));
        $this->assertTrue($this->roleService->userHasPermission($userId, 'verein.finance.write'));
        $this->assertTrue($this->roleService->userHasPermission($userId, 'verein.sepa.export'));

        // But not admin permissions
        $this->assertFalse($this->roleService->userHasPermission($userId, 'verein.role.manage'));
        $this->assertFalse($this->roleService->userHasPermission($userId, 'verein.finance.delete'));
    }

    /**
     * Test: User with wildcard permission has all matching permissions
     */
    public function testUserWithWildcardPermissionHasAllMatching(): void {
        $userId = 'power_user';
        $roleId = 3;

        // Mock the role with wildcard
        $role = new Role();
        $role->setId($roleId);
        $role->setName('Finance Manager');
        $role->setPermissionsArray(['verein.finance.*']);

        $userRole = new UserRole();
        $userRole->setUserId($userId);
        $userRole->setRoleId($roleId);

        $this->groupManager->method('isAdmin')->with($userId)->willReturn(false);
        $this->userRoleMapper->method('findByUserId')->with($userId)->willReturn([$userRole]);
        $this->roleMapper->method('find')->with($roleId)->willReturn($role);

        // User should have all finance permissions
        $this->assertTrue($this->roleService->userHasPermission($userId, 'verein.finance.read'));
        $this->assertTrue($this->roleService->userHasPermission($userId, 'verein.finance.write'));
        $this->assertTrue($this->roleService->userHasPermission($userId, 'verein.finance.delete'));
        $this->assertTrue($this->roleService->userHasPermission($userId, 'verein.finance.export'));

        // But not member permissions
        $this->assertFalse($this->roleService->userHasPermission($userId, 'verein.member.view'));
    }

    /**
     * Test: User with full admin permission (*) has everything
     */
    public function testUserWithFullWildcardHasAllPermissions(): void {
        $userId = 'super_user';
        $roleId = 1;

        // Mock the role with full wildcard
        $role = new Role();
        $role->setId($roleId);
        $role->setName('Super Admin');
        $role->setPermissionsArray(['*']);

        $userRole = new UserRole();
        $userRole->setUserId($userId);
        $userRole->setRoleId($roleId);

        $this->groupManager->method('isAdmin')->with($userId)->willReturn(false);
        $this->userRoleMapper->method('findByUserId')->with($userId)->willReturn([$userRole]);
        $this->roleMapper->method('find')->with($roleId)->willReturn($role);

        // User should have all permissions
        $this->assertTrue($this->roleService->userHasPermission($userId, 'verein.member.view'));
        $this->assertTrue($this->roleService->userHasPermission($userId, 'verein.finance.delete'));
        $this->assertTrue($this->roleService->userHasPermission($userId, 'verein.role.manage'));
    }

    /**
     * Test: User with multiple roles inherits all permissions
     */
    public function testUserWithMultipleRolesHasAllPermissions(): void {
        $userId = 'multi_role_user';
        $roleId1 = 2;
        $roleId2 = 3;

        // Mock roles
        $role1 = new Role();
        $role1->setId($roleId1);
        $role1->setName('Treasurer');
        $role1->setPermissionsArray(['verein.finance.*']);

        $role2 = new Role();
        $role2->setId($roleId2);
        $role2->setName('Member Manager');
        $role2->setPermissionsArray(['verein.member.manage']);

        // Mock user role assignments
        $userRole1 = new UserRole();
        $userRole1->setUserId($userId);
        $userRole1->setRoleId($roleId1);

        $userRole2 = new UserRole();
        $userRole2->setUserId($userId);
        $userRole2->setRoleId($roleId2);

        $this->groupManager->method('isAdmin')->with($userId)->willReturn(false);
        $this->userRoleMapper->method('findByUserId')->with($userId)->willReturn([$userRole1, $userRole2]);
        $this->roleMapper->method('find')
            ->willReturnMap([
                [$roleId1, $role1],
                [$roleId2, $role2],
            ]);

        // User should have combined permissions from both roles
        $this->assertTrue($this->roleService->userHasPermission($userId, 'verein.finance.read'));
        $this->assertTrue($this->roleService->userHasPermission($userId, 'verein.finance.write'));
        $this->assertTrue($this->roleService->userHasPermission($userId, 'verein.member.manage'));

        // But not full member view (only manage)
        $this->assertFalse($this->roleService->userHasPermission($userId, 'verein.member.view'));
    }

    /**
     * Test: Permission attribute can be instantiated
     */
    public function testRequirePermissionAttribute(): void {
        $attr = new RequirePermission('verein.member.view');
        $this->assertEquals('verein.member.view', $attr->getPermission());
    }

    /**
     * Test: Permission attribute can be repeated
     */
    public function testMultiplePermissionAttributes(): void {
        $attr1 = new RequirePermission('verein.member.view');
        $attr2 = new RequirePermission('verein.member.manage');

        $this->assertEquals('verein.member.view', $attr1->getPermission());
        $this->assertEquals('verein.member.manage', $attr2->getPermission());
    }

    /**
     * Test: All standard permissions are available
     */
    public function testAvailablePermissions(): void {
        $perms = $this->roleService->getAvailablePermissions();

        $this->assertIsArray($perms);
        $this->assertContains('verein.role.manage', $perms);
        $this->assertContains('verein.member.view', $perms);
        $this->assertContains('verein.member.manage', $perms);
        $this->assertContains('verein.finance.read', $perms);
        $this->assertContains('verein.finance.write', $perms);
        $this->assertContains('verein.finance.delete', $perms);
        $this->assertContains('verein.finance.export', $perms);
        $this->assertContains('verein.sepa.export', $perms);
    }

    /**
     * Test: Default role templates are available
     */
    public function testDefaultRoleTemplates(): void {
        $templates = $this->roleService->getDefaultRoleTemplates();

        $this->assertIsArray($templates);
        $this->assertArrayHasKey('music', $templates);
        $this->assertArrayHasKey('sports', $templates);

        // Music club should have Admin, Treasurer, Member roles
        $musicRoles = $templates['music'];
        $this->assertCount(3, $musicRoles);
        $this->assertEquals('Admin', $musicRoles[0]['name']);
        $this->assertEquals('Treasurer', $musicRoles[1]['name']);
        $this->assertEquals('Member', $musicRoles[2]['name']);
    }
}
