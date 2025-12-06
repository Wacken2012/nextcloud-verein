<?php

namespace OCA\Verein\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use OCA\Verein\Service\ReminderService;
use OCP\IConfig;
use Psr\Log\LoggerInterface;

/**
 * Unit tests for ReminderService
 * 
 * Note: These tests create ReminderService without dependencies
 * to test standalone methods that use IConfig
 */
class ReminderServiceTest extends TestCase {
	private ReminderService $reminderService;
	private $config;
	private $logger;

	protected function setUp(): void {
		parent::setUp();

		$this->config = $this->createMock(IConfig::class);
		$this->logger = $this->createMock(LoggerInterface::class);

		// Create service with minimal dependencies (nullable params)
		$this->reminderService = new ReminderService(
			null,  // reminderMapper
			null,  // mailer
			$this->logger,
			null,  // memberService
			$this->config,
			'verein'
		);
	}

	public function testIsEnabledReturnsFalseByDefault(): void {
		$this->config->method('getAppValue')
			->willReturn('0');

		$this->assertFalse($this->reminderService->isEnabled());
	}

	public function testIsEnabledReturnsTrueWhenEnabled(): void {
		$this->config->method('getAppValue')
			->willReturn('1');

		$this->assertTrue($this->reminderService->isEnabled());
	}

	public function testIsEnabledReturnsTrueForStringTrue(): void {
		$this->config->method('getAppValue')
			->willReturn('true');

		$this->assertTrue($this->reminderService->isEnabled());
	}

	public function testEnableReminders(): void {
		$this->config->expects($this->once())
			->method('setAppValue')
			->with('verein', $this->stringContains('reminder_'), '1');

		$this->reminderService->enableReminders(true);
	}

	public function testDisableReminders(): void {
		$this->config->expects($this->once())
			->method('setAppValue')
			->with('verein', $this->stringContains('reminder_'), '0');

		$this->reminderService->enableReminders(false);
	}

	public function testGetReminderIntervals(): void {
		$this->config->method('getAppValue')
			->will($this->returnCallback(function($app, $key, $default) {
				$values = [
					'reminder_interval_level_1' => '7',
					'reminder_interval_level_2' => '3',
					'reminder_interval_level_3' => '7',
				];
				return $values[$key] ?? $default;
			}));

		$intervals = $this->reminderService->getReminderIntervals();

		$this->assertEquals(7, $intervals['level_1']);
		$this->assertEquals(3, $intervals['level_2']);
		$this->assertEquals(7, $intervals['level_3']);
	}

	public function testSetReminderIntervals(): void {
		$this->config->expects($this->exactly(3))
			->method('setAppValue');

		$this->reminderService->setReminderIntervals(10, 5, 14);
	}

	public function testGetDaysBetweenReminders(): void {
		$this->config->method('getAppValue')
			->willReturn('3');

		$days = $this->reminderService->getDaysBetweenReminders();
		$this->assertEquals(3, $days);
	}

	public function testSetDaysBetweenReminders(): void {
		$this->config->expects($this->once())
			->method('setAppValue')
			->with('verein', $this->stringContains('reminder_'), '5');

		$this->reminderService->setDaysBetweenReminders(5);
	}
}
