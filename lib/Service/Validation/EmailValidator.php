<?php

namespace OCA\Verein\Service\Validation;

/**
 * Email Validator - RFC 5322 simplified compliance
 */
class EmailValidator {
    /**
     * Validate email address
     *
     * RFC 5322 simplified validation (not fully compliant as full compliance is impractical)
     * Checks:
     * - Basic format: local@domain.tld
     * - Local part: alphanumeric, dots, hyphens, underscores (but not starting/ending with dot)
     * - Domain: valid DNS name structure
     * - Optional MX record check (soft warning, non-blocking)
     *
     * @param string $email
     * @param bool $checkMx Optional MX record check (default: false)
     * @return array with keys: valid (bool), code (string|null), message (string|null), warning (string|null)
     */
    public function validate(string $email, bool $checkMx = false): array {
        $email = trim($email);

        // Basic format check
        if (!$this->isValidFormat($email)) {
            return [
                'valid' => false,
                'code' => 'INVALID_FORMAT',
                'message' => 'Invalid email format',
            ];
        }

        // Optional MX check (soft warning)
        if ($checkMx) {
            [$localPart, $domain] = explode('@', $email, 2);
            if (!$this->hasMxRecord($domain)) {
                return [
                    'valid' => true, // Still valid, but with warning
                    'code' => null,
                    'message' => null,
                    'warning' => 'DOMAIN_UNREACHABLE',
                ];
            }
        }

        return [
            'valid' => true,
            'code' => null,
            'message' => null,
        ];
    }

    /**
     * Check email format using simplified RFC 5322 regex
     */
    private function isValidFormat(string $email): bool {
        // Simplified RFC 5322: local@domain.tld
        // local: alphanumeric + . - _ (no leading/trailing dots)
        // domain: labels separated by dots (alphanumeric + hyphens, no leading/trailing hyphens)
        // TLD: at least 2 characters
        $pattern = '/^[a-zA-Z0-9][a-zA-Z0-9._-]*[a-zA-Z0-9]@[a-zA-Z0-9][a-zA-Z0-9.-]*[a-zA-Z0-9]\.[a-zA-Z]{2,}$/';
        
        // Allow single character local parts (e.g., a@example.com)
        if (preg_match('/^[a-zA-Z0-9]@[a-zA-Z0-9][a-zA-Z0-9.-]*[a-zA-Z0-9]\.[a-zA-Z]{2,}$/', $email)) {
            return true;
        }

        if (!preg_match($pattern, $email)) {
            return false;
        }

        // Additional checks
        [$localPart, $domain] = explode('@', $email, 2);

        // Local part checks
        if (strlen($localPart) > 64) {
            return false; // RFC 5321
        }
        if (strpos($localPart, '..') !== false) {
            return false; // No consecutive dots
        }

        // Domain checks
        if (strlen($domain) > 255) {
            return false;
        }
        if (strpos($domain, '..') !== false) {
            return false;
        }
        if (substr($domain, -1) === '.') {
            return false;
        }
        if (substr($domain, 0, 1) === '.') {
            return false;
        }

        // Check each domain label
        $labels = explode('.', $domain);
        foreach ($labels as $label) {
            if (strlen($label) > 63) {
                return false;
            }
            if (!preg_match('/^[a-zA-Z0-9]([a-zA-Z0-9-]*[a-zA-Z0-9])?$/', $label)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check MX record exists for domain (soft validation)
     */
    private function hasMxRecord(string $domain): bool {
        // Use checkdnsrr if available
        if (function_exists('checkdnsrr')) {
            return checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A');
        }

        // Fallback: assume valid if function not available
        return true;
    }

    /**
     * Sanitize email: trim, lowercase domain, preserve local-part case
     */
    public function sanitize(string $email): string {
        $email = trim($email);
        [$localPart, $domain] = explode('@', $email, 2);
        return $localPart . '@' . strtolower($domain);
    }
}
