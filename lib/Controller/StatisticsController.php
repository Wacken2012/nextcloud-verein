<?php

namespace OCA\Verein\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCA\Verein\Service\StatisticsService;

class StatisticsController extends Controller {
    private StatisticsService $statisticsService;

    public function __construct(
        string $appName,
        IRequest $request,
        StatisticsService $statisticsService
    ) {
        parent::__construct($appName, $request);
        $this->statisticsService = $statisticsService;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function getMemberStatistics(): JSONResponse {
        try {
            $stats = $this->statisticsService->getMemberStatistics();
            return new JSONResponse([
                'status' => 'ok',
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return new JSONResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function getFeeStatistics(): JSONResponse {
        try {
            $stats = $this->statisticsService->getFeeStatistics();
            return new JSONResponse([
                'status' => 'ok',
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return new JSONResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
