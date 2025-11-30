<?php
namespace OCA\Verein\Controller;

use OCA\Verein\Attributes\RequirePermission;
use OCA\Verein\Service\RBAC\RoleService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class PermissionController extends ApiController {
    private RoleService $roleService;

    public function __construct(
        string $appName,
        IRequest $request,
        RoleService $roleService
    ) {
        parent::__construct($appName, $request);
        $this->roleService = $roleService;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    #[RequirePermission('verein.role.manage')]
    public function index(): DataResponse {
        return new DataResponse([
            'permissions' => $this->roleService->getAvailablePermissions(),
            'templates' => $this->roleService->getDefaultRoleTemplates(),
        ]);
    }
}
