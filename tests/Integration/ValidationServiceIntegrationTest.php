<?php

namespace OCA\Verein\Tests\Integration\Validation;

use OCA\Verein\Service\Validation\Sanitizer;
use OCA\Verein\Service\Validation\IbanValidator;
use OCA\Verein\Service\Validation\BicValidator;
use OCA\Verein\Service\Validation\EmailValidator;
use OCA\Verein\Service\Validation\SepaXmlValidator;
use PHPUnit\Framework\TestCase;

class ValidationServiceIntegrationTest extends TestCase {
    private IbanValidator $ibanValidator;
    private BicValidator $bicValidator;
    private EmailValidator $emailValidator;
    private SepaXmlValidator $sepaValidator;
    private Sanitizer $sanitizer;

    protected function setUp(): void {
        parent::setUp();
        $this->ibanValidator = new IbanValidator();
        $this->bicValidator = new BicValidator();
        $this->emailValidator = new EmailValidator();
        $this->sepaValidator = new SepaXmlValidator();
        $this->sanitizer = new Sanitizer();
    }

    /**
     * Test complete member registration flow with validation
     */
    public function testCompleteValidationFlow(): void {
        // Simulate form data
        $formData = [
            'name' => '  John  DOE  ',
            'email' => '  john.DOE@EXAMPLE.COM  ',
            'iban' => '  de89 3704 0044 0532 0130 00  ',
            'bic' => '  deutdedd  ',
            'reference' => '  Payment Reference  ',
        ];

        // Sanitize
        $sanitized = $this->sanitizer->sanitize($formData);

        // Validate each field
        $this->assertTrue($this->ibanValidator->validate($sanitized['iban'])['valid']);
        $this->assertTrue($this->bicValidator->validate($sanitized['bic'])['valid']);
        $this->assertTrue($this->emailValidator->validate($sanitized['email'])['valid']);

        // Check sanitization results
        $this->assertSame('John DOE', $sanitized['name']);
        $this->assertSame('john.DOE@example.com', $sanitized['email']);
        $this->assertSame('Payment Reference', $sanitized['reference']);
    }

    /**
     * Test validation chain with multiple validators
     */
    public function testValidationChain(): void {
        // Valid payment data
        $paymentData = [
            'debtor_iban' => 'DE89370400440532013000',
            'debtor_bic' => 'DEUTDEDD',
            'creditor_iban' => 'FR1420041010050500013M026',
            'creditor_bic' => 'BNPAFRPP',
            'email' => 'treasurer@verein.de',
            'amount' => '125.50',
        ];

        // Sanitize all fields
        $sanitized = $this->sanitizer->sanitize($paymentData);

        // Validate each field
        $debtorIbanResult = $this->ibanValidator->validate($sanitized['debtor_iban']);
        $debtorBicResult = $this->bicValidator->validate($sanitized['debtor_bic']);
        $creditorIbanResult = $this->ibanValidator->validate($sanitized['creditor_iban']);
        $creditorBicResult = $this->bicValidator->validate($sanitized['creditor_bic']);
        $emailResult = $this->emailValidator->validate($sanitized['email']);

        // All should be valid
        $this->assertTrue($debtorIbanResult['valid']);
        $this->assertTrue($debtorBicResult['valid']);
        $this->assertTrue($creditorIbanResult['valid']);
        $this->assertTrue($creditorBicResult['valid']);
        $this->assertTrue($emailResult['valid']);
    }

    /**
     * Test validation with invalid data
     */
    public function testValidationChainWithInvalidData(): void {
        // Invalid payment data
        $paymentData = [
            'debtor_iban' => 'INVALID_IBAN',
            'debtor_bic' => 'INVALID',
            'email' => 'invalid-email',
        ];

        // Sanitize
        $sanitized = $this->sanitizer->sanitize($paymentData);

        // Validate - should all fail
        $debtorIbanResult = $this->ibanValidator->validate($sanitized['debtor_iban']);
        $debtorBicResult = $this->bicValidator->validate($sanitized['debtor_bic']);
        $emailResult = $this->emailValidator->validate($sanitized['email']);

        // All should be invalid
        $this->assertFalse($debtorIbanResult['valid']);
        $this->assertFalse($debtorBicResult['valid']);
        $this->assertFalse($emailResult['valid']);

        // Check error codes
        $this->assertNotNull($debtorIbanResult['code']);
        $this->assertNotNull($debtorBicResult['code']);
        $this->assertNotNull($emailResult['code']);
    }

