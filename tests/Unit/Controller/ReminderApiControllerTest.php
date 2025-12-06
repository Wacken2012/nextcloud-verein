<?php

namespace OCA\Verein\Tests\Unit\Controller;

use PHPUnit\Framework\TestCase;
use OCA\Verein\Controller\ReminderApiController;
use OCA\Verein\Service\ReminderService;
use OCP\IRequest;
use OCP\AppFramework\Http\JSONResponse;

class ReminderApiControllerTest extends TestCase {
	private ReminderApiController $controller;
	private $request;
	private $reminderService;

	protected function setUp(): void {
		parent::setUp();

		$this->request = $this->createMock(IRequest::class);
		$this->reminderService = $this->createMock(ReminderService::class);

		$this->controller = new ReminderApiController(
			'verein',
			$this->request,
			$this->reminderService
		);
	}

	public function testGetConfigReturnsJSONResponse(): void {
		$config = [
			'enabled' => true,
			'intervals' => ['level_1' => 7, 'level_2' => 3, 'level_3' => 7],
			'daysBetween' => 3
		];

		$this->reminderService->method('getConfig')
			->willReturn($config);

		$response = $this->controller->getConfig();

		$this->assertInstanceOf(JSONResponse::class, $response);
		$this->assertEquals($config, $response->getData());
	}

	public function testGetLogReturnsJSONResponse(): void {
		$log = [
			['id' => 1, 'member_id' => 1, 'level' => 1, 'sent_at' => '2025-12-01'],
			['id' => 2, 'member_id' => 2, 'level' => 2, 'sent_at' => '2025-12-02'],
		];

		$this->reminderService->method('getLog')
			->willReturn($log);

		$response = $this->controller->getLog();

		$this->assertInstanceOf(JSONResponse::class, $response);
		$this->assertEquals($log, $response->getData());
	}

	public function testGetLogReturnsEmptyArray(): void {
		$this->reminderService->method('getLog')
			->willReturn([]);

		$response = $this->controller->getLog();

		$this->assertInstanceOf(JSONResponse::class, $response);
		$this->assertEquals([], $response->getData());
	}
}
