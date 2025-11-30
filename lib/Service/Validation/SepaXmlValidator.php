<?php

namespace OCA\Verein\Service\Validation;

use Exception;

/**
 * SEPA XML Validator - pain.001 schema compliance
 */
class SepaXmlValidator {
    /**
     * Path to pain.001 XSD schema (will be bundled in app)
     */
    private string $schemaPath;

    public function __construct() {
        $this->schemaPath = __DIR__ . '/../../resources/schema/pain.001.001.03.xsd';
    }

    /**
     * Validate SEPA XML against pain.001 schema
     *
     * @param string $xmlContent XML content as string
     * @return array with keys: valid (bool), code (string|null), message (string|null), details (array|null)
     */
    public function validate(string $xmlContent): array {
        try {
            $dom = new \DOMDocument();
            $dom->preserveWhiteSpace = false;

            // Load and validate XML structure
            if (!@$dom->loadXML($xmlContent, LIBXML_NONET)) {
                return [
                    'valid' => false,
                    'code' => 'XML_INVALID',
                    'message' => 'XML is not well-formed',
                    'details' => null,
                ];
            }

            // Check for SEPA specific structure
            if (!$this->hasValidSepaStructure($dom)) {
                return [
                    'valid' => false,
                    'code' => 'SEPA_STRUCTURE_INVALID',
                    'message' => 'SEPA XML structure is invalid or missing required elements',
                    'details' => null,
                ];
            }

            // Schema validation if XSD available
            if (file_exists($this->schemaPath)) {
                if (!@$dom->schemaValidate($this->schemaPath)) {
                    return [
                        'valid' => false,
                        'code' => 'SCHEMA_MISMATCH',
                        'message' => 'XML does not validate against pain.001 schema',
                        'details' => null,
                    ];
                }
            }

            // Validate critical fields
            $fieldValidation = $this->validateCriticalFields($dom);
            if (!$fieldValidation['valid']) {
                return $fieldValidation;
            }

            return [
                'valid' => true,
                'code' => null,
                'message' => null,
                'details' => null,
            ];
        } catch (Exception $e) {
            return [
                'valid' => false,
                'code' => 'XML_ERROR',
                'message' => 'Error validating SEPA XML: ' . $e->getMessage(),
                'details' => null,
            ];
        }
    }

    /**
     * Check if XML has valid SEPA pain.001 structure
     */
    private function hasValidSepaStructure(\DOMDocument $dom): bool {
        // Check for Document root
        $root = $dom->documentElement;
        if (!$root || !in_array($root->localName, ['Document', 'CstmrCdtTrfInitn'], true)) {
            return false;
        }

        // Check for CstmrCdtTrfInitn (Customer Credit Transfer Initiation)
        $xpath = new \DOMXPath($dom);
        $initNode = $xpath->query('//CstmrCdtTrfInitn')->item(0);
        if (!$initNode) {
            return false;
        }

        // Check for GrpHdr (Group Header)
        $grpHdr = $xpath->query('.//GrpHdr', $initNode)->item(0);
        if (!$grpHdr) {
            return false;
        }

        // Check for PmtInf (Payment Information)
        $pmtInf = $xpath->query('.//PmtInf', $initNode)->item(0);
        if (!$pmtInf) {
            return false;
        }

        return true;
    }

    /**
     * Validate critical SEPA fields
     */
    private function validateCriticalFields(\DOMDocument $dom): array {
        $xpath = new \DOMXPath($dom);

        // Get the PmtInf first
        $pmtInf = $xpath->query('//PmtInf')->item(0);
        if (!$pmtInf) {
            return [
                'valid' => false,
                'code' => 'FIELD_MISSING',
                'message' => 'Missing Payment Information',
                'details' => ['element' => 'PmtInf'],
            ];
        }

        // Check Debtor (mandatory)
        $debtor = $xpath->query('.//Dbtr', $pmtInf)->item(0);
        if (!$debtor) {
            return [
                'valid' => false,
                'code' => 'FIELD_MISSING',
                'message' => 'Missing Debtor (Dbtr) element',
                'details' => ['element' => 'Dbtr'],
            ];
        }

        // Check Debtor IBAN (mandatory) - in same PmtInf, not under Dbtr
        $debtorIban = $xpath->query('.//DbtrAcct/Id/IBAN', $pmtInf)->item(0);
        if (!$debtorIban || !trim($debtorIban->nodeValue)) {
            return [
                'valid' => false,
                'code' => 'FIELD_MISSING',
                'message' => 'Missing Debtor IBAN',
                'details' => ['element' => 'DbtrAcct/Id/IBAN'],
            ];
        }

        // Check Debtor Agent BIC (optional in some variants but check if present)
        $debtorAgentBic = $xpath->query('.//DbtrAgt/FinInstnId/BIC', $pmtInf)->item(0);
        if ($debtorAgentBic && !trim($debtorAgentBic->nodeValue)) {
            return [
                'valid' => false,
                'code' => 'FIELD_INVALID',
                'message' => 'Debtor Agent BIC is empty',
                'details' => ['element' => 'DbtrAgt/FinInstnId/BIC'],
            ];
        }

        // Check CdtTrfTxInf (Credit Transfer Transaction Information)
        $cdtTrfTxInf = $xpath->query('.//CdtTrfTxInf', $pmtInf)->item(0);
        if (!$cdtTrfTxInf) {
            return [
                'valid' => false,
                'code' => 'FIELD_MISSING',
                'message' => 'Missing Credit Transfer Transaction Information',
                'details' => ['element' => 'CdtTrfTxInf'],
            ];
        }

        // Check Creditor (mandatory)
        $creditor = $xpath->query('.//Cdtr', $cdtTrfTxInf)->item(0);
        if (!$creditor) {
            return [
                'valid' => false,
                'code' => 'FIELD_MISSING',
                'message' => 'Missing Creditor (Cdtr) element',
                'details' => ['element' => 'Cdtr'],
            ];
        }

        // Check Creditor IBAN (mandatory)
        $creditorIban = $xpath->query('.//CdtrAcct/Id/IBAN', $cdtTrfTxInf)->item(0);
        if (!$creditorIban || !trim($creditorIban->nodeValue)) {
            return [
                'valid' => false,
                'code' => 'FIELD_MISSING',
                'message' => 'Missing Creditor IBAN',
                'details' => ['element' => 'CdtrAcct/Id/IBAN'],
            ];
        }

        // Check Amount (mandatory)
        $amount = $xpath->query('.//Amt', $cdtTrfTxInf)->item(0);
        if (!$amount || !trim($amount->nodeValue)) {
            return [
                'valid' => false,
                'code' => 'FIELD_MISSING',
                'message' => 'Missing Amount element',
                'details' => ['element' => 'Amt'],
            ];
        }

        // Validate amount format (should be numeric with max 2 decimals)
        $amountValue = trim($amount->nodeValue);
        if (!preg_match('/^\d+(\.\d{1,2})?$/', $amountValue)) {
            return [
                'valid' => false,
                'code' => 'FIELD_INVALID',
                'message' => 'Amount format invalid (must be numeric with max 2 decimals)',
                'details' => ['element' => 'Amt', 'value' => $amountValue],
            ];
        }

        return [
            'valid' => true,
            'code' => null,
            'message' => null,
            'details' => null,
        ];
    }
}
