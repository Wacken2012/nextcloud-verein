<?php

namespace OCA\Verein\Tests\Integration;

use PHPUnit\Framework\TestCase;
use OCA\Verein\Service\Export\CsvExporter;
use OCA\Verein\Service\Export\PdfExporter;

class ExportControllerTest extends TestCase {
    private CsvExporter $csvExporter;
    private PdfExporter $pdfExporter;

    protected function setUp(): void {
        parent::setUp();

        $this->csvExporter = new CsvExporter();
        $this->pdfExporter = new PdfExporter();
    }

    /**
     * Test members and fees export with real data
     */
    public function testCompleteExportFlow(): void {
        $members = [
            [
                'id' => 1,
                'name' => 'Max Mustermann',
                'email' => 'max@example.com',
                'role' => 'member',
                'iban' => 'DE89370400440532013000',
                'bic' => 'DEUTDEDD',
                'created_at' => '2024-01-15',
            ],
        ];

        $fees = [
            [
                'id' => 1,
                'member_id' => 1,
                'member_name' => 'Max Mustermann',
                'amount' => '25.50',
                'period' => '2024-01',
                'status' => 'paid',
                'created_at' => '2024-01-15',
            ],
        ];

        // Format and export members
        $formattedMembers = $this->csvExporter->formatMembers($members);
        $csvResult = $this->csvExporter->export($formattedMembers['data'], $formattedMembers['headers'], 'members');

        $this->assertArrayHasKey('content', $csvResult);
        $this->assertArrayHasKey('filename', $csvResult);
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csvResult['content']);

        // Format and export fees
        $formattedFees = $this->csvExporter->formatFees($fees);
        $feesCsvResult = $this->csvExporter->export($formattedFees['data'], $formattedFees['headers'], 'fees');

