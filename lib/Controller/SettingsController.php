<?php

namespace OCA\Verein\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCA\Verein\Service\AppSettingsService;

class SettingsController extends Controller {
    private AppSettingsService $settingsService;

    public function __construct(string $appName, IRequest $request, AppSettingsService $settingsService) {
        parent::__construct($appName, $request);
        $this->settingsService = $settingsService;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function getAppSettings(): JSONResponse {
        return new JSONResponse([
            'status' => 'ok',
            'data' => $this->settingsService->getAppSettings()
        ]);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function setChartsEnabled(string $enabled = ''): JSONResponse {
        // Accept POST body or param
        $param = $this->request->getParam('enabled', $enabled);
        $flag = $param === '1' || $param === 'true';
        $this->settingsService->setChartsEnabled($flag);
        return new JSONResponse([
            'status' => 'ok',
            'data' => ['enable_charts' => $this->settingsService->isChartsEnabled()]
        ]);
    }
}
