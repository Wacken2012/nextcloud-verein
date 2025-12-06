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

    protected function setUp(): void {
        parent::setUp();
        
        $this->reminderService = $this->createMock(ReminderService::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->timeFactory = $this->createMock(ITimeFactory::class);
        
        // Mock getTime to return current timestamp
        $this->timeFactory->method('getTime')
            ->willReturn(time());
        
        $this->job = new ReminderJob(
            $this->timeFactory,
            $this->reminderService,
            $this->logger
        );
    }

    /**
     * Test: Job ruft processDueReminders auf
     */
    public function testRunCallsProcessDueReminders(): void {
        $this->reminderService->expects($this->once())
            ->method('processDueReminders');
        
        $this->logger->expects($this->exactly(2))
            ->method('info')
            ->withConsecutive(
                [$this->stringContains('Starting')],
                [$this->stringContains('completed')]
            );
        
        // Verwende Reflection um protected run() aufzurufen
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
        
        $this->reminderService->expects($this->once())
            ->method('processDueReminders')
            ->willThrowException($exception);
        
        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                $this->stringContains('Error'),
                $this->callback(function ($context) {
                    return isset($context['exception']) && 
                           $context['exception'] === 'Test error';
                })
            );
        
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