        $this->assertArrayHasKey('content', $feesCsvResult);
        $this->assertArrayHasKey('filename', $feesCsvResult);
    }

    /**
     * Test PDF members export with real data
     */
    public function testMembersPdfExportFlow(): void {
        $members = [
            [
                'id' => 1,
                'name' => 'Alice Meier',
                'email' => 'alice@example.com',
                'role' => 'admin',
                'iban' => 'DE89370400440532013000',
                'bic' => 'DEUTDEDD',
                'created_at' => new \DateTime('2024-01-10'),
            ],
            [
                'id' => 2,
                'name' => 'Bob König',
                'email' => 'bob@example.com',
                'role' => 'member',
                'iban' => 'IT60X0542811101000000123456',
                'bic' => 'UNCRITITMM',
                'created_at' => new \DateTime('2024-02-15'),
            ],
        ];

        $result = $this->pdfExporter->exportMembers($members);

        $this->assertArrayHasKey('content', $result);
        $this->assertArrayHasKey('filename', $result);
        $this->assertStringStartsWith('%PDF', $result['content']);
        $this->assertGreaterThan(800, strlen($result['content']));
    }

    /**
     * Test PDF fees export with real data
     */
    public function testFeesPdfExportFlow(): void {
        $fees = [
            [
                'id' => 1,
                'member_id' => 1,
                'member_name' => 'Alice Meier',
                'amount' => 25.50,
                'period' => '2024-01',
                'status' => 'paid',
                'created_at' => '2024-01-15',
            ],
            [
                'id' => 2,
                'member_id' => 2,
                'member_name' => 'Bob König',
                'amount' => 100,
                'period' => '2024-01',
                'status' => 'pending',
                'created_at' => '2024-01-16',
            ],
        ];

        $result = $this->pdfExporter->exportFees($fees);

        $this->assertArrayHasKey('content', $result);
        $this->assertArrayHasKey('filename', $result);
        $this->assertStringStartsWith('%PDF', $result['content']);
        $this->assertGreaterThan(800, strlen($result['content']));
    }

    /**
     * Test CSV export with empty data
     */
    public function testCsvExportWithEmptyMembers(): void {
        $formattedMembers = $this->csvExporter->formatMembers([]);
        $result = $this->csvExporter->export($formattedMembers['data'], $formattedMembers['headers'], 'members_empty');

        $this->assertArrayHasKey('content', $result);
        $this->assertStringStartsWith("\xEF\xBB\xBF", $result['content']);
        
        // Should have BOM + headers + newline
        $content = substr($result['content'], 3);
        $lines = array_filter(explode("\n", $content), 'strlen');
        $this->assertEquals(1, count($lines));
    }

    /**
     * Test PDF export with empty data
     */
    public function testPdfExportWithEmptyFees(): void {
        $result = $this->pdfExporter->exportFees([]);

        $this->assertArrayHasKey('content', $result);
        $this->assertStringStartsWith('%PDF', $result['content']);
        $this->assertNotEmpty($result['content']);
    }

    /**
     * Test CSV filename format is correct
     */
    public function testCsvFilenameFormat(): void {
        $members = [
            [
                'id' => 1,
                'name' => 'Test User',
                'email' => 'test@example.com',
                'role' => 'member',
                'iban' => 'DE89370400440532013000',
                'bic' => 'DEUTDEDD',
                'created_at' => '2024-01-15',
            ],
        ];

        $formatted = $this->csvExporter->formatMembers($members);
        $result = $this->csvExporter->export($formatted['data'], $formatted['headers'], 'members_test');

        $this->assertEquals('members_test.csv', $result['filename']);
    }

    /**
     * Test PDF filename format has timestamp
     */
    public function testPdfFilenameFormat(): void {
        $members = [
            [
                'id' => 1,
                'name' => 'Test User',
                'email' => 'test@example.com',
                'role' => 'member',
                'iban' => 'DE89370400440532013000',
                'bic' => 'DEUTDEDD',
                'created_at' => '2024-01-15',
            ],
        ];

        $result = $this->pdfExporter->exportMembers($members);

        $this->assertMatchesRegularExpression(
            '/members_\d{4}-\d{2}-\d{2}_\d{6}\.pdf/',
            $result['filename']
        );
    }

    /**
     * Test CSV MIME type is correct
     */
    public function testCsvMimeType(): void {
        $members = [];
        $formatted = $this->csvExporter->formatMembers($members);
        $result = $this->csvExporter->export($formatted['data'], $formatted['headers'], 'test');

        $this->assertStringContainsString('text/csv', $result['mimeType']);
        $this->assertStringContainsString('charset=utf-8', $result['mimeType']);
    }

    /**
     * Test PDF MIME type is correct
     */
    public function testPdfMimeType(): void {
        $result = $this->pdfExporter->exportMembers([]);

        $this->assertEquals('application/pdf', $result['mimeType']);
    }

    /**
     * Test CSV with special characters (German umlauts)
     */
    public function testCsvWithUnicodeCharacters(): void {
        $members = [
            [
                'id' => 1,
                'name' => 'Müller, München',
                'email' => 'mueller@beispiel.de',
                'role' => 'member',
                'iban' => 'DE89370400440532013000',
                'bic' => 'DEUTDEDD',
                'created_at' => '2024-01-15',
            ],
        ];

        $formatted = $this->csvExporter->formatMembers($members);
        $result = $this->csvExporter->export($formatted['data'], $formatted['headers'], 'unicode_test');

        $this->assertStringContainsString('Müller', $result['content']);
        $this->assertStringContainsString('München', $result['content']);
    }

    /**
     * Test CSV with quoted fields
     */
    public function testCsvWithQuotedFields(): void {
        $headers = ['Name', 'Description'];
        $data = [['Test; Value', 'Text with "quotes"']];

        $result = $this->csvExporter->export($data, $headers, 'quoted_test');

        $content = substr($result['content'], 3);
        // Semicolon should cause quoting
        $this->assertStringContainsString('"Test; Value"', $content);
        // Quotes should be doubled
        $this->assertStringContainsString('""quotes""', $content);
    }

    /**
     * Test large member list export performance
     */
    public function testLargeMemberListCsvExport(): void {
        $members = [];
        for ($i = 1; $i <= 100; $i++) {
            $members[] = [
                'id' => $i,
                'name' => 'Member ' . $i,
                'email' => 'member' . $i . '@example.com',
                'role' => 'member',
                'iban' => 'DE89370400440532013000',
                'bic' => 'DEUTDEDD',
                'created_at' => '2024-01-15',
            ];
        }

        $formatted = $this->csvExporter->formatMembers($members);
        $result = $this->csvExporter->export($formatted['data'], $formatted['headers'], 'large_export');

        $this->assertNotEmpty($result['content']);
        // Should have BOM + 100 data rows + header row
        $content = substr($result['content'], 3);
        $lines = array_filter(explode("\n", $content), 'strlen');
        $this->assertEquals(101, count($lines));
    }

    /**
     * Test fee and member integration export
     */
    public function testIntegratedMemberAndFeeExport(): void {
        $members = [
            [
                'id' => 1,
                'name' => 'Alice Meier',
                'email' => 'alice@example.com',
                'role' => 'admin',
                'iban' => 'DE89370400440532013000',
                'bic' => 'DEUTDEDD',
                'created_at' => '2024-01-10',
            ],
            [
                'id' => 2,
                'name' => 'Bob König',
                'email' => 'bob@example.com',
                'role' => 'member',
                'iban' => 'IT60X0542811101000000123456',
                'bic' => 'UNCRITITMM',
                'created_at' => '2024-02-15',
            ],
        ];

        $fees = [
            [
                'id' => 1,
                'member_id' => 1,
                'member_name' => 'Alice Meier',
                'amount' => '50.00',
                'period' => '2024-01',
                'status' => 'paid',
                'created_at' => '2024-01-15',
            ],
            [
                'id' => 2,
                'member_id' => 2,
                'member_name' => 'Bob König',
                'amount' => '25.50',
                'period' => '2024-01',
                'status' => 'pending',
                'created_at' => '2024-01-16',
            ],
        ];

        // Export both
        $membersFormatted = $this->csvExporter->formatMembers($members);
        $membersCsv = $this->csvExporter->export($membersFormatted['data'], $membersFormatted['headers'], 'members');

        $feesFormatted = $this->csvExporter->formatFees($fees);
        $feesCsv = $this->csvExporter->export($feesFormatted['data'], $feesFormatted['headers'], 'fees');

        $membersPdf = $this->pdfExporter->exportMembers($members);
        $feesPdf = $this->pdfExporter->exportFees($fees);

        // Verify all exports succeeded
        $this->assertNotEmpty($membersCsv['content']);
        $this->assertNotEmpty($feesCsv['content']);
        $this->assertNotEmpty($membersPdf['content']);
        $this->assertNotEmpty($feesPdf['content']);

        // Verify file types
        $this->assertStringEndsWith('.csv', $membersCsv['filename']);
        $this->assertStringEndsWith('.csv', $feesCsv['filename']);
        $this->assertStringEndsWith('.pdf', $membersPdf['filename']);
        $this->assertStringEndsWith('.pdf', $feesPdf['filename']);
    }
}
