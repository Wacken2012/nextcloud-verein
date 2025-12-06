<?php

declare(strict_types=1);

namespace OCA\Verein\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

/**
 * Test Controller to debug routing issues
 */
class TestApiController extends Controller {

	public function __construct(
		string $appName,
		IRequest $request,
	) {
		parent::__construct($appName, $request);
	}

	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function testGet(): JSONResponse {
		return new JSONResponse([
			'action' => 'testGet',
			'method' => $this->request->getMethod(),
			'serverMethod' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
		]);
	}

	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function testPost(): JSONResponse {
		return new JSONResponse([
			'action' => 'testPost',
			'method' => $this->request->getMethod(),
			'serverMethod' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
			'body' => json_decode(file_get_contents('php://input'), true)
		]);
	}
}
