<?php
namespace OCA\Verein\Service;

use OCA\Verein\Db\ReminderMapper;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use DateTime;

class ReminderLevelsService {

    public function __construct(
        private ?ReminderMapper $reminderMapper = null,
        private ?ReminderService $reminderService = null,
        private ?LoggerInterface $logger = null,
    ) {
        $this->logger = $this->logger ?? new NullLogger();
    }

    /**
     * Determine the reminder level for an age in days using configured intervals.
     * Returns the highest applicable level (1..N) or null if none applies.
     *
     * @param int $ageDays
     * @param array $intervals Example: ['level_1'=>7,'level_2'=>14,'level_3'=>21]
     * @return int|null
     */
    public function determineLevelForAge(int $ageDays, array $intervals): ?int {
        $applicable = null;
        foreach ($intervals as $key => $days) {
            // keys may be 'level_1'..'level_N'
            if (!is_numeric($days)) {
                continue;
            }
            $level = (int) filter_var($key, FILTER_SANITIZE_NUMBER_INT);
            if ($ageDays >= (int)$days) {
                $applicable = max($applicable ?? 0, $level);
            }
        }
        return $applicable ?: null;
    }

    /**
     * Apply reminder logic for a given reminder id.
     * Ensures idempotency by checking current reminder level.
     * Returns true if an action (send/update) was performed.
     *
     * @param int $reminderId
     * @return bool
     */
    public function applyLevelForReminder(int $reminderId): bool {
        if (!$this->reminderMapper || !$this->reminderService) {
            $this->logger->warning('ReminderMapper or ReminderService missing');
            return false;
        }

        $reminder = $this->reminderMapper->findById($reminderId);
        if (!$reminder) {
            $this->logger->warning('Reminder #' . $reminderId . ' not found');
            return false;
        }

        $dueDate = $reminder->getDueDate();
        if (!$dueDate instanceof DateTime) {
            $this->logger->warning('Reminder #' . $reminderId . ' has no valid due date');
            return false;
        }

        $now = new DateTime();
        $ageDays = (int)$dueDate->diff($now)->days;

        // Use existing reminder service intervals as a first implementation
        $intervals = $this->reminderService->getReminderIntervals();
        $newLevel = $this->determineLevelForAge($ageDays, $intervals);

        if ($newLevel === null) {
            $this->logger->info('No reminder level applicable for reminder #' . $reminderId);
            return false;
        }

        $currentLevel = (int)$reminder->getReminderLevel();
        if ($currentLevel >= $newLevel) {
            $this->logger->info('Reminder #' . $reminderId . ' already at level ' . $currentLevel);
            return false;
        }

        // Send reminder via ReminderService
        $sent = $this->reminderService->sendReminder($reminder);
        if ($sent) {
            // advance level and schedule next reminder
            $reminder->setReminderLevel($newLevel);
            $nextInterval = $this->reminderService->getMaxReminderLevel() >= $newLevel
                ? $this->reminderService->getDaysBetweenReminders()
                : $this->reminderService->getDaysBetweenReminders();

            // compute next reminder date
            $nextDate = (clone $now)->add(new \DateInterval('P' . (int)$nextInterval . 'D'));
            $reminder->setNextReminderDate($nextDate);

            // escalate status if at max
            if ($newLevel >= $this->reminderService->getMaxReminderLevel()) {
                $reminder->setStatus('escalated');
            }

            try {
                $this->reminderMapper->update($reminder);
                $this->logger->info('Reminder #' . $reminderId . ' updated to level ' . $newLevel);
            } catch (\Exception $e) {
                $this->logger->error('Failed to update reminder #' . $reminderId . ': ' . $e->getMessage());
            }
            return true;
        }

        return false;
    }

}
