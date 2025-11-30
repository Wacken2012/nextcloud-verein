<?php
declare(strict_types=1);

namespace OCA\Verein\Tests\Integration\Controller;

use PHPUnit\Framework\TestCase;

final class ExportControllerTest extends TestCase {
    public function testExportEndpointsSkipsWithoutNextcloudHarness(): void {
        if (!class_exists('OCP\\IRequest')) {
            $this->markTestSkipped('Nextcloud test harness not available (OCP interfaces missing).');
        }
        $this->markTestIncomplete('Add controller integration tests when Nextcloud testing harness is available.');
    }
}
