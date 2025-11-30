<?php
namespace OCA\Verein\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\Util;
use OCP\IRequest;

class PageController extends Controller {
    public function __construct(
        string $AppName,
        IRequest $request
    ) {
        parent::__construct($AppName, $request);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function index(): TemplateResponse {
        $response = new TemplateResponse('verein', 'main', [
            'id-app-content' => '#app-content',
            'id-app-navigation' => '#verein-navigation',
            'pageTitle' => 'Verein',
        ]);

        // Ensure Nextcloud core scripts are loaded so OC and translations are available
        Util::addScript('core', 'common');
        Util::addScript('core', 'main');

        // Rely on Nextcloud's default CSP with nonces; do not override here

        return $response;
    }
}

