<?php
namespace OCA\Verein\Controller;

use Exception;
use OCA\Verein\Service\MemberService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

class MemberController extends Controller {
    private MemberService $memberService;

    public function __construct($AppName, IRequest $request, MemberService $memberService) {
        parent::__construct($AppName, $request);
        $this->memberService = $memberService;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
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
    public function create() {
        try {
            $name = $this->request->getParam('name');
            $address = $this->request->getParam('address');
            $email = $this->request->getParam('email');
            $iban = $this->request->getParam('iban');
            $bic = $this->request->getParam('bic');
            $role = $this->request->getParam('role', 'member');

            $member = $this->memberService->create($name, $address, $email, $iban, $bic, $role);
            return new JSONResponse($member);
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
            $name = $this->request->getParam('name');
            $address = $this->request->getParam('address');
            $email = $this->request->getParam('email');
            $iban = $this->request->getParam('iban');
            $bic = $this->request->getParam('bic');
            $role = $this->request->getParam('role');

            $member = $this->memberService->update($id, $name, $address, $email, $iban, $bic, $role);
            return new JSONResponse($member);
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
            $member = $this->memberService->delete($id);
            return new JSONResponse([
                'status' => 'ok',
                'message' => 'Member deleted'
            ]);
        } catch (Exception $e) {
            return new JSONResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 404);
        }
    }
}
