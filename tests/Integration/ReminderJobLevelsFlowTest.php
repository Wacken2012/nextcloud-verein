<?php

namespace OCA\Verein\Tests\Integration;

use OCA\Verein\BackgroundJob\ReminderJob;
use OCA\Verein\Service\ReminderService;
use OCA\Verein\Service\ReminderLevelsService;
use OCA\Verein\Db\ReminderMapper;
use OCP\AppFramework\Utility\ITimeFactory;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ReminderJobLevelsFlowTest extends TestCase {
    public function testJobUsesLevelsServiceWhenAvailable(): void {
        // time factory mock
        $timeFactory = $this->createMock(ITimeFactory::class);
        $timeFactory->method('getTime')->willReturn(time());

        // services and mapper mocks
        $reminderService = $this->createMock(ReminderService::class);
        $levelsService = $this->createMock(ReminderLevelsService::class);
        $reminderMapper = $this->getMockBuilder(ReminderMapper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findPending'])
            ->getMock();
        $logger = $this->createMock(LoggerInterface::class);

        // pending reminders (simple objects with getId)
        $rem1 = new class { public function getId() { return 10; } };
        $rem2 = new class { public function getId() { return 11; } };
        $reminderMapper->method('findPending')->willReturn([$rem1, $rem2]);

        $levelsService->expects($this->exactly(2))
            ->method('applyLevelForReminder')
            ->withConsecutive([10], [11]);

        $job = new ReminderJob(
            $timeFactory,
            $reminderService,
            $logger,
            $levelsService,
            $reminderMapper
        );

        $ref = new \ReflectionClass($job);
        $run = $ref->getMethod('run');
        $run->setAccessible(true);
        $run->invoke($job, null);
    }
}
