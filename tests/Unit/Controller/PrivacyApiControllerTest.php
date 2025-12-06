<?php

namespace OCA\Verein\Tests\Unit\Controller;

use PHPUnit\Framework\TestCase;
use OCA\Verein\Controller\PrivacyApiController;
use OCA\Verein\Service\PrivacyService;
use OCP\IRequest;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\DataDownloadResponse;
use OCP\AppFramework\Http;

class PrivacyApiControllerTest extends TestCase {
	private PrivacyApiController $controller;
	private $request;
	private $privacyService;

	protected function setUp(): void {
		parent::setUp();

		$this->request = $this->createMock(IRequest::class);
		$this->privacyService = $this->createMock(PrivacyService::class);

		$this->controller = new PrivacyApiController(
			'verein',
			$this->request,
			$this->privacyService
		);
	}

	public function testGetPolicyReturnsJSONResponse(): void {
		$this->privacyService->method('getPrivacyPolicy')
			->willReturn('Test Privacy Policy');

		$response = $this->controller->getPolicy();

		$this->assertInstanceOf(JSONResponse::class, $response);
		$data = $response->getData();
		$this->assertArrayHasKey('policy', $data);
		$this->assertEquals('Test Privacy Policy', $data['policy']);
	}

	public function testGetPolicyReturnsEmptyPolicy(): void {
		$this->privacyService->method('getPrivacyPolicy')
			->willReturn('');

		$response = $this->controller->getPolicy();

		$this->assertInstanceOf(JSONResponse::class, $response);
		$data = $response->getData();
		$this->assertEquals('', $data['policy']);
	}

	public function testGetConsentReturnsJSONResponse(): void {
		$consents = [
			'newsletter' => true,
			'marketing' => false
		];

		$this->privacyService->method('getMemberConsents')
			->with(1)
			->willReturn($consents);

		$response = $this->controller->getConsent(1);

		$this->assertInstanceOf(JSONResponse::class, $response);
	}

	public function testExportDataWithStringUserIdReturnsStubData(): void {
		// String userId (not numeric) should return stub data
		$response = $this->controller->exportData('ncuser');

		$this->assertInstanceOf(DataDownloadResponse::class, $response);
	}

	public function testExportDataWithNumericIdCallsService(): void {
		$memberData = [
			'member' => ['id' => 1, 'name' => 'Test'],
			'exported_at' => '2025-12-04'
		];

		$this->privacyService->method('exportMemberData')
			->with(1)
			->willReturn($memberData);

		$response = $this->controller->exportData(1);

		$this->assertInstanceOf(DataDownloadResponse::class, $response);
	}

	public function testExportDataHandlesException(): void {
		$this->privacyService->method('exportMemberData')
			->willThrowException(new \Exception('Member not found'));

		$response = $this->controller->exportData(999);

		$this->assertInstanceOf(JSONResponse::class, $response);
		$this->assertEquals(Http::STATUS_FORBIDDEN, $response->getStatus());
	}
}
