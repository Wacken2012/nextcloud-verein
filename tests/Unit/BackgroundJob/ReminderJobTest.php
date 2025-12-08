<?php

declare(strict_types=1);

namespace OCA\Verein\Tests\Unit\BackgroundJob;

use OCA\Verein\BackgroundJob\ReminderJob;
use OCA\Verein\Service\ReminderService;
use OCP\AppFramework\Utility\ITimeFactory;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Unit Tests für ReminderJob
 */
class ReminderJobTest extends TestCase {
    private ReminderJob $job;
    private ReminderService $reminderService;
    private LoggerInterface $logger;
    private ITimeFactory $timeFactory;
    private $levelsService;
    private $reminderMapper;

    protected function setUp(): void {
        parent::setUp();
        
        $this->reminderService = $this->createMock(ReminderService::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->timeFactory = $this->createMock(ITimeFactory::class);
        $this->levelsService = $this->createMock(\OCA\Verein\Service\ReminderLevelsService::class);
        $this->reminderMapper = $this->createMock(\OCA\Verein\Db\ReminderMapper::class);
        
        // Mock getTime to return current timestamp
        $this->timeFactory->method('getTime')
            ->willReturn(time());
        
        $this->job = new ReminderJob(
            $this->timeFactory,
            $this->reminderService,
            $this->logger,
            $this->levelsService,
            $this->reminderMapper
        );
    }

    /**
     * Test: Job ruft processDueReminders auf
     */
    public function testRunCallsProcessDueReminders(): void {
        // Prepare pending reminders (two simple objects with getId)
        $rem1 = new class { public function getId() { return 1; } };
        $rem2 = new class { public function getId() { return 2; } };

        $this->reminderMapper->method('findPending')->willReturn([$rem1, $rem2]);

        $this->levelsService->expects($this->exactly(2))
            ->method('applyLevelForReminder')
            ->withConsecutive([1], [2]);

        $this->logger->expects($this->atLeast(1))
            ->method('info');

        // call protected run()
        $reflection = new \ReflectionClass($this->job);
        $method = $reflection->getMethod('run');
        $method->setAccessible(true);
        $method->invoke($this->job, null);
    }

    /**
     * Test: Job protokolliert Fehler bei Exception
     */
    public function testRunLogsErrorOnException(): void {
        $exception = new \Exception('Test error');

        // make levelsService throw when applying
        $this->reminderMapper->method('findPending')->willReturn([new class { public function getId(){ return 5; } }]);
        $this->levelsService->expects($this->once())
            ->method('applyLevelForReminder')
            ->willThrowException($exception);

        $this->logger->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Error'));

        $reflection = new \ReflectionClass($this->job);
        $method = $reflection->getMethod('run');
        $method->setAccessible(true);
        $method->invoke($this->job, null);
    }

    /**
     * Test: Job-Intervall ist auf 24 Stunden gesetzt
     */
    public function testJobIntervalIs24Hours(): void {
        $reflection = new \ReflectionClass($this->job);
        $property = $reflection->getProperty('interval');
        $property->setAccessible(true);
        
        $interval = $property->getValue($this->job);
        
        // 86400 Sekunden = 24 Stunden
        $this->assertEquals(86400, $interval);
    }
}
