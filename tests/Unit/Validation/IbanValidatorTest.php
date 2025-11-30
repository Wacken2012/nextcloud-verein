<?php

namespace OCA\Verein\Tests\Unit\Validation;

use OCA\Verein\Service\Validation\IbanValidator;
use PHPUnit\Framework\TestCase;

class IbanValidatorTest extends TestCase {
    private IbanValidator $validator;

    protected function setUp(): void {
        parent::setUp();
        $this->validator = new IbanValidator();
    }

    /**
     * Test valid IBANs from different countries
     */
    public function testValidIbans(): void {
        $validIbans = [
            'DE89370400440532013000' => 'German IBAN',
            'GB82WEST12345698765432' => 'UK IBAN',
            'IT60X0542811101000000123456' => 'Italian IBAN',
            'ES9121000418450200051332' => 'Spanish IBAN',
            'NL91ABNA0417164300' => 'Dutch IBAN',
            'BE68539007547034' => 'Belgian IBAN',
            'AT611904300234573201' => 'Austrian IBAN',
            'CH9300762011623852957' => 'Swiss IBAN',
            'SE4550000000058398257466' => 'Swedish IBAN',
        ];

        foreach ($validIbans as $iban => $description) {
            $result = $this->validator->validate($iban);
            $this->assertTrue($result['valid'], "Should validate $description: $iban");
            $this->assertNull($result['code'], "Valid IBAN should have no error code: $description");
        }
    }

    /**
     * Test IBAN with spaces (should be normalized)
     */
    public function testValidIbanWithSpaces(): void {
        $result = $this->validator->validate('DE89 3704 0044 0532 0130 00');
        $this->assertTrue($result['valid']);
        $this->assertNull($result['code']);
    }

    /**
     * Test invalid IBAN length
     */
    public function testInvalidIbanLength(): void {
        $result = $this->validator->validate('DE89370400440532013');
        $this->assertFalse($result['valid']);
        $this->assertSame('INVALID_LENGTH', $result['code']);
    }

    /**
     * Test invalid IBAN length - too long
     */
    public function testInvalidIbanLengthTooLong(): void {
        $result = $this->validator->validate('DE893704004405320130001234567890');
        $this->assertFalse($result['valid']);
        $this->assertSame('INVALID_LENGTH', $result['code']);
    }

    /**
     * Test invalid country code in IBAN
     */
    public function testInvalidCountryCode(): void {
        $result = $this->validator->validate('XX893704004405320130000');
        $this->assertFalse($result['valid']);
        $this->assertSame('INVALID_FORMAT', $result['code']);
    }

    /**
     * Test invalid check digits
     */
    public function testInvalidChecksum(): void {
        // Modify check digit from valid IBAN
        $result = $this->validator->validate('DE88370400440532013000');
        $this->assertFalse($result['valid']);
        $this->assertSame('INVALID_CHECKSUM', $result['code']);
    }

    /**
     * Test IBAN with non-alphanumeric characters
     */
    public function testInvalidIbanWithSpecialCharacters(): void {
        $result = $this->validator->validate('DE89-3704-0044-0532-0130-00');
        $this->assertFalse($result['valid']);
        $this->assertSame('INVALID_FORMAT', $result['code']);
    }

    /**
     * Test empty IBAN
     */
    public function testEmptyIban(): void {
        $result = $this->validator->validate('');
        $this->assertFalse($result['valid']);
        $this->assertSame('INVALID_FORMAT', $result['code']);
    }

    /**
     * Test lowercase IBAN (should be normalized to uppercase)
     */
    public function testLowercaseIban(): void {
        $result = $this->validator->validate('de89370400440532013000');
        $this->assertTrue($result['valid']);
    }

    /**
     * Test IBAN with lowercase and spaces
     */
    public function testLowercaseWithSpaces(): void {
        $result = $this->validator->validate('de89 3704 0044 0532 0130 00');
        $this->assertTrue($result['valid']);
    }

    /**
     * Test sanitize method
     */
    public function testSanitize(): void {
        $dirty = '  de89 3704 0044 0532 0130 00  ';
        $clean = $this->validator->sanitize($dirty);
        // Sanitize removes all spaces and uppercases
        $this->assertSame('DE89370400440532013000', $clean);
    }

    /**
     * Test sanitize removes spaces
     */
    public function testSanitizeRemovesSpaces(): void {
        $clean = $this->validator->sanitize('DE89 3704 0044 0532 0130 00');
        // Result: all spaces removed
        $this->assertSame('DE89370400440532013000', $clean);
    }

    /**
     * Test return structure has correct keys
     */
    public function testValidationResultStructure(): void {
        $result = $this->validator->validate('DE89370400440532013000');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('valid', $result);
        $this->assertArrayHasKey('code', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertIsBool($result['valid']);
    }

    /**
     * Test multiple invalid IBANs
     */
    public function testMultipleInvalidIbans(): void {
        $invalidIbans = [
            'INVALID' => 'INVALID_FORMAT',
            '12345678901234567890' => 'INVALID_FORMAT',
            'DE893704004405320130' => 'INVALID_LENGTH',
            'DE88370400440532013000' => 'INVALID_CHECKSUM',
        ];

        foreach ($invalidIbans as $iban => $expectedCode) {
            $result = $this->validator->validate($iban);
            $this->assertFalse($result['valid'], "Should be invalid: $iban");
            $this->assertSame($expectedCode, $result['code'], "Expected code for $iban");
        }
    }

    /**
     * Test IBAN with numbers in country code (invalid)
     */
    public function testIbanWithNumbersInCountryCode(): void {
        $result = $this->validator->validate('D289370400440532013000');
        $this->assertFalse($result['valid']);
        $this->assertSame('INVALID_FORMAT', $result['code']);
    }
}
