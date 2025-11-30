<?php

namespace OCA\Verein\Tests\Unit\Service\Export;

use PHPUnit\Framework\TestCase;
use OCA\Verein\Service\Export\CsvExporter;

class CsvExporterTest extends TestCase {
    private CsvExporter $csvExporter;

    protected function setUp(): void {
        parent::setUp();
        $this->csvExporter = new CsvExporter();
    }

    /**
     * Test UTF-8 BOM is present in exported CSV
     */
    public function testUtf8BomPresent(): void {
        $data = [['Name', 'Email']];
        $result = $this->csvExporter->export($data, ['Name', 'Email'], 'test');

        // UTF-8 BOM is 3 bytes: EF BB BF
        $this->assertStringStartsWith("\xEF\xBB\xBF", $result['content']);
    }

    /**
     * Test header row is present and correct
     */
    public function testHeaderRow(): void {
        $headers = ['ID', 'Name', 'Email'];
        $data = [];
        $result = $this->csvExporter->export($data, $headers, 'test');

        // Should contain header row after BOM
        $content = substr($result['content'], 3); // Skip BOM
        $this->assertStringContainsString('ID;Name;Email', $content);
    }

    /**
     * Test semicolon separator is used
     */
    public function testSemicolonSeparator(): void {
        $headers = ['Column1', 'Column2', 'Column3'];
        $data = [['Value1', 'Value2', 'Value3']];
        $result = $this->csvExporter->export($data, $headers, 'test');

        // Check for semicolons in header
        $content = substr($result['content'], 3); // Skip BOM
        $lines = explode("\n", $content);
        $this->assertStringContainsString(';', $lines[0]);
    }

    /**
     * Test data rows are formatted correctly
     */
    public function testDataRows(): void {
        $headers = ['ID', 'Name'];
        $data = [
            ['1', 'Max Mustermann'],
            ['2', 'Anna Schmidt'],
        ];
        $result = $this->csvExporter->export($data, $headers, 'test');

        $content = substr($result['content'], 3); // Skip BOM
        $this->assertStringContainsString('1;Max Mustermann', $content);
        $this->assertStringContainsString('2;Anna Schmidt', $content);
    }

    /**
     * Test special characters are escaped properly
     */
    public function testSpecialCharacterEscaping(): void {
        $headers = ['Name', 'Description'];
        $data = [['Test; Value', 'Text with "quotes"']];
        $result = $this->csvExporter->export($data, $headers, 'test');

        $content = substr($result['content'], 3); // Skip BOM
        // Semicolon in value should be quoted
        $this->assertStringContainsString('"Test; Value"', $content);
        // Quotes should be doubled
        $this->assertStringContainsString('""quotes""', $content);
    }

    /**
     * Test newlines in data are preserved and quoted
     */
    public function testNewlineHandling(): void {
        $headers = ['Notes'];
        $data = [['Line 1
Line 2']];
        $result = $this->csvExporter->export($data, $headers, 'test');

        $content = substr($result['content'], 3); // Skip BOM
        // Newline in field should be preserved and quoted
        $this->assertStringContainsString('"Line 1', $content);
    }

    /**
     * Test filename format
     */
    public function testFilename(): void {
        $result = $this->csvExporter->export([], [], 'members_export');
        $this->assertEquals('members_export.csv', $result['filename']);
    }

    /**
     * Test MIME type is correct
     */
    public function testMimeType(): void {
        $result = $this->csvExporter->export([], [], 'test');
        $this->assertStringContainsString('text/csv', $result['mimeType']);
        $this->assertStringContainsString('charset=utf-8', $result['mimeType']);
    }

    /**
     * Test empty data returns headers only
     */
    public function testEmptyDataWithHeaders(): void {
        $headers = ['ID', 'Name', 'Email'];
        $result = $this->csvExporter->export([], $headers, 'empty');

        $content = substr($result['content'], 3); // Skip BOM
        // Should have only one line (header)
        $lines = array_filter(explode("\n", $content), 'strlen');
        $this->assertEquals(1, count($lines));
        $this->assertStringContainsString('ID;Name;Email', $lines[0]);
    }

    /**
     * Test member data formatting
     */
    public function testFormatMembers(): void {
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

        $formatted = $this->csvExporter->formatMembers($members);

        $this->assertArrayHasKey('headers', $formatted);
        $this->assertArrayHasKey('data', $formatted);
        $this->assertEquals(1, count($formatted['data']));
        $this->assertStringContainsString('Max Mustermann', implode(';', $formatted['data'][0]));
    }

    /**
     * Test fee data formatting
     */
    public function testFormatFees(): void {
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

        $formatted = $this->csvExporter->formatFees($fees);

        $this->assertArrayHasKey('headers', $formatted);
        $this->assertArrayHasKey('data', $formatted);
        $this->assertEquals(1, count($formatted['data']));
        $this->assertStringContainsString('25.50', implode(';', $formatted['data'][0]));
    }

    /**
     * Test unicode/umlauts are preserved
     */
    public function testUnicodePreservation(): void {
        $headers = ['Name', 'City'];
        $data = [['Müller', 'München']];
        $result = $this->csvExporter->export($data, $headers, 'unicode_test');

        $content = $result['content'];
        $this->assertStringContainsString('Müller', $content);
        $this->assertStringContainsString('München', $content);
    }

    /**
     * Test numeric values are converted to strings
     */
    public function testNumericConversion(): void {
        $headers = ['ID', 'Amount'];
        $data = [[123, 45.67]];
        $result = $this->csvExporter->export($data, $headers, 'numeric');

        $content = substr($result['content'], 3); // Skip BOM
        $lines = array_filter(explode("\n", $content), 'strlen');
        $this->assertStringContainsString('123;45.67', $lines[1] ?? '');
    }

    /**
     * Test with real member-like data
     */
    public function testRealMemberExport(): void {
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

        $formatted = $this->csvExporter->formatMembers($members);
        $result = $this->csvExporter->export($formatted['data'], $formatted['headers'], 'members_full');

        $content = substr($result['content'], 3); // Skip BOM
        $this->assertStringContainsString('Alice Meier', $content);
        $this->assertStringContainsString('Bob König', $content);
        $this->assertStringContainsString('DE89370400440532013000', $content);
    }

    /**
     * Test empty member list
     */
    public function testEmptyMemberList(): void {
        $formatted = $this->csvExporter->formatMembers([]);
        $result = $this->csvExporter->export($formatted['data'], $formatted['headers'], 'members_empty');

        $content = substr($result['content'], 3); // Skip BOM
        $lines = array_filter(explode("\n", $content), 'strlen');
        // Should have header only
        $this->assertEquals(1, count($lines));
    }
}
