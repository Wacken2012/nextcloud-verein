<?php

namespace OCA\Verein\Service\Validation;

/**
 * BIC Validator - ISO 9362 compliant
 */
class BicValidator {
    /**
     * Validate BIC format
     *
     * Format: AAAA BB CC DDD (or without branch AAAA BB CC)
     * - First 4 chars: Bank code (letters only)
     * - Next 2 chars: Country code (letters only, ISO 3166-1 alpha-2)
     * - Next 2 chars: Location code (alphanumeric)
     * - Last 3 chars (optional): Branch code (alphanumeric)
     *
     * @param string $bic
     * @return array with keys: valid (bool), code (string|null), message (string|null)
     */
    public function validate(string $bic): array {
        // Normalize: trim, uppercase, remove spaces
        $bic = trim($bic);
        $bic = strtoupper(str_replace(' ', '', $bic));

        // Check length (8 or 11 characters)
        $length = strlen($bic);
        if ($length !== 8 && $length !== 11) {
            return [
                'valid' => false,
                'code' => 'INVALID_LENGTH',
                'message' => sprintf('BIC length invalid (expected 8 or 11, got %d)', $length),
            ];
        }

        // Check format: AAAABBCCXXX or AAAABBCC
        // A = letter (4 chars), B = letter (2 chars), C = alphanumeric (2 chars), X = alphanumeric (3 chars)
        if (!preg_match('/^[A-Z]{4}[A-Z]{2}[A-Z0-9]{2}([A-Z0-9]{3})?$/', $bic)) {
            return [
                'valid' => false,
                'code' => 'INVALID_FORMAT',
                'message' => 'BIC format invalid (expected: 4 letters + 2 letters + 2 alphanumeric + optional 3 alphanumeric)',
            ];
        }

        // Validate country code (positions 5-6)
        $countryCode = substr($bic, 4, 2);
        if (!$this->isValidCountryCode($countryCode)) {
            return [
                'valid' => false,
                'code' => 'INVALID_FORMAT',
                'message' => sprintf('Invalid country code in BIC: %s', $countryCode),
            ];
        }

        return [
            'valid' => true,
            'code' => null,
            'message' => null,
        ];
    }

    /**
     * Check if country code is valid (simplified list of major countries)
     */
    private function isValidCountryCode(string $code): bool {
        // Simplified list - in production, use ISO 3166-1 alpha-2 complete list
        $validCountries = [
            'AD', 'AE', 'AF', 'AG', 'AI', 'AL', 'AM', 'AO', 'AQ', 'AR', 'AS', 'AT',
            'AU', 'AW', 'AX', 'AZ', 'BA', 'BB', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI',
            'BJ', 'BL', 'BM', 'BN', 'BO', 'BQ', 'BR', 'BS', 'BT', 'BV', 'BW', 'BY',
            'BZ', 'CA', 'CC', 'CD', 'CF', 'CG', 'CH', 'CI', 'CK', 'CL', 'CM', 'CN',
            'CO', 'CR', 'CU', 'CV', 'CW', 'CX', 'CY', 'CZ', 'DE', 'DJ', 'DK', 'DM',
            'DO', 'DZ', 'EC', 'EE', 'EG', 'EH', 'ER', 'ES', 'ET', 'FI', 'FJ', 'FK',
            'FM', 'FO', 'FR', 'GA', 'GB', 'GD', 'GE', 'GF', 'GG', 'GH', 'GI', 'GL',
            'GM', 'GN', 'GP', 'GQ', 'GR', 'GS', 'GT', 'GU', 'GW', 'GY', 'HK', 'HM',
            'HN', 'HR', 'HT', 'HU', 'ID', 'IE', 'IL', 'IM', 'IN', 'IO', 'IQ', 'IR',
            'IS', 'IT', 'JE', 'JM', 'JO', 'JP', 'KE', 'KG', 'KH', 'KI', 'KM', 'KN',
            'KP', 'KR', 'KW', 'KY', 'KZ', 'LA', 'LB', 'LC', 'LI', 'LK', 'LR', 'LS',
            'LT', 'LU', 'LV', 'LY', 'MA', 'MC', 'MD', 'ME', 'MF', 'MG', 'MH', 'MK',
            'ML', 'MM', 'MN', 'MO', 'MP', 'MQ', 'MR', 'MS', 'MT', 'MU', 'MV', 'MW',
            'MX', 'MY', 'MZ', 'NA', 'NC', 'NE', 'NF', 'NG', 'NI', 'NL', 'NO', 'NP',
            'NR', 'NU', 'NZ', 'OM', 'PA', 'PE', 'PF', 'PG', 'PH', 'PK', 'PL', 'PM',
            'PN', 'PR', 'PS', 'PT', 'PW', 'PY', 'QA', 'RE', 'RO', 'RS', 'RU', 'RW',
            'SA', 'SB', 'SC', 'SD', 'SE', 'SG', 'SH', 'SI', 'SJ', 'SK', 'SL', 'SM',
            'SN', 'SO', 'SR', 'SS', 'ST', 'SV', 'SX', 'SY', 'SZ', 'TC', 'TD', 'TF',
            'TG', 'TH', 'TJ', 'TK', 'TL', 'TM', 'TN', 'TO', 'TR', 'TT', 'TV', 'TW',
            'TZ', 'UA', 'UG', 'UM', 'US', 'UY', 'UZ', 'VA', 'VC', 'VE', 'VG', 'VI',
            'VN', 'VU', 'WF', 'WS', 'YE', 'YT', 'ZA', 'ZM', 'ZW',
        ];

        return in_array($code, $validCountries, true);
    }

    /**
     * Sanitize BIC: trim, uppercase, remove spaces
     */
    public function sanitize(string $bic): string {
        return strtoupper(str_replace(' ', '', trim($bic)));
    }
}
