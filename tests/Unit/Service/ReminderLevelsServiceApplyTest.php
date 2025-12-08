<?php

namespace OCA\Verein\Tests\Unit\Service;

use OCA\Verein\Service\ReminderLevelsService;
use OCA\Verein\Db\ReminderMapper;
use OCA\Verein\Service\ReminderService;
use PHPUnit\Framework\TestCase;
use DateTime;

class ReminderLevelsServiceApplyTest extends TestCase {
    public function testApplyLevelForReminderSendsAndUpdates(): void {
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
        // Mock ReminderMapper (disable constructor to avoid DB dependency)
        $updated = false;
        $reminderMapper = $this->getMockBuilder(ReminderMapper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findById','update'])
            ->getMock();

        $reminderMapper->method('findById')->willReturn($reminder);
        $reminderMapper->method('update')->willReturnCallback(function() use (&$updated) { $updated = true; });

        // Expect sendReminder to be called once and return true
        $reminderService->expects($this->once())
            ->method('sendReminder')
            ->with($reminder)
            ->willReturn(true);

        $service = new ReminderLevelsService($reminderMapper, $reminderService, null);

        $result = $service->applyLevelForReminder(42);
        $this->assertTrue($result);
        $this->assertTrue($updated, 'Mapper update should have been called and set flag');
    }
}
