<?php

namespace OCA\Verein\Service\Export;

use Exception;

/**
 * CSV Exporter - UTF-8 with BOM for Excel compatibility
 */
class CsvExporter {
    /**
     * Export data as CSV
     *
     * @param array $data Array of arrays to export
     * @param array $headers Column headers
     * @param string $filename Filename for download (without extension)
     * @return array with keys: content, filename, mimeType
     */
    public function export(array $data, array $headers, string $filename): array {
        // UTF-8 BOM for Excel compatibility
        $csv = "\xEF\xBB\xBF"; // UTF-8 BOM

        // Add header row
        $csv .= $this->escapeLine($headers);

        // Add data rows
        foreach ($data as $row) {
            $csv .= $this->escapeLine($row);
        }

        return [
            'content' => $csv,
            'filename' => $filename . '.csv',
            'mimeType' => 'text/csv; charset=utf-8',
        ];
    }

    /**
     * Escape CSV line and return with newline
     *
     * @param array $fields
     * @return string
     */
    private function escapeLine(array $fields): string {
        $line = [];

        foreach ($fields as $field) {
            // Convert to string
            $field = (string)$field;

            // Escape quotes by doubling them
            $field = str_replace('"', '""', $field);

            // Wrap in quotes if contains semicolon, newline, or quote
            if (strpos($field, ';') !== false || strpos($field, "\n") !== false || strpos($field, '"') !== false) {
                $field = '"' . $field . '"';
            }

            $line[] = $field;
        }

        // Join with semicolon (common in Europe)
        return implode(';', $line) . "\n";
    }

    /**
     * Format members data for export
     *
     * @param array $members Array of member objects/arrays
     * @return array with headers and formatted data
     */
    public function formatMembers(array $members): array {
        $headers = [
            'ID',
            'Name',
            'Email',
            'Role',
            'IBAN',
            'BIC',
            'Created At',
        ];

        $data = [];
        foreach ($members as $member) {
            $data[] = [
                $member['id'] ?? $member->getId() ?? '',
                $member['name'] ?? $member->getFirstname() . ' ' . $member->getLastname() ?? '',
                $member['email'] ?? $member->getEmail() ?? '',
                $member['role'] ?? $member->getRole() ?? '',
                $member['iban'] ?? $member->getIban() ?? '',
                $member['bic'] ?? $member->getBic() ?? '',
                $member['created_at'] ?? $member->getCreatedAt() ?? '',
            ];
        }

        return [
            'headers' => $headers,
            'data' => $data,
        ];
    }

    /**
     * Format fees data for export
     *
     * @param array $fees Array of fee objects/arrays
     * @return array with headers and formatted data
     */
    public function formatFees(array $fees): array {
        $headers = [
            'ID',
            'Member ID',
            'Member Name',
            'Amount',
            'Period',
            'Status',
            'Created At',
        ];

        $data = [];
        foreach ($fees as $fee) {
            $data[] = [
                $fee['id'] ?? $fee->getId() ?? '',
                $fee['member_id'] ?? $fee->getMemberId() ?? '',
                $fee['member_name'] ?? ($fee['member'] ? $fee['member']['name'] : '') ?? '',
                $fee['amount'] ?? $fee->getAmount() ?? '',
                $fee['period'] ?? $fee->getPeriod() ?? '',
                $fee['status'] ?? $fee->getStatus() ?? '',
                $fee['created_at'] ?? $fee->getCreatedAt() ?? '',
            ];
        }

        return [
            'headers' => $headers,
            'data' => $data,
        ];
    }
}
