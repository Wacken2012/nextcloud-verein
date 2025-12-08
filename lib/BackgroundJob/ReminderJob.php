<?php

declare(strict_types=1);

namespace OCA\Verein\BackgroundJob;

use OCA\Verein\Service\ReminderService;
use OCA\Verein\Service\ReminderLevelsService;
use OCA\Verein\Db\ReminderMapper;
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

    /** @var ReminderLevelsService|null */
    private ?ReminderLevelsService $levelsService;

    /** @var ReminderMapper|null */
    private ?ReminderMapper $reminderMapper;

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
        LoggerInterface $logger,
        ?ReminderLevelsService $levelsService = null,
        ?ReminderMapper $reminderMapper = null
    ) {
        parent::__construct($time);
        
        $this->reminderService = $reminderService;
        $this->levelsService = $levelsService;
        $this->reminderMapper = $reminderMapper;
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
            // If a ReminderLevelsService is available, prefer level-based processing
            if ($this->levelsService && $this->reminderMapper) {
                $pending = $this->reminderMapper->findPending();
                $this->logger->info('Processing ' . count($pending) . ' pending reminders with levels service');
                foreach ($pending as $r) {
                    try {
                        $this->levelsService->applyLevelForReminder((int)$r->getId());
                    } catch (\Exception $e) {
                        $this->logger->error('Error applying reminder level for reminder ' . $r->getId() . ': ' . $e->getMessage());
                    }
                }
                $this->logger->info('Verein: Level-based reminder processing completed');
            } else {
                // fallback to legacy processing
                $this->reminderService->processDueReminders();
                $this->logger->info('Verein: Automatic reminder processing completed successfully');
            }
        } catch (\Exception $e) {
            $this->logger->error('Verein: Error during automatic reminder processing', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
