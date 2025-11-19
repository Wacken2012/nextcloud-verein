<?php
namespace OCA\Verein\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\AppFramework\Http\TemplateResponse;
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
        
        // Do not override the default Nextcloud Content Security Policy here.
        // Leaving CSP management to the Nextcloud core ensures the required
        // inline scripts (nonces / hashes) and global runtime objects (like
        // OC) are available to apps. Setting a CSP here caused inline scripts
        // to be blocked (OC undefined) in some browsers.
        
        return $response;
    }
}