    /**
     * Test sanitizer with mixed case fields
     */
    public function testSanitizerMixedCase(): void {
        $formData = [
            'Name' => '  marie-louise  ',
            'EMAIL' => '  Marie.Louise@EXAMPLE.COM  ',
            'IBAN' => '  FR14 2004 1010 0505 0001 3M02 6  ',
            'Email_Address' => '  contact@example.org  ',
        ];

        $sanitized = $this->sanitizer->sanitize($formData);

        // Fields should be normalized correctly
        $this->assertSame('marie-louise', $sanitized['Name']);
        $this->assertSame('Marie.Louise@example.com', $sanitized['EMAIL']);
        $this->assertSame('marie-louise', $sanitized['Email_Address']);
    }

    /**
     * Test error handling for SEPA XML
     */
    public function testSepaXmlValidationErrors(): void {
        $invalidXml = '<?xml version="1.0"?><Document><CstmrCdtTrfInitn></CstmrCdtTrfInitn></Document>';
        $result = $this->sepaValidator->validate($invalidXml);

        $this->assertFalse($result['valid']);
        $this->assertNotNull($result['code']);
    }

    /**
     * Test sanitizer with NFKC normalization
     */
    public function testSanitizerNfkcNormalization(): void {
        // Test with composed vs decomposed characters
        $data = [
            'name' => 'Café',  // é as single character
        ];

        $sanitized = $this->sanitizer->sanitize($data);
        // NFKC normalization should convert to compatibility form
        $this->assertSame('Cafe', $sanitized['name']);
    }

    /**
     * Test sanitizer bulk operation
     */
    public function testSanitizerBulkOperation(): void {
        $data = [
            'firstname' => '  John  ',
            'lastname' => '  DOE  ',
            'email' => '  JOHN.DOE@EXAMPLE.COM  ',
            'iban' => '  de89 3704 0044 0532 0130 00  ',
            'bic' => '  deutdedd  ',
            'comment' => '  This is a comment  ',
            'amount' => '  100,50  ',
        ];

        $sanitized = $this->sanitizer->sanitize($data);

        $this->assertSame('John', $sanitized['firstname']);
        $this->assertSame('DOE', $sanitized['lastname']);
        $this->assertSame('JOHN.DOE@example.com', $sanitized['email']);
        $this->assertSame('DE893704004405320130 00', $sanitized['iban']);
        $this->assertSame('DEUTDEDD', $sanitized['bic']);
        $this->assertSame('This is a comment', $sanitized['comment']);
        $this->assertSame('100.50', $sanitized['amount']);
    }

    /**
     * Test validation with whitespace variations
     */
    public function testValidationWithWhitespace(): void {
        // Various whitespace combinations
        $ibans = [
            'DE89 3704 0044 0532 0130 00',
            'DE893704  0044  0532013000',
            'DE89  3704 0044 0532 0130 00  ',
            "\tDE89 3704 0044 0532 0130 00\n",
        ];

        foreach ($ibans as $iban) {
            $result = $this->ibanValidator->validate($iban);
            $this->assertTrue($result['valid'], "IBAN validation should handle whitespace: " . var_export($iban, true));
        }
    }

    /**
     * Test validation result contains all required fields
     */
    public function testValidationResultStructure(): void {
        // IBAN validation
        $ibanResult = $this->ibanValidator->validate('DE89370400440532013000');
        $this->assertArrayHasKey('valid', $ibanResult);
        $this->assertArrayHasKey('code', $ibanResult);
        $this->assertArrayHasKey('message', $ibanResult);

        // BIC validation
        $bicResult = $this->bicValidator->validate('DEUTDEDD');
        $this->assertArrayHasKey('valid', $bicResult);
        $this->assertArrayHasKey('code', $bicResult);
        $this->assertArrayHasKey('message', $bicResult);

        // Email validation
        $emailResult = $this->emailValidator->validate('test@example.com');
        $this->assertArrayHasKey('valid', $emailResult);
        $this->assertArrayHasKey('code', $emailResult);
        $this->assertArrayHasKey('message', $emailResult);

        // SEPA validation
        $sepaResult = $this->sepaValidator->validate('<Document></Document>');
        $this->assertArrayHasKey('valid', $sepaResult);
        $this->assertArrayHasKey('code', $sepaResult);
        $this->assertArrayHasKey('message', $sepaResult);
        $this->assertArrayHasKey('details', $sepaResult);
    }

    /**
     * Test multiple language/encoding in sanitizer
     */
    public function testSanitizerWithMultibyteCharacters(): void {
        $data = [
            'name' => '  Müller  ',
            'comment' => '  Äußerung  ',
        ];

        $sanitized = $this->sanitizer->sanitize($data);

        // Should handle UTF-8 correctly
        $this->assertStringContainsString('M', $sanitized['name']);
        $this->assertStringContainsString('A', $sanitized['comment']);
    }
}
