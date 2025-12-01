<?php

namespace OCA\Verein\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use OCA\Verein\Service\ReminderService;
use OCA\Verein\Db\ReminderMapper;
use OCP\ILogger;
use OCP\Mail\IMailer;
use DateTime;

class ReminderServiceTest extends TestCase {
	private ReminderService $reminderService;
	private $reminderMapper;
	private $mailer;
	private $logger;
	private $memberService;
	private $settingService;

	protected function setUp(): void {
		parent::setUp();

		$this->reminderMapper = $this->createMock(ReminderMapper::class);
		$this->mailer = $this->createMock(IMailer::class);
		$this->logger = $this->createMock(ILogger::class);
		$this->memberService = $this->createMock(\OCA\NextcloudVerein\Service\MemberService::class);
		$this->settingService = $this->createMock(\OCA\NextcloudVerein\Service\SettingService::class);

		$this->reminderService = new ReminderService(
			$this->reminderMapper,
			$this->mailer,
			$this->logger,
			$this->memberService,
			$this->settingService
		);
	}

	public function testIsEnabledReturnsFalseByDefault(): void {
		$this->settingService->method('get')
			->willReturn('false');

		$this->assertFalse($this->reminderService->isEnabled());
	}

	public function testEnableReminders(): void {
		$this->settingService->expects($this->once())
			->method('set')
			->with($this->stringContains('reminder_enabled'), 'true');

		$this->reminderService->enableReminders(true);
	}

	public function testGetReminderIntervals(): void {
		$this->settingService->method('get')
			->will($this->returnValueMap([
				['reminder_interval_level_1', '7'],
				['reminder_interval_level_2', '3'],
				['reminder_interval_level_3', '7'],
			]));

		$intervals = $this->reminderService->getReminderIntervals();

		$this->assertEquals(7, $intervals['level_1']);
		$this->assertEquals(3, $intervals['level_2']);
		$this->assertEquals(7, $intervals['level_3']);
	}

	public function testSetReminderIntervals(): void {
		$this->settingService->expects($this->exactly(3))
			->method('set');

		$this->reminderService->setReminderIntervals(10, 5, 14);
	}

	public function testGetDaysBetweenReminders(): void {
		$this->settingService->method('get')
			->willReturn('3');

		$days = $this->reminderService->getDaysBetweenReminders();
		$this->assertEquals(3, $days);
	}

	public function testSetDaysBetweenReminders(): void {
		$this->settingService->expects($this->once())
			->method('set')
			->with($this->stringContains('reminder_days_between'), '5');

		$this->reminderService->setDaysBetweenReminders(5);
	}
}
