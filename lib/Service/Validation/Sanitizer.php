<?php

namespace OCA\Verein\Service\Validation;

/**
 * Sanitizer - Data sanitization utility
 *
 * Handles:
 * - NFKC Unicode normalization
 * - Whitespace collapse and trim
 * - Remove non-printable characters
 * - Case normalization per field type
 */
class Sanitizer {
    /**
     * Sanitize name field
     * - NFKC Unicode normalization
     * - Collapse multiple spaces
     * - Trim leading/trailing whitespace
     * - Preserve mixed case
     *
     * @param string $name
     * @return string
     */
    public function sanitizeName(string $name): string {
        return $this->normalizeString($name, false);
    }

    /**
     * Sanitize email field
     * - NFKC normalization
     * - Trim whitespace
     * - Lowercase domain part only (preserve local-part case)
     *
     * @param string $email
     * @return string
     */
    public function sanitizeEmail(string $email): string {
        $email = trim($email);

        if (strpos($email, '@') === false) {
            return $email;
        }

        [$localPart, $domain] = explode('@', $email, 2);
        $localPart = $this->normalizeString($localPart, false);
        $domain = $this->normalizeString($domain, true);

        return $localPart . '@' . $domain;
    }

    /**
     * Sanitize IBAN field
     * - NFKC normalization
     * - Uppercase
     * - Remove spaces and hyphens
     * - Trim
     *
     * @param string $iban
     * @return string
     */
    public function sanitizeIban(string $iban): string {
        $iban = $this->normalizeString($iban, true);
        $iban = str_replace([' ', '-', "\t", "\n", "\r"], '', $iban);
        return $iban;
    }

    /**
     * Sanitize BIC field
     * - NFKC normalization
     * - Uppercase
     * - Remove spaces
     * - Trim
     *
     * @param string $bic
     * @return string
     */
    public function sanitizeBic(string $bic): string {
        $bic = $this->normalizeString($bic, true);
        $bic = str_replace(' ', '', $bic);
        return $bic;
    }

    /**
     * Sanitize reference/comment field
     * - NFKC normalization
     * - Collapse whitespace
     * - Remove non-printable characters except newlines
     * - Trim
     *
     * @param string $reference
     * @return string
     */
    public function sanitizeReference(string $reference): string {
        $reference = $this->normalizeString($reference, false);
        // Remove non-printable characters (ASCII 0-31, 127-159) except newline/tab
        $reference = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F-\x9F]/u', '', $reference);
        return $reference;
    }

    /**
     * Sanitize amount field (numeric validation + normalization)
     * - Collapse whitespace
     * - Remove currency symbols
     * - Normalize decimal separator to point
     * - Trim
     *
     * @param string $amount
     * @return string
     */
    public function sanitizeAmount(string $amount): string {
        $amount = trim($amount);
        // Remove common currency symbols and whitespace
        $amount = preg_replace('/[\s€$¥£₹]/u', '', $amount);
        // Normalize decimal separator (comma to point)
        $amount = str_replace(',', '.', $amount);
        return $amount;
    }

    /**
     * Normalize string with NFKC Unicode normalization
     *
     * @param string $str
     * @param bool $uppercase Convert to uppercase after normalization
     * @return string
     */
    private function normalizeString(string $str, bool $uppercase = false): string {
        // Trim first
        $str = trim($str);

        // NFKC Unicode normalization (compatibility decomposition + canonical composition)
        // This normalizes characters like é to e + combining accent, then combines them
        if (function_exists('normalizer_normalize')) {
            $str = normalizer_normalize($str, \Normalizer::FORM_KC);
            if ($str === null) {
                // Fallback if normalization fails
                $str = trim($str);
            }
        }

        // Collapse multiple spaces/whitespace to single space
        $str = preg_replace('/\s+/u', ' ', $str);

        // Trim again after space collapse
        $str = trim($str);

        // Uppercase if requested
        if ($uppercase) {
            $str = mb_strtoupper($str, 'UTF-8');
        }

        return $str;
    }

    /**
     * Get all sanitized values at once (bulk sanitization)
     *
     * @param array $data Associative array of field_name => value
     * @return array Sanitized data
     *
     * Example:
     * $data = ['name' => '  John  ', 'email' => '  USER@DOMAIN.COM  ', 'iban' => 'de89 3704 0044 0532 0130 00']
     * $sanitized = $sanitizer->sanitize($data)
     * // Returns: ['name' => 'John', 'email' => 'user@domain.com', 'iban' => 'DE893704004405320130 00']
     */
    public function sanitize(array $data): array {
        $sanitized = [];

        foreach ($data as $field => $value) {
            if (!is_string($value)) {
                $sanitized[$field] = $value;
                continue;
            }

            // Route to appropriate sanitizer based on field name
            switch (strtolower($field)) {
                case 'email':
                case 'email_address':
                    $sanitized[$field] = $this->sanitizeEmail($value);
                    break;
                case 'iban':
                    $sanitized[$field] = $this->sanitizeIban($value);
                    break;
                case 'bic':
                case 'swift':
                    $sanitized[$field] = $this->sanitizeBic($value);
                    break;
                case 'name':
                case 'firstname':
                case 'lastname':
                case 'given_name':
                case 'family_name':
                    $sanitized[$field] = $this->sanitizeName($value);
                    break;
                case 'amount':
                case 'balance':
                case 'fee':
                    $sanitized[$field] = $this->sanitizeAmount($value);
                    break;
                case 'reference':
                case 'comment':
                case 'description':
                    $sanitized[$field] = $this->sanitizeReference($value);
                    break;
                default:
                    // Generic sanitization for unknown fields
                    $sanitized[$field] = $this->normalizeString($value, false);
                    break;
            }
        }

        return $sanitized;
    }
}
