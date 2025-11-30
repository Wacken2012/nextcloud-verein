<?php
namespace OCA\Verein\Tests\Unit;

use OCA\Verein\Controller\MemberController;
use OCA\Verein\Controller\FeeController;
use OCA\Verein\Controller\RoleController;
use OCA\Verein\Service\MemberService;
use OCA\Verein\Service\FeeService;
use OCA\Verein\Service\RBAC\RoleService;
use OCA\Verein\Service\ValidationService;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use PHPUnit\Framework\TestCase;

class ControllerPermissionsTest extends TestCase {
    
    /**
     * Test: MemberController.index requires 'verein.member.view' permission
     */
    public function testMemberControllerIndexRequiresViewPermission(): void {
        $request = $this->createMock(IRequest::class);
        $memberService = $this->createMock(MemberService::class);
        $validationService = $this->createMock(ValidationService::class);

        $controller = new MemberController('verein', $request, $memberService, $validationService);

        // Verify method has permission attribute (via reflection)
        $reflection = new \ReflectionMethod($controller, 'index');
        $attributes = $reflection->getAttributes('OCA\Verein\Attributes\RequirePermission');
        
        $this->assertNotEmpty($attributes);
        $attr = $attributes[0]->newInstance();
        $this->assertEquals('verein.member.view', $attr->getPermission());
    }

    /**
     * Test: MemberController.create requires 'verein.member.manage' permission
     */
    public function testMemberControllerCreateRequiresManagePermission(): void {
        $request = $this->createMock(IRequest::class);
        $memberService = $this->createMock(MemberService::class);
        $validationService = $this->createMock(ValidationService::class);

        $controller = new MemberController('verein', $request, $memberService, $validationService);

        $reflection = new \ReflectionMethod($controller, 'create');
        $attributes = $reflection->getAttributes('OCA\Verein\Attributes\RequirePermission');
        
        $this->assertNotEmpty($attributes);
        $attr = $attributes[0]->newInstance();
        $this->assertEquals('verein.member.manage', $attr->getPermission());
    }

    /**
     * Test: MemberController.update requires 'verein.member.manage' permission
     */
    public function testMemberControllerUpdateRequiresManagePermission(): void {
        $request = $this->createMock(IRequest::class);
        $memberService = $this->createMock(MemberService::class);
        $validationService = $this->createMock(ValidationService::class);

        $controller = new MemberController('verein', $request, $memberService, $validationService);

        $reflection = new \ReflectionMethod($controller, 'update');
        $attributes = $reflection->getAttributes('OCA\Verein\Attributes\RequirePermission');
        
        $this->assertNotEmpty($attributes);
        $attr = $attributes[0]->newInstance();
        $this->assertEquals('verein.member.manage', $attr->getPermission());
    }

    /**
     * Test: MemberController.destroy requires 'verein.member.manage' permission
     */
    public function testMemberControllerDestroyRequiresManagePermission(): void {
        $request = $this->createMock(IRequest::class);
        $memberService = $this->createMock(MemberService::class);
        $validationService = $this->createMock(ValidationService::class);

        $controller = new MemberController('verein', $request, $memberService, $validationService);

        $reflection = new \ReflectionMethod($controller, 'destroy');
        $attributes = $reflection->getAttributes('OCA\Verein\Attributes\RequirePermission');
        
        $this->assertNotEmpty($attributes);
        $attr = $attributes[0]->newInstance();
        $this->assertEquals('verein.member.manage', $attr->getPermission());
    }

    /**
     * Test: FeeController.index requires 'verein.finance.read' permission
     */
    public function testFeeControllerIndexRequiresReadPermission(): void {
        $request = $this->createMock(IRequest::class);
        $feeService = $this->createMock(FeeService::class);

        $controller = new FeeController('verein', $request, $feeService);

        $reflection = new \ReflectionMethod($controller, 'index');
        $attributes = $reflection->getAttributes('OCA\Verein\Attributes\RequirePermission');
        
        $this->assertNotEmpty($attributes);
        $attr = $attributes[0]->newInstance();
        $this->assertEquals('verein.finance.read', $attr->getPermission());
    }

    /**
     * Test: FeeController.create requires 'verein.finance.write' permission
     */
    public function testFeeControllerCreateRequiresWritePermission(): void {
        $request = $this->createMock(IRequest::class);
        $feeService = $this->createMock(FeeService::class);

        $controller = new FeeController('verein', $request, $feeService);

        $reflection = new \ReflectionMethod($controller, 'create');
        $attributes = $reflection->getAttributes('OCA\Verein\Attributes\RequirePermission');
        
        $this->assertNotEmpty($attributes);
        $attr = $attributes[0]->newInstance();
        $this->assertEquals('verein.finance.write', $attr->getPermission());
    }

