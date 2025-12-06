<?php

declare(strict_types=1);

namespace OCA\Verein\BackgroundJob;

use OCA\Verein\Service\ReminderService;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\TimedJob;
use Psr\Log\LoggerInterface;

/**
 * Hintergrund-Job für automatische Zahlungserinnerungen.
 * 
 * Dieser Job wird täglich ausgeführt und verarbeitet alle fälligen
 * Mahnungen über den ReminderService.
 * 
 * @package OCA\Verein\BackgroundJob
 */
class ReminderJob extends TimedJob {
    /** @var ReminderService */
    private ReminderService $reminderService;

    /** @var LoggerInterface */
    private LoggerInterface $logger;

    /**
     * @param ITimeFactory $time Zeit-Factory für den Job-Scheduler
     * @param ReminderService $reminderService Service für Mahnungen
     * @param LoggerInterface $logger Logger für Protokollierung
     */
    public function __construct(
        ITimeFactory $time,
        ReminderService $reminderService,
        LoggerInterface $logger
    ) {
        parent::__construct($time);
        
        $this->reminderService = $reminderService;
        $this->logger = $logger;
        
        // Ausführung einmal täglich (86400 Sekunden = 24 Stunden)
        $this->setInterval(86400);
        
        // Zeitbasiert statt Intervall-basiert für konsistentere Ausführung
        $this->setTimeSensitivity(self::TIME_SENSITIVE);
    }

    /**
     * Führt die Mahnungsverarbeitung aus.
     * 
     * Diese Methode wird vom Nextcloud-Cron aufgerufen und delegiert
     * die eigentliche Verarbeitung an den ReminderService.
     *
     * @param mixed $argument Nicht verwendet, aber vom Interface erforderlich
     */
    protected function run($argument): void {
        $this->logger->info('Verein: Starting automatic reminder processing');
        
        try {
            $this->reminderService->processDueReminders();
            $this->logger->info('Verein: Automatic reminder processing completed successfully');
        } catch (\Exception $e) {
            $this->logger->error('Verein: Error during automatic reminder processing', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
