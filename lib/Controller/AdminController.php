<?php
namespace OCA\Verein\Controller;

use OCA\Verein\Attributes\RequirePermission;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;
use OCA\Verein\Service\RBAC\RoleService;
use OCA\Verein\Db\RoleMapper;

class AdminController extends Controller {
    private RoleService $roleService;
    private RoleMapper $roleMapper;
    
    public function __construct(
        string $AppName,
        IRequest $request,
        RoleService $roleService,
        RoleMapper $roleMapper
    ) {
        parent::__construct($AppName, $request);
        $this->roleService = $roleService;
        $this->roleMapper = $roleMapper;
    }

    /**
     * Zeige die Rollen-Admin-Seite
     * @return TemplateResponse
     */
    #[RequirePermission('verein.role.manage')]
    public function roles(): TemplateResponse {
        try {
            $roles = $this->roleMapper->findAll();
            $rolesData = array_map(fn($r) => $r->jsonSerialize(), $roles);
        } catch (\Exception $e) {
            $rolesData = [];
        }

        return new TemplateResponse('verein', 'admin/roles', [
            'roles' => $rolesData
        ]);
    }
}
