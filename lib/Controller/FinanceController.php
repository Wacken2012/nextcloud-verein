<?php
namespace OCA\Verein\Controller;

use Exception;
use OCA\Verein\Service\FeeService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

class FinanceController extends Controller {
    private FeeService $feeService;

    public function __construct($AppName, IRequest $request, FeeService $feeService) {
        parent::__construct($AppName, $request);
        $this->feeService = $feeService;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function index() {
        try {
            $fees = $this->feeService->findAll();
            return new JSONResponse([
                'status' => 'ok',
                'fees' => $fees
            ]);
        } catch (Exception $e) {
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
    public function create() {
        try {
            $memberId = $this->request->getParam('memberId');
            $amount = $this->request->getParam('amount');
            $status = $this->request->getParam('status', 'pending');
            $dueDate = $this->request->getParam('dueDate');

            $fee = $this->feeService->create($memberId, $amount, $status, $dueDate);
            return new JSONResponse($fee);
        } catch (Exception $e) {
            return new JSONResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function update($id) {
        try {
            $memberId = $this->request->getParam('memberId');
            $amount = $this->request->getParam('amount');
            $status = $this->request->getParam('status');
            $dueDate = $this->request->getParam('dueDate');

            $fee = $this->feeService->update($id, $memberId, $amount, $status, $dueDate);
            return new JSONResponse($fee);
        } catch (Exception $e) {
            return new JSONResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function destroy($id) {
        try {
            $fee = $this->feeService->delete($id);
            return new JSONResponse([
                'status' => 'ok',
                'message' => 'Fee deleted'
            ]);
        } catch (Exception $e) {
            return new JSONResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 404);
        }
    }
}
