<?php

namespace OCA\Verein\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCA\Verein\Service\RBAC\RoleService;
use OCP\IUserSession;

class RolesApiController extends Controller {
    private RoleService $roleService;
    private IUserSession $userSession;

    public function __construct(
        $appName,
        IRequest $request,
        RoleService $roleService,
        IUserSession $userSession
    ) {
        parent::__construct($appName, $request);
        $this->roleService = $roleService;
        $this->userSession = $userSession;
    }

    /**
     * Get all roles
     */
    public function getRoles(): DataResponse {
        try {
            $roles = $this->roleService->getAllRoles();
            return new DataResponse($roles);
        } catch (\Exception $e) {
            return new DataResponse([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Create a new role
     */
    public function createRole(string $name, string $description = ''): DataResponse {
        try {
            $role = $this->roleService->createRole($name, $description);
            return new DataResponse($role);
        } catch (\Exception $e) {
            return new DataResponse([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Update a role
     */
    public function updateRole(int $roleId, string $name, string $description = ''): DataResponse {
        try {
            $role = $this->roleService->updateRole($roleId, $name, $description);
            return new DataResponse($role);
        } catch (\Exception $e) {
            return new DataResponse([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Delete a role
     */
    public function deleteRole(int $roleId): DataResponse {
        try {
            $this->roleService->deleteRole($roleId);
            return new DataResponse(['success' => true]);
        } catch (\Exception $e) {
            return new DataResponse([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Update role permissions
     */
    public function updatePermissions(int $roleId): DataResponse {
        try {
            $permissions = $this->request->getParam('permissions', []);
            $role = $this->roleService->updatePermissions($roleId, $permissions);
            return new DataResponse($role);
        } catch (\Exception $e) {
            return new DataResponse([
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
