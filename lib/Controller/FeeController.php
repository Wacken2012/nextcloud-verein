<?php
namespace OCA\Verein\Controller;

use OCA\Verein\Attributes\RequirePermission;
use OCA\Verein\Service\FeeService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class FeeController extends ApiController {
    private FeeService $service;

    public function __construct(
        string $appName,
        IRequest $request,
        FeeService $service
    ) {
        parent::__construct($appName, $request);
        $this->service = $service;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    #[RequirePermission('verein.finance.read')]
    public function index(): DataResponse {
        return new DataResponse($this->service->findAll());
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    #[RequirePermission('verein.finance.read')]
    public function show(int $id): DataResponse {
        return new DataResponse($this->service->find($id));
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    #[RequirePermission('verein.finance.write')]
    public function create(
        int $memberId,
        float $amount,
        string $status,
        string $dueDate,
        ?string $description = null
    ): DataResponse {
        return new DataResponse($this->service->create(
            $memberId, $amount, $status, $dueDate, $description
        ));
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    #[RequirePermission('verein.finance.write')]
    public function update(
        int $id,
        int $memberId,
        float $amount,
        string $status,
        string $dueDate,
        ?string $description = null
    ): DataResponse {
        return new DataResponse($this->service->update(
            $id, $memberId, $amount, $status, $dueDate, $description
        ));
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    #[RequirePermission('verein.finance.delete')]
    public function destroy(int $id): DataResponse {
        return new DataResponse($this->service->delete($id));
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    #[RequirePermission('verein.finance.export')]
    public function exportCsv(): DataResponse {
        return new DataResponse($this->service->exportToCsv());
    }
}
