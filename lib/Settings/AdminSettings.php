<?php
namespace OCA\Verein\Settings;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\Settings\ISettings;
use OCA\Verein\Service\RBAC\RoleService;
use OCA\Verein\Db\RoleMapper;

class AdminSettings implements ISettings {
    private RoleService $roleService;
    private RoleMapper $roleMapper;

    public function __construct(RoleService $roleService, RoleMapper $roleMapper) {
        $this->roleService = $roleService;
        $this->roleMapper = $roleMapper;
    }

    /**
     * @return TemplateResponse
     */
    public function getForm(): TemplateResponse {
        try {
            $roles = $this->roleMapper->findAll();
            $rolesData = array_map(fn($r) => $r->jsonSerialize(), $roles);
        } catch (\Exception $e) {
            $rolesData = [];
        }

        return new TemplateResponse(
            'verein',
            'admin/roles',
            ['roles' => $rolesData],
            'blank'
        );
    }

    /**
     * @return string the section ID
     */
    public function getSection(): string {
        return 'verein';
    }

    /**
     * Priority for ordering
     */
    public function getPriority(): int {
        return 50;
    }
}
