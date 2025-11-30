<?php
namespace OCA\Verein\Settings;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\Settings\ISettings;
use OCP\AppFramework\IAppContainer;

class AdminSettings implements ISettings {
    private IAppContainer $container;

    public function __construct(IAppContainer $container) {
        $this->container = $container;
    }

    /**
     * @return TemplateResponse
     */
    public function getForm(): TemplateResponse {
        try {
            $roleMapper = $this->container->query('OCA\\Verein\\Db\\RoleMapper');
            $roles = $roleMapper->findAll();
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
        return 'verein';  // Must match AdminSection::getID()
    }

    /**
     * Priority for ordering
     */
    public function getPriority(): int {
        return 50;
    }
}
