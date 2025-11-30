<?php

namespace OCA\Verein\Tests\Unit\Validation;

use OCA\Verein\Service\Validation\BicValidator;
use PHPUnit\Framework\TestCase;

class BicValidatorTest extends TestCase {
    private BicValidator $validator;

    protected function setUp(): void {
        parent::setUp();
        $this->validator = new BicValidator();
    }

    /**
     * Test valid 8-character BICs (without branch code)
     */
    public function testValidBic8Chars(): void {
        $validBics = [
            'DEUTDEDD' => 'Deutsche Bank',
            'DRESDEFF' => 'Dresdner Bank',
            'COBADEHU' => 'Commerzbank',
            'GENODEFF' => 'Genossenschaftsbank',
        ];

        foreach ($validBics as $bic => $description) {
            $result = $this->validator->validate($bic);
            $this->assertTrue($result['valid'], "Should validate $description: $bic");
            $this->assertNull($result['code'], "Valid BIC should have no error code");
        }
    }

    /**
     * Test valid 11-character BICs (with branch code)
     */
    public function testValidBic11Chars(): void {
        $validBics = [
            'DEUTDEDDXXX' => 'Deutsche Bank with XXX',
            'DRESDEFFXXX' => 'Dresdner Bank with XXX',
            'COBADEHUXXX' => 'Commerzbank with XXX',
            'GENODEFF500' => 'Genossenschaftsbank with branch',
        ];

        foreach ($validBics as $bic => $description) {
            $result = $this->validator->validate($bic);
            $this->assertTrue($result['valid'], "Should validate $description: $bic");
            $this->assertNull($result['code'], "Valid BIC should have no error code");
        }
    }

    /**
     * Test BIC with spaces (should be normalized)
     */
    public function testValidBicWithSpaces(): void {
        $result = $this->validator->validate('DEUT DEDD');
        $this->assertTrue($result['valid']);
    }

    /**
     * Test lowercase BIC (should be normalized)
     */
    public function testLowercaseBic(): void {
        $result = $this->validator->validate('deutdedd');
        $this->assertTrue($result['valid']);
    }

    /**
     * Test BIC with mixed case (should be normalized)
     */
    public function testMixedCaseBic(): void {
        $result = $this->validator->validate('DeUtDeDd');
        $this->assertTrue($result['valid']);
    }

    /**
     * Test invalid BIC - too short
     */
    public function testInvalidBicTooShort(): void {
        $result = $this->validator->validate('DEUTDE');
        $this->assertFalse($result['valid']);
        $this->assertSame('INVALID_LENGTH', $result['code']);
    }

    /**
     * Test invalid BIC - too long
     */
    public function testInvalidBicTooLong(): void {
        $result = $this->validator->validate('DEUTDEDDXXXX');
        $this->assertFalse($result['valid']);
        $this->assertSame('INVALID_LENGTH', $result['code']);
    }

    /**
     * Test invalid BIC - exactly 9 characters
     */
    public function testInvalidBicNineChars(): void {
        $result = $this->validator->validate('DEUTDEDDX');
        $this->assertFalse($result['valid']);
        $this->assertSame('INVALID_LENGTH', $result['code']);
    }

    /**
     * Test invalid BIC - exactly 10 characters
     */
    public function testInvalidBicTenChars(): void {
        $result = $this->validator->validate('DEUTDEDDXX');
        $this->assertFalse($result['valid']);
        $this->assertSame('INVALID_LENGTH', $result['code']);
    }

    /**
     * Test invalid BIC - numbers in bank code
     */
    public function testInvalidBicNumbersInBankCode(): void {
        $result = $this->validator->validate('DEUT1EDD');
        $this->assertFalse($result['valid']);
        $this->assertSame('INVALID_FORMAT', $result['code']);
    }

    /**
     * Test invalid BIC - numbers in country code
     */
    public function testInvalidBicNumbersInCountryCode(): void {
        $result = $this->validator->validate('DEUT1EDD');
        $this->assertFalse($result['valid']);
        $this->assertSame('INVALID_FORMAT', $result['code']);
    }

    /**
     * Test invalid BIC - special characters (8 chars)
     */
    public function testInvalidBicSpecialCharacters(): void {
        $result = $this->validator->validate('DEUT#EDX');  // 8 chars but contains #
        $this->assertFalse($result['valid']);
        $this->assertSame('INVALID_FORMAT', $result['code']);
    }

    /**
     * Test invalid BIC - invalid country code
     */
    public function testInvalidBicInvalidCountryCode(): void {
        $result = $this->validator->validate('DEUTXXDD');
        $this->assertFalse($result['valid']);
        $this->assertSame('INVALID_FORMAT', $result['code']);
    }

    /**
     * Test empty BIC
     */
    public function testEmptyBic(): void {
        $result = $this->validator->validate('');
        $this->assertFalse($result['valid']);
        $this->assertSame('INVALID_LENGTH', $result['code']);
    }

    /**
     * Test sanitize method
     */
    public function testSanitize(): void {
        $dirty = '  deut DEDD  ';
        $clean = $this->validator->sanitize($dirty);
        $this->assertSame('DEUTDEDD', $clean);
    }

    /**
     * Test sanitize removes spaces
     */
    public function testSanitizeRemovesSpaces(): void {
        $clean = $this->validator->sanitize('DEUT DEDD');
        $this->assertSame('DEUTDEDD', $clean);
    }

    /**
     * Test validation result structure
     */
    public function testValidationResultStructure(): void {
        $result = $this->validator->validate('DEUTDEDD');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('valid', $result);
        $this->assertArrayHasKey('code', $result);
        $this->assertArrayHasKey('message', $result);
    }
}
