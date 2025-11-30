<?php

namespace OCA\Verein\Service\Validation;

/**
 * IBAN Validator - ISO 13616 compliant
 */
class IbanValidator {
    /**
     * Valid IBAN country lengths (ISO 13616)
     */
    private const IBAN_LENGTHS = [
        'AD' => 24, 'AE' => 23, 'AL' => 28, 'AT' => 20, 'AZ' => 28, 'BA' => 20, 'BE' => 16,
        'BG' => 22, 'BH' => 22, 'BR' => 29, 'BY' => 28, 'CH' => 21, 'CR' => 22, 'CY' => 28,
        'CZ' => 24, 'DE' => 22, 'DK' => 18, 'DO' => 28, 'EE' => 20, 'EG' => 29, 'ES' => 24,
        'FI' => 18, 'FO' => 18, 'FR' => 27, 'GB' => 22, 'GE' => 22, 'GI' => 23, 'GL' => 18,
        'GR' => 27, 'GT' => 28, 'HR' => 21, 'HU' => 28, 'IE' => 22, 'IL' => 23, 'IS' => 26,
        'IT' => 27, 'JO' => 30, 'KW' => 30, 'KZ' => 20, 'LB' => 28, 'LC' => 32, 'LI' => 21,
        'LT' => 20, 'LU' => 20, 'LV' => 21, 'MC' => 27, 'MD' => 24, 'ME' => 22, 'MK' => 19,
        'MR' => 27, 'MT' => 31, 'MU' => 30, 'NL' => 18, 'NO' => 15, 'PK' => 24, 'PL' => 28,
        'PS' => 29, 'PT' => 25, 'QA' => 29, 'RO' => 24, 'RS' => 22, 'SA' => 24, 'SE' => 24,
        'SI' => 19, 'SK' => 24, 'SM' => 27, 'TN' => 24, 'TR' => 26, 'UA' => 29, 'VA' => 22,
        'VG' => 24, 'XK' => 20,
    ];

    /**
     * Validate IBAN format, length, and checksum
     *
     * @param string $iban
     * @return array with keys: valid (bool), code (string|null), message (string|null)
     */
    public function validate(string $iban): array {
        // Normalize: trim, uppercase, remove spaces
        $iban = trim($iban);
        $iban = strtoupper(str_replace(' ', '', $iban));

        // Check basic format
        if (!preg_match('/^[A-Z]{2}[0-9]{2}[A-Z0-9]+$/', $iban)) {
            return [
                'valid' => false,
                'code' => 'INVALID_FORMAT',
                'message' => 'IBAN format invalid (expected: 2 letters + 2 digits + alphanumeric)',
            ];
        }

        // Extract country code
        $countryCode = substr($iban, 0, 2);

        // Check country code validity
        if (!isset(self::IBAN_LENGTHS[$countryCode])) {
            return [
                'valid' => false,
                'code' => 'INVALID_FORMAT',
                'message' => sprintf('Unknown or unsupported country code: %s', $countryCode),
            ];
        }

        if (strlen($iban) !== self::IBAN_LENGTHS[$countryCode]) {
            return [
                'valid' => false,
                'code' => 'INVALID_LENGTH',
                'message' => sprintf(
                    'IBAN length invalid for %s (expected %d, got %d)',
                    $countryCode,
                    self::IBAN_LENGTHS[$countryCode],
                    strlen($iban)
                ),
            ];
        }

        // Validate checksum (Mod-97)
        if (!$this->validateChecksum($iban)) {
            return [
                'valid' => false,
                'code' => 'INVALID_CHECKSUM',
                'message' => 'IBAN checksum validation failed (Mod-97)',
            ];
        }

        return [
            'valid' => true,
            'code' => null,
            'message' => null,
        ];
    }

    /**
     * Validate checksum using Mod-97 algorithm (ISO 7064)
     */
    private function validateChecksum(string $iban): bool {
        // Move country code and check digits to the end
        $iban = substr($iban, 4) . substr($iban, 0, 4);

        // Convert letters to numbers: A=10, B=11, ..., Z=35
        $numeric = '';
        for ($i = 0; $i < strlen($iban); $i++) {
            $char = $iban[$i];
            if (is_numeric($char)) {
                $numeric .= $char;
            } else {
                $numeric .= (ord($char) - ord('A') + 10);
            }
        }

        // Calculate Mod 97 - use bcmod for large numbers (if BC Math available)
        if (function_exists('bcmod')) {
            return \bcmod($numeric, '97') === '1';
        }

        // Fallback implementation using string manipulation for Mod 97
        return $this->mod97($numeric) === 1;
    }

    /**
     * Fallback Mod 97 calculation without BC Math
     */
    private function mod97(string $numeric): int {
        $remainder = 0;
        for ($i = 0; $i < strlen($numeric); $i++) {
            $remainder = ($remainder * 10 + (int)$numeric[$i]) % 97;
        }
        return $remainder;
    }

    /**
     * Sanitize IBAN: trim, uppercase, remove spaces
     */
    public function sanitize(string $iban): string {
        return strtoupper(str_replace(' ', '', trim($iban)));
    }
}
