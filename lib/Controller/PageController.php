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
        $response = new TemplateResponse('verein', 'main');
        
        // Add Content Security Policy
        $csp = new ContentSecurityPolicy();
        $csp->addAllowedScriptDomain("'self'");
        $csp->addAllowedStyleDomain("'self'");
        $csp->addAllowedStyleDomain("'unsafe-inline'");
        $csp->addAllowedImageDomain('*');
        $csp->addAllowedFontDomain("'self'");
        $csp->addAllowedConnectDomain('*');
        $response->setContentSecurityPolicy($csp);
        
        return $response;
    }
}

