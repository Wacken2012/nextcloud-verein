<?php
namespace OCA\Verein\Controller;

use Exception;
use OCA\Verein\Service\FeeService;
use OCA\Verein\Service\ValidationService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

class FinanceController extends Controller {
    private FeeService $feeService;
    private ValidationService $validationService;

    public function __construct(
        $AppName,
        IRequest $request,
        FeeService $feeService,
        ValidationService $validationService
    ) {
        parent::__construct($AppName, $request);
        $this->feeService = $feeService;
        $this->validationService = $validationService;
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
            $memberId = (int)$this->request->getParam('memberId');
            $amount = (float)$this->request->getParam('amount');
            $description = $this->request->getParam('description', '');
            $status = $this->request->getParam('status', 'open');

            // Validierung
            $validation = $this->validationService->validateFee($memberId, $amount, $description);
            if (!$validation['valid']) {
                return new JSONResponse([
                    'status' => 'error',
                    'message' => 'Validierung fehlgeschlagen',
                    'errors' => $validation['errors']
                ], 400);
            }

            // Status validieren
            if (!$this->validationService->validateFeeStatus($status)) {
                return new JSONResponse([
                    'status' => 'error',
                    'message' => 'Ungültiger Status',
                    'errors' => ['Status muss open, paid, overdue oder cancelled sein']
                ], 400);
            }

            $fee = $this->feeService->create($memberId, $amount, $description, $status);
            return new JSONResponse([
                'status' => 'ok',
                'data' => $fee
            ], 201);
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
    public function update($id) {
        try {
            $amount = (float)$this->request->getParam('amount');
            $description = $this->request->getParam('description');
            $status = $this->request->getParam('status');

            // Validierung Amount und Description
            $memberId = 1; // Platzhalter für Validierung
            $validation = $this->validationService->validateFee($memberId, $amount, $description);
            if (!$validation['valid']) {
                return new JSONResponse([
                    'status' => 'error',
                    'message' => 'Validierung fehlgeschlagen',
                    'errors' => $validation['errors']
                ], 400);
            }

            // Status validieren wenn angegeben
            if ($status && !$this->validationService->validateFeeStatus($status)) {
                return new JSONResponse([
                    'status' => 'error',
                    'message' => 'Ungültiger Status',
                    'errors' => ['Status muss open, paid, overdue oder cancelled sein']
                ], 400);
            }

            $fee = $this->feeService->update($id, $amount, $description, $status);
            return new JSONResponse([
                'status' => 'ok',
                'data' => $fee
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
    public function destroy($id) {
        try {
            $this->feeService->delete($id);
            return new JSONResponse([
                'status' => 'ok',
                'message' => 'Gebühr gelöscht'
            ]);
        } catch (Exception $e) {
            return new JSONResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 404);
        }
    }
}
