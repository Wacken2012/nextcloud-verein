<?php
namespace OCA\Verein\Controller;

use OCA\Verein\Attributes\RequirePermission;
use OCA\Verein\Db\RoleMapper;
use OCA\Verein\Service\RBAC\RoleService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class RoleController extends ApiController {
    private RoleService $roleService;
    private RoleMapper $roleMapper;
    
    public function __construct(
        string $appName,
        IRequest $request,
        RoleService $roleService,
        RoleMapper $roleMapper
    ) {
        parent::__construct($appName, $request);
        $this->roleService = $roleService;
        $this->roleMapper = $roleMapper;
    }
    
    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    #[RequirePermission('verein.role.manage')]
    public function index(): DataResponse {
        try {
            $roles = $this->roleMapper->findAll();
            return new DataResponse(array_map(fn($r) => $r->jsonSerialize(), $roles));
        } catch (\Exception $e) {
            return new DataResponse(['error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    #[RequirePermission('verein.role.manage')]
    public function indexByClubType(string $clubType): DataResponse {
        try {
            $roles = $this->roleService->getRolesForClubType($clubType);
            return new DataResponse($roles);
        } catch (\Exception $e) {
            return new DataResponse(['error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    #[RequirePermission('verein.role.manage')]
    public function show(int $id): DataResponse {
        try {
            $role = $this->roleMapper->find($id);
            return new DataResponse($role->jsonSerialize());
        } catch (\Exception $e) {
            return new DataResponse(['error' => 'Rolle nicht gefunden'], 404);
        }
    }
    
    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    #[RequirePermission('verein.role.manage')]
    public function store(): DataResponse {
        try {
            $name = $this->request->getParam('name');
            $clubType = $this->request->getParam('clubType') ?? 'music';
            $description = $this->request->getParam('description');
            $permissionInput = $this->request->getParam('permissions') ?? [];
            $permissions = is_array($permissionInput)
                ? $permissionInput
                : array_filter(array_map('trim', explode(',', (string)$permissionInput)));
            
            if (!$name) {
                return new DataResponse(['error' => 'Name erforderlich'], 400);
            }
            
            $role = $this->roleService->createRole($name, $clubType, $description, $permissions);
            return new DataResponse($role->jsonSerialize(), 201);
        } catch (\Exception $e) {
            return new DataResponse(['error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    #[RequirePermission('verein.role.manage')]
    public function update(int $id): DataResponse {
        try {
            $name = $this->request->getParam('name');
            $description = $this->request->getParam('description');
            $permissionInput = $this->request->getParam('permissions');
            $permissions = null;
            if ($permissionInput !== null) {
                $permissions = is_array($permissionInput)
                    ? $permissionInput
                    : array_filter(array_map('trim', explode(',', (string)$permissionInput)));
            }
            
            $role = $this->roleService->updateRole($id, $name, $description, $permissions);
            return new DataResponse($role->jsonSerialize());
        } catch (\Exception $e) {
            return new DataResponse(['error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    #[RequirePermission('verein.role.manage')]
    public function destroy(int $id): DataResponse {
        try {
            $this->roleService->deleteRole($id);
            return new DataResponse(['success' => true]);
        } catch (\Exception $e) {
            return new DataResponse(['error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    #[RequirePermission('verein.role.manage')]
    public function getUserRoles(string $userId, int $clubId = 0): DataResponse {
        try {
            $clubIdParam = $this->request->getParam('clubId');
            $clubIdToUse = $clubIdParam !== null ? (int)$clubIdParam : $clubId;
            $roles = $this->roleService->getUserRoles($userId, $clubIdToUse);
            return new DataResponse($roles);
        } catch (\Exception $e) {
            return new DataResponse(['error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    #[RequirePermission('verein.role.manage')]
    public function assignRole(): DataResponse {
        try {
            $userId = $this->request->getParam('userId');
            $roleId = (int)$this->request->getParam('roleId');
            $clubId = (int)($this->request->getParam('clubId') ?? 0);
            
            if (!$userId || !$roleId) {
                return new DataResponse(['error' => 'userId und roleId erforderlich'], 400);
            }
            
            $userRole = $this->roleService->assignRole($userId, $roleId, $clubId);
            return new DataResponse($userRole->jsonSerialize(), 201);
        } catch (\Exception $e) {
            return new DataResponse(['error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    #[RequirePermission('verein.role.manage')]
    public function removeRoles(): DataResponse {
        try {
            $userId = $this->request->getParam('userId');
            $clubIdParam = $this->request->getParam('clubId');
            $clubId = $clubIdParam !== null ? (int)$clubIdParam : null;
            
            if (!$userId) {
                return new DataResponse(['error' => 'userId erforderlich'], 400);
            }
            
            $this->roleService->removeUserRoles($userId, $clubId);
            return new DataResponse(['success' => true]);
        } catch (\Exception $e) {
            return new DataResponse(['error' => $e->getMessage()], 400);
        }
    }
}
