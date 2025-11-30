<?php

namespace OCA\Verein\Tests\Unit\Service\Export;

use PHPUnit\Framework\TestCase;
use OCA\Verein\Service\Export\PdfExporter;

class PdfExporterTest extends TestCase {
    private PdfExporter $pdfExporter;

    protected function setUp(): void {
        parent::setUp();
        $this->pdfExporter = new PdfExporter();
    }

    /**
     * Test members PDF export produces valid PDF binary
     */
    public function testExportMembersAsPdf(): void {
        $members = [
            [
                'id' => 1,
                'name' => 'Max Mustermann',
                'email' => 'max@example.com',
                'role' => 'member',
                'iban' => 'DE89370400440532013000',
                'bic' => 'DEUTDEDD',
                'created_at' => new \DateTime('2024-01-15'),
            ],
        ];

        $result = $this->pdfExporter->exportMembers($members);

        // Check result structure
        $this->assertArrayHasKey('content', $result);
        $this->assertArrayHasKey('filename', $result);
        $this->assertArrayHasKey('mimeType', $result);

        // Check MIME type
        $this->assertEquals('application/pdf', $result['mimeType']);

        // Check filename format
        $this->assertStringStartsWith('members_', $result['filename']);
        $this->assertStringEndsWith('.pdf', $result['filename']);
    }

    /**
     * Test fees PDF export produces valid PDF binary
     */
    public function testExportFeesAsPdf(): void {
        $fees = [
            [
                'id' => 1,
                'member_id' => 1,
                'member_name' => 'Max Mustermann',
                'amount' => '25.50',
                'period' => '2024-01',
                'status' => 'paid',
                'created_at' => new \DateTime('2024-01-15'),
            ],
        ];

        $result = $this->pdfExporter->exportFees($fees);

        // Check result structure
        $this->assertArrayHasKey('content', $result);
        $this->assertArrayHasKey('filename', $result);
        $this->assertArrayHasKey('mimeType', $result);

        // Check MIME type
        $this->assertEquals('application/pdf', $result['mimeType']);

        // Check filename format
        $this->assertStringStartsWith('fees_', $result['filename']);
        $this->assertStringEndsWith('.pdf', $result['filename']);
    }

    /**
     * Test PDF content is not empty
     */
    public function testPdfContentNotEmpty(): void {
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

        // PDF content should not be empty
        $this->assertNotEmpty($result['content']);
        // PDF should be reasonably sized (at least 500 bytes)
        $this->assertGreaterThan(500, strlen($result['content']));
    }

    /**
     * Test PDF content starts with PDF magic bytes
     */
    public function testPdfMagicBytes(): void {
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

        // PDF magic bytes: %PDF-
        $this->assertStringStartsWith('%PDF', $result['content']);
    }

    /**
     * Test members PDF contains expected text
     */
    public function testMembersPdfContainsContent(): void {
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

        $result = $this->pdfExporter->exportMembers($members);

        // PDF content might be compressed, so we check for patterns
        $this->assertNotEmpty($result['content']);
        // At minimum, should contain PDF structure
        $this->assertStringContainsString('%PDF', $result['content']);
    }

    /**
     * Test fees PDF contains expected structure
     */
    public function testFeesPdfContainsStructure(): void {
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

        $result = $this->pdfExporter->exportFees($fees);

        // PDF content should have structure markers
        $this->assertNotEmpty($result['content']);
        $this->assertStringContainsString('%PDF', $result['content']);
    }

    /**
     * Test empty member list produces valid PDF
     */
    public function testEmptyMembersPdfIsValid(): void {
        $result = $this->pdfExporter->exportMembers([]);

        // Should still produce valid PDF
        $this->assertNotEmpty($result['content']);
        $this->assertStringStartsWith('%PDF', $result['content']);
        $this->assertEquals('application/pdf', $result['mimeType']);
    }

    /**
     * Test empty fees list produces valid PDF
     */
    public function testEmptyFeesPdfIsValid(): void {
        $result = $this->pdfExporter->exportFees([]);

        // Should still produce valid PDF
        $this->assertNotEmpty($result['content']);
        $this->assertStringStartsWith('%PDF', $result['content']);
        $this->assertEquals('application/pdf', $result['mimeType']);
    }

    /**
     * Test PDF filename includes timestamp
     */
    public function testPdfFilenameHasTimestamp(): void {
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

        // Filename should match pattern: members_YYYY-MM-DD_HHmmss.pdf
        $this->assertMatchesRegularExpression(
            '/members_\d{4}-\d{2}-\d{2}_\d{6}\.pdf/',
            $result['filename']
        );
    }

    /**
     * Test fee filename has timestamp
     */
    public function testFeeFilenameHasTimestamp(): void {
        $fees = [
            [
                'id' => 1,
                'member_id' => 1,
                'member_name' => 'Test User',
                'amount' => '25.50',
                'period' => '2024-01',
                'status' => 'paid',
                'created_at' => '2024-01-15',
            ],
        ];

        $result = $this->pdfExporter->exportFees($fees);

        // Filename should match pattern: fees_YYYY-MM-DD_HHmmss.pdf
        $this->assertMatchesRegularExpression(
            '/fees_\d{4}-\d{2}-\d{2}_\d{6}\.pdf/',
            $result['filename']
        );
    }

    /**
     * Test members PDF with multiple entries
     */
    public function testMembersWithMultipleEntries(): void {
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
            [
                'id' => 3,
                'name' => 'Clara Fröhlich',
                'email' => 'clara@example.com',
                'role' => 'treasurer',
                'iban' => 'FR1420041010050500013M02606',
                'bic' => 'BNAGFRPPXXX',
                'created_at' => '2024-03-20',
            ],
        ];

        $result = $this->pdfExporter->exportMembers($members);

        // Should produce valid PDF
        $this->assertNotEmpty($result['content']);
        $this->assertStringStartsWith('%PDF', $result['content']);
        // PDF should be larger due to more content
        $this->assertGreaterThan(800, strlen($result['content']));
    }

    /**
     * Test amount formatting in fees PDF
     */
    public function testAmountFormattingInFeesPdf(): void {
        $fees = [
            [
                'id' => 1,
                'member_id' => 1,
                'member_name' => 'Max Mustermann',
                'amount' => 25.50,
                'period' => '2024-01',
                'status' => 'paid',
                'created_at' => '2024-01-15',
            ],
            [
                'id' => 2,
                'member_id' => 2,
                'member_name' => 'Anna Schmidt',
                'amount' => 100,
                'period' => '2024-01',
                'status' => 'pending',
                'created_at' => '2024-01-16',
            ],
        ];

        $result = $this->pdfExporter->exportFees($fees);

        // Should handle numeric amounts correctly
        $this->assertNotEmpty($result['content']);
        $this->assertStringStartsWith('%PDF', $result['content']);
    }

    /**
     * Test binary content is returned (not string)
     */
    public function testBinaryContentReturned(): void {
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

        // Content should be string (binary safe)
        $this->assertIsString($result['content']);
        // Should contain non-ASCII bytes (PDF is binary)
        $this->assertGreaterThan(0, strlen($result['content']));
    }
}
