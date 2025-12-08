<?php

namespace OCA\Verein\Tests\Unit\Service;

use OCA\Verein\Service\ReminderLevelsService;
use OCP\IDBConnection;
use PHPUnit\Framework\TestCase;

class ReminderLevelsServiceTest extends TestCase {
    private ReminderLevelsService $service;

    protected function setUp(): void {
        parent::setUp();

        // Create service with null dependencies where not needed
        $this->service = new ReminderLevelsService(null, null, null);
    }

    public function testDetermineLevelForAge(): void {
        $intervals = [
            'level_1' => 7,
            'level_2' => 14,
            'level_3' => 30,
        ];

        $this->assertEquals(1, $this->service->determineLevelForAge(7, $intervals));
        $this->assertEquals(2, $this->service->determineLevelForAge(14, $intervals));
        $this->assertEquals(3, $this->service->determineLevelForAge(31, $intervals));
        $this->assertNull($this->service->determineLevelForAge(0, $intervals));
    }
}
