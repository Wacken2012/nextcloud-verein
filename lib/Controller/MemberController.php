<?php
namespace OCA\Verein\Controller;

use Exception;
use OCA\Verein\Attributes\RequirePermission;
use OCA\Verein\Service\MemberService;
use OCA\Verein\Service\ValidationService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

class MemberController extends Controller {
    private MemberService $memberService;
    private ValidationService $validationService;

    public function __construct(
        $AppName,
        IRequest $request,
        MemberService $memberService,
        ValidationService $validationService
    ) {
        parent::__construct($AppName, $request);
        $this->memberService = $memberService;
        $this->validationService = $validationService;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    #[RequirePermission('verein.member.view')]
    public function index() {
        try {
            $members = $this->memberService->findAll();
            return new JSONResponse([
                'status' => 'ok',
                'members' => $members
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
    #[RequirePermission('verein.member.view')]
    public function show(int $id) {
        try {
            $member = $this->memberService->find($id);
            return new JSONResponse([
                'status' => 'ok',
                'data' => $member
            ]);
        } catch (Exception $e) {
            return new JSONResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    #[RequirePermission('verein.member.manage')]
    public function create() {
        try {
            $name = $this->request->getParam('name');
            $address = $this->request->getParam('address');
            $email = $this->request->getParam('email');
            $iban = $this->request->getParam('iban');
            $bic = $this->request->getParam('bic');
            $role = $this->request->getParam('role', 'member');

            // Validierung
            $validation = $this->validationService->validateMember($name, $email, $iban);
            if (!$validation['valid']) {
                return new JSONResponse([
                    'status' => 'error',
                    'message' => 'Validierung fehlgeschlagen',
                    'errors' => $validation['errors']
                ], 400);
            }

            // Rolle validieren
            if (!$this->validationService->validateRole($role)) {
                return new JSONResponse([
                    'status' => 'error',
                    'message' => 'Ungültige Rolle',
                    'errors' => ['Rolle muss Mitglied, Kassierer oder Admin sein']
                ], 400);
            }

            $member = $this->memberService->create($name, $address, $email, $iban, $bic, $role);
            return new JSONResponse([
                'status' => 'ok',
                'data' => $member
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
    #[RequirePermission('verein.member.manage')]
    public function update($id) {
        try {
            $name = (string)$this->request->getParam('name', '');
            $address = (string)$this->request->getParam('address', '');
            $email = (string)$this->request->getParam('email', '');
            $iban = (string)$this->request->getParam('iban', '');
            $bic = (string)$this->request->getParam('bic', '');
            $role = (string)$this->request->getParam('role', 'member');

            // Validierung
            $validation = $this->validationService->validateMember($name, $email, $iban);
            if (!$validation['valid']) {
                return new JSONResponse([
                    'status' => 'error',
                    'message' => 'Validierung fehlgeschlagen',
                    'errors' => $validation['errors']
                ], 400);
            }

            // Rolle validieren wenn angegeben
            if ($role && !$this->validationService->validateRole($role)) {
                return new JSONResponse([
                    'status' => 'error',
                    'message' => 'Ungültige Rolle',
                    'errors' => ['Rolle muss Mitglied, Kassierer oder Admin sein']
                ], 400);
            }

            $member = $this->memberService->update($id, $name, $address, $email, $iban, $bic, $role);
            return new JSONResponse([
                'status' => 'ok',
                'data' => $member
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
    #[RequirePermission('verein.member.manage')]
    public function destroy($id) {
        try {
            $this->memberService->delete($id);
            return new JSONResponse([
                'status' => 'ok',
                'message' => 'Mitglied gelöscht'
            ]);
        } catch (Exception $e) {
            return new JSONResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 404);
        }
    }
}
