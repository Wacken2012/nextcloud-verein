<?php
namespace OCA\Verein\Tests\Unit;

use OCA\Verein\Attributes\RequirePermission;
use OCA\Verein\Middleware\AuthorizationMiddleware;
use OCA\Verein\Service\RBAC\RoleService;
use OCP\AppFramework\Http\JSONResponse;
use OCP\ILogger;
use OCP\IUser;
use OCP\IUserSession;
use PHPUnit\Framework\TestCase;

class AuthorizationMiddlewareTest extends TestCase {
    private AuthorizationMiddleware $middleware;
    private RoleService $roleService;
    private IUserSession $userSession;
    private ILogger $logger;

    protected function setUp(): void {
        parent::setUp();

        $this->roleService = $this->createMock(RoleService::class);
        $this->userSession = $this->createMock(IUserSession::class);
        $this->logger = $this->createMock(ILogger::class);

        $this->middleware = new AuthorizationMiddleware(
            $this->roleService,
            $this->userSession,
            $this->logger
        );
    }

    /**
     * Test: Middleware allows access when user has required permission
     */
    public function testMiddlewareAllowsAccessWithPermission(): void {
        $user = $this->createMock(IUser::class);
        $user->method('getUID')->willReturn('allowed_user');

        $this->userSession->method('getUser')->willReturn($user);
        $this->roleService->method('userHasPermission')
            ->with('allowed_user', 'verein.member.view')
            ->willReturn(true);

        $controller = new class {
            #[RequirePermission('verein.member.view')]
            public function listMembers() {}
        };

        $result = $this->middleware->beforeController($controller, 'listMembers');
        $this->assertNull($result);
    }

    /**
     * Test: Middleware denies access when user lacks permission
     */
    public function testMiddlewareDeniesAccessWithoutPermission(): void {
        $user = $this->createMock(IUser::class);
        $user->method('getUID')->willReturn('restricted_user');

        $this->userSession->method('getUser')->willReturn($user);
        $this->roleService->method('userHasPermission')
            ->with('restricted_user', 'verein.role.manage')
            ->willReturn(false);

        $controller = new class {
            #[RequirePermission('verein.role.manage')]
            public function manageRoles() {}
        };

        $result = $this->middleware->beforeController($controller, 'manageRoles');
        
        $this->assertInstanceOf(JSONResponse::class, $result);
        $this->assertEquals(403, $result->getStatus());
    }

    /**
     * Test: Middleware allows access when no permissions required
     */
    public function testMiddlewareAllowsPublicMethods(): void {
        $controller = new class {
            public function publicMethod() {}
        };

        $result = $this->middleware->beforeController($controller, 'publicMethod');
        $this->assertNull($result);
    }

    /**
     * Test: Middleware denies unauthenticated users
     */
    public function testMiddlewareDeniesUnauthenticatedUsers(): void {
        $this->userSession->method('getUser')->willReturn(null);

        $controller = new class {
            #[RequirePermission('verein.member.view')]
            public function listMembers() {}
        };

        $result = $this->middleware->beforeController($controller, 'listMembers');

        $this->assertInstanceOf(JSONResponse::class, $result);
        $this->assertEquals(403, $result->getStatus());
    }

    /**
     * Test: Middleware handles multiple permission requirements
     */
    public function testMiddlewareHandlesMultiplePermissionRequirements(): void {
        $user = $this->createMock(IUser::class);
        $user->method('getUID')->willReturn('multi_perm_user');

        $this->userSession->method('getUser')->willReturn($user);
        
        // First call has permission, second doesn't
        $this->roleService->method('userHasPermission')
            ->willReturnMap([
                ['multi_perm_user', 'verein.member.view', true],
                ['multi_perm_user', 'verein.member.manage', false],
            ]);

        // Create controller with multiple permission requirements
        $controller = new class {
            #[RequirePermission('verein.member.view')]
            #[RequirePermission('verein.member.manage')]
            public function criticalOperation() {}
        };

        $result = $this->middleware->beforeController($controller, 'criticalOperation');

        // Should be denied because second permission is missing
        $this->assertInstanceOf(JSONResponse::class, $result);
        $this->assertEquals(403, $result->getStatus());
    }

    /**
     * Test: Middleware logs permission violations
     */
    public function testMiddlewareLogsPermissionViolations(): void {
        $user = $this->createMock(IUser::class);
        $user->method('getUID')->willReturn('violation_user');

        $this->userSession->method('getUser')->willReturn($user);
        $this->roleService->method('userHasPermission')
            ->with('violation_user', 'verein.role.manage')
            ->willReturn(false);

        // Expect logger to be called
        $this->logger->expects($this->once())
            ->method('warning')
            ->with($this->stringContains('RBAC: Permission denied'));

        $controller = new class {
            #[RequirePermission('verein.role.manage')]
            public function manageRoles() {}
        };

        $this->middleware->beforeController($controller, 'manageRoles');
    }

    /**
     * Test: Middleware returns proper 403 JSON response
     */
    public function testMiddlewareReturnsForbiddenResponse(): void {
        $user = $this->createMock(IUser::class);
        $user->method('getUID')->willReturn('denied_user');

        $this->userSession->method('getUser')->willReturn($user);
        $this->roleService->method('userHasPermission')->willReturn(false);

        $controller = new class {
            #[RequirePermission('verein.member.manage')]
            public function createMember() {}
        };

        $result = $this->middleware->beforeController($controller, 'createMember');

        $this->assertInstanceOf(JSONResponse::class, $result);
        $this->assertEquals(403, $result->getStatus());

        // Verify response structure
        $data = $result->getData();
        $this->assertEquals('error', $data['status']);
        $this->assertStringContainsString('Missing permission', $data['message']);
    }
}
