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
            // Return predefined roles for this instance
            $roles = $this->roleService->getRolesForClubType('music');
            return new DataResponse($roles);
        } catch (\Exception $e) {
            return new DataResponse([
                'error' => $e->getMessage()
            ], 400);
        }
    }


    /**
     * Create a new role - Not yet implemented
     */
    public function createRole(string $name, string $description = ''): DataResponse {
        return new DataResponse([
            'error' => 'Role creation not yet implemented'
        ], 501);
    }


    /**
     * Update a role - Not yet implemented
     */
    public function updateRole(int $roleId, string $name, string $description = ''): DataResponse {
        return new DataResponse([
            'error' => 'Role update not yet implemented'
        ], 501);
    }


    /**
     * Delete a role - Not yet implemented
     */
    public function deleteRole(int $roleId): DataResponse {
        return new DataResponse([
            'error' => 'Role deletion not yet implemented'
        ], 501);
    }


    /**
     * Update role permissions - Not yet implemented
     */
    public function updatePermissions(int $roleId): DataResponse {
        return new DataResponse([
            'error' => 'Permission update not yet implemented'
        ], 501);
    }
}
