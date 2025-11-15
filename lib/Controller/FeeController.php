<?php
namespace OCA\Verein\Controller;

use OCP\IRequest;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\ApiController;
use OCA\Verein\Service\FeeService;

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
     */
    public function index(): DataResponse {
        return new DataResponse($this->service->findAll());
    }

    /**
     * @NoAdminRequired
     */
    public function show(int $id): DataResponse {
        return new DataResponse($this->service->find($id));
    }

    /**
     * @NoAdminRequired
     */
    public function create(
        int $memberId,
        float $amount,
        string $status,
        string $dueDate
    ): DataResponse {
        return new DataResponse($this->service->create(
            $memberId, $amount, $status, $dueDate
        ));
    }

    /**
     * @NoAdminRequired
     */
    public function update(
        int $id,
        int $memberId,
        float $amount,
        string $status,
        string $dueDate
    ): DataResponse {
        return new DataResponse($this->service->update(
            $id, $memberId, $amount, $status, $dueDate
        ));
    }

    /**
     * @NoAdminRequired
     */
    public function destroy(int $id): DataResponse {
        return new DataResponse($this->service->delete($id));
    }

    /**
     * @NoAdminRequired
     */
    public function exportCsv(): DataResponse {
        return new DataResponse($this->service->exportToCsv());
    }
}
