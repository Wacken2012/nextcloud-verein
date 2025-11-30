<?php

namespace OCA\Verein\Tests\Unit\Validation;

use OCA\Verein\Service\Validation\EmailValidator;
use PHPUnit\Framework\TestCase;

class EmailValidatorTest extends TestCase {
    private EmailValidator $validator;

    protected function setUp(): void {
        parent::setUp();
        $this->validator = new EmailValidator();
    }

    /**
     * Test valid email addresses
     */
    public function testValidEmails(): void {
        $validEmails = [
            'user@example.com' => 'Simple email',
            'john.doe@example.com' => 'Email with dot',
            'john_doe@example.com' => 'Email with underscore',
            'john-doe@example.com' => 'Email with hyphen',
            'user123@example.com' => 'Email with numbers',
            'user@subdomain.example.com' => 'Email with subdomain',
            'user@example.co.uk' => 'Email with multi-level TLD',
            'a@example.com' => 'Single character local part',
        ];

        foreach ($validEmails as $email => $description) {
            $result = $this->validator->validate($email);
            $this->assertTrue($result['valid'], "Should validate $description: $email");
            $this->assertNull($result['code'], "Valid email should have no error code");
        }
    }

    /**
     * Test email with spaces (should be trimmed)
     */
    public function testEmailWithSpaces(): void {
        $result = $this->validator->validate('  user@example.com  ');
        $this->assertTrue($result['valid']);
    }

    /**
     * Test invalid email - missing @ symbol
     */
    public function testInvalidEmailMissingAt(): void {
        $result = $this->validator->validate('userexample.com');
        $this->assertFalse($result['valid']);
        $this->assertSame('INVALID_FORMAT', $result['code']);
    }

    /**
     * Test invalid email - missing domain
     */
    public function testInvalidEmailMissingDomain(): void {
        $result = $this->validator->validate('user@');
        $this->assertFalse($result['valid']);
        $this->assertSame('INVALID_FORMAT', $result['code']);
    }

    /**
     * Test invalid email - missing local part
     */
    public function testInvalidEmailMissingLocalPart(): void {
        $result = $this->validator->validate('@example.com');
        $this->assertFalse($result['valid']);
        $this->assertSame('INVALID_FORMAT', $result['code']);
    }

    /**
     * Test invalid email - no TLD
     */
    public function testInvalidEmailNoTld(): void {
        $result = $this->validator->validate('user@localhost');
        $this->assertFalse($result['valid']);
        $this->assertSame('INVALID_FORMAT', $result['code']);
    }

    /**
     * Test invalid email - dot at beginning of local part
     */
    public function testInvalidEmailDotAtBeginning(): void {
        $result = $this->validator->validate('.user@example.com');
        $this->assertFalse($result['valid']);
        $this->assertSame('INVALID_FORMAT', $result['code']);
    }

    /**
     * Test invalid email - dot at end of local part
     */
    public function testInvalidEmailDotAtEnd(): void {
        $result = $this->validator->validate('user.@example.com');
        $this->assertFalse($result['valid']);
        $this->assertSame('INVALID_FORMAT', $result['code']);
    }

    /**
     * Test invalid email - consecutive dots in local part
     */
    public function testInvalidEmailConsecutiveDots(): void {
        $result = $this->validator->validate('user..name@example.com');
        $this->assertFalse($result['valid']);
        $this->assertSame('INVALID_FORMAT', $result['code']);
    }

    /**
     * Test invalid email - consecutive dots in domain
     */
    public function testInvalidEmailConsecutiveDotsInDomain(): void {
        $result = $this->validator->validate('user@example..com');
        $this->assertFalse($result['valid']);
        $this->assertSame('INVALID_FORMAT', $result['code']);
    }

    /**
     * Test invalid email - special characters in local part
     */
    public function testInvalidEmailSpecialCharacters(): void {
        $result = $this->validator->validate('user#name@example.com');
        $this->assertFalse($result['valid']);
        $this->assertSame('INVALID_FORMAT', $result['code']);
    }

    /**
     * Test invalid email - space in email
     */
    public function testInvalidEmailWithSpace(): void {
        $result = $this->validator->validate('user name@example.com');
        $this->assertFalse($result['valid']);
        $this->assertSame('INVALID_FORMAT', $result['code']);
    }

    /**
     * Test invalid email - local part too long (>64 chars)
     */
    public function testInvalidEmailLocalPartTooLong(): void {
        $longLocal = str_repeat('a', 65);
        $result = $this->validator->validate($longLocal . '@example.com');
        $this->assertFalse($result['valid']);
        $this->assertSame('INVALID_FORMAT', $result['code']);
    }

    /**
     * Test invalid email - domain too long (>255 chars)
     */
    public function testInvalidEmailDomainTooLong(): void {
        $longDomain = str_repeat('a', 250) . '.com';
        $result = $this->validator->validate('user@' . $longDomain);
        $this->assertFalse($result['valid']);
        $this->assertSame('INVALID_FORMAT', $result['code']);
    }

    /**
     * Test invalid email - domain label too long (>63 chars)
     */
    public function testInvalidEmailDomainLabelTooLong(): void {
        $longLabel = str_repeat('a', 64);
        $result = $this->validator->validate('user@' . $longLabel . '.com');
        $this->assertFalse($result['valid']);
        $this->assertSame('INVALID_FORMAT', $result['code']);
    }

    /**
     * Test invalid email - domain starting with hyphen
     */
    public function testInvalidEmailDomainStartingWithHyphen(): void {
        $result = $this->validator->validate('user@-example.com');
        $this->assertFalse($result['valid']);
        $this->assertSame('INVALID_FORMAT', $result['code']);
    }

    /**
     * Test invalid email - domain ending with hyphen
     */
    public function testInvalidEmailDomainEndingWithHyphen(): void {
        $result = $this->validator->validate('user@example-.com');
        $this->assertFalse($result['valid']);
        $this->assertSame('INVALID_FORMAT', $result['code']);
    }

    /**
     * Test empty email
     */
    public function testEmptyEmail(): void {
        $result = $this->validator->validate('');
        $this->assertFalse($result['valid']);
        $this->assertSame('INVALID_FORMAT', $result['code']);
    }

    /**
     * Test sanitize method
     */
    public function testSanitize(): void {
        $dirty = '  USER@EXAMPLE.COM  ';
        $clean = $this->validator->sanitize($dirty);
        $this->assertSame('USER@example.com', $clean);
    }

    /**
     * Test sanitize preserves local part case
     */
    public function testSanitizePreservesLocalPartCase(): void {
        $clean = $this->validator->sanitize('JohnDoe@Example.COM');
        $this->assertSame('JohnDoe@example.com', $clean);
    }

    /**
     * Test sanitize lowercases domain
     */
    public function testSanitizeLowercasesDomain(): void {
        $clean = $this->validator->sanitize('user@EXAMPLE.COM');
        $this->assertSame('user@example.com', $clean);
    }

    /**
     * Test validation result structure
     */
    public function testValidationResultStructure(): void {
        $result = $this->validator->validate('user@example.com');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('valid', $result);
        $this->assertArrayHasKey('code', $result);
        $this->assertArrayHasKey('message', $result);
    }

    /**
     * Test MX check returns warning (without actual MX lookup)
     */
    public function testMxCheckWithValidFormat(): void {
        // This should still be valid even without actual MX check
        $result = $this->validator->validate('user@example.com', true);
        $this->assertTrue($result['valid']);
    }
}
