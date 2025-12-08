<?php

namespace OCA\Verein\Tests\Unit\Service;

use OCA\Verein\Service\ReminderLevelsService;
use OCA\Verein\Db\ReminderMapper;
use OCA\Verein\Service\ReminderService;
use PHPUnit\Framework\TestCase;
use DateTime;

class ReminderLevelsServiceApplyTest extends TestCase {
    public function testApplyLevelForReminderSendsAndUpdates(): void {
        $reminderMapper = $this->createMock(ReminderMapper::class);
        $reminderService = $this->createMock(ReminderService::class);

        // Create a fake reminder object
        $reminder = new class {
            private $id = 42;
            private $dueDate;
            private $reminderLevel = 1;
            public function __construct() { $this->dueDate = (new DateTime())->sub(new \DateInterval('P15D')); }
            public function getId() { return $this->id; }
            public function getDueDate() { return $this->dueDate; }
            public function getReminderLevel() { return $this->reminderLevel; }
            public function setReminderLevel($v) { $this->reminderLevel = $v; }
            public function setNextReminderDate($d) { /* noop */ }
            public function setStatus($s) { /* noop */ }
        };

        $reminderMapper->method('findById')->willReturn($reminder);

        // Expect sendReminder to be called once and return true
        $reminderService->expects($this->once())
            ->method('sendReminder')
            ->with($reminder)
            ->willReturn(true);

        // Expect update to be called on mapper
        $reminderMapper->expects($this->once())
            ->method('update')
            ->with($reminder);

        $service = new ReminderLevelsService($reminderMapper, $reminderService, null);

        $result = $service->applyLevelForReminder(42);
        $this->assertTrue($result);
    }
}