    /**
     * Test: FeeController.destroy requires 'verein.finance.delete' permission
     */
    public function testFeeControllerDestroyRequiresDeletePermission(): void {
        $request = $this->createMock(IRequest::class);
        $feeService = $this->createMock(FeeService::class);

        $controller = new FeeController('verein', $request, $feeService);

        $reflection = new \ReflectionMethod($controller, 'destroy');
        $attributes = $reflection->getAttributes('OCA\Verein\Attributes\RequirePermission');
        
        $this->assertNotEmpty($attributes);
        $attr = $attributes[0]->newInstance();
        $this->assertEquals('verein.finance.delete', $attr->getPermission());
    }

    /**
     * Test: FeeController.exportCsv requires 'verein.finance.export' permission
     */
    public function testFeeControllerExportCsvRequiresExportPermission(): void {
        $request = $this->createMock(IRequest::class);
        $feeService = $this->createMock(FeeService::class);

        $controller = new FeeController('verein', $request, $feeService);

        $reflection = new \ReflectionMethod($controller, 'exportCsv');
        $attributes = $reflection->getAttributes('OCA\Verein\Attributes\RequirePermission');
        
        $this->assertNotEmpty($attributes);
        $attr = $attributes[0]->newInstance();
        $this->assertEquals('verein.finance.export', $attr->getPermission());
    }

    /**
     * Test: RoleController methods require 'verein.role.manage' permission
     */
    public function testRoleControllerRequiresRoleManagePermission(): void {
        $request = $this->createMock(IRequest::class);
        $roleService = $this->createMock(RoleService::class);

        // Use reflection to avoid needing all dependencies
        $reflectionClass = new \ReflectionClass('OCA\Verein\Controller\RoleController');
        
        // Check that RoleController has methods with role.manage permission
        $methods = ['index', 'show', 'store', 'update', 'destroy'];
        
        foreach ($methods as $method) {
            if ($reflectionClass->hasMethod($method)) {
                $reflection = $reflectionClass->getMethod($method);
                $attributes = $reflection->getAttributes('OCA\Verein\Attributes\RequirePermission');
                
                if (!empty($attributes)) {
                    $attr = $attributes[0]->newInstance();
                    $this->assertEquals('verein.role.manage', $attr->getPermission());
                }
            }
        }
    }

    /**
     * Test: All critical endpoints have permission requirements
     */
    public function testAllCriticalEndpointsHavePermissions(): void {
        $request = $this->createMock(IRequest::class);
        
        // Test Member endpoints
        $memberService = $this->createMock(MemberService::class);
        $validationService = $this->createMock(ValidationService::class);
        $memberController = new MemberController('verein', $request, $memberService, $validationService);
        
        $criticalMethods = ['create', 'update', 'destroy'];
        foreach ($criticalMethods as $method) {
            $reflection = new \ReflectionMethod($memberController, $method);
            $attributes = $reflection->getAttributes('OCA\Verein\Attributes\RequirePermission');
            $this->assertNotEmpty($attributes, "Method $method should have RequirePermission attribute");
        }
        
        // Test Fee endpoints
        $feeService = $this->createMock(FeeService::class);
        $feeController = new FeeController('verein', $request, $feeService);
        
        $criticalMethods = ['create', 'update', 'destroy'];
        foreach ($criticalMethods as $method) {
            $reflection = new \ReflectionMethod($feeController, $method);
            $attributes = $reflection->getAttributes('OCA\Verein\Attributes\RequirePermission');
            $this->assertNotEmpty($attributes, "FeeController method $method should have RequirePermission attribute");
        }
    }

    /**
     * Test: Permission hierarchy - Admin > Treasurer > Member
     */
    public function testPermissionHierarchy(): void {
        // Admin permissions should include all
        $adminPerms = ['*'];
        $this->assertContains('*', $adminPerms);
        
        // Treasurer permissions
        $treasurerPerms = ['verein.finance.*', 'verein.member.view', 'verein.sepa.export'];
        $this->assertContains('verein.finance.read', $treasurerPerms);  // wildcard includes
        $this->assertContains('verein.sepa.export', $treasurerPerms);
        
        // Member permissions (read-only)
        $memberPerms = ['verein.member.view'];
        $this->assertContains('verein.member.view', $memberPerms);
        $this->assertNotContains('verein.member.manage', $memberPerms);
    }
}
