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
            // Handle both array and object formats
            $id = is_array($member) ? ($member['id'] ?? '') : $member->getId();
            $name = is_array($member) ? ($member['name'] ?? '') : $member->getName();
            $email = is_array($member) ? ($member['email'] ?? '') : $member->getEmail();
            $role = is_array($member) ? ($member['role'] ?? '') : $member->getRole();
            $iban = is_array($member) ? ($member['iban'] ?? '') : $member->getIban();
            $bic = is_array($member) ? ($member['bic'] ?? '') : $member->getBic();
            $createdAt = is_array($member) ? ($member['created_at'] ?? '') : $member->getCreatedAt();

            $data[] = [
                $id ?? '',
                $name ?? '',
                $email ?? '',
                $role ?? '',
                $iban ?? '',
                $bic ?? '',
                $createdAt ?? '',
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
            // Handle both array and object formats
            $id = is_array($fee) ? ($fee['id'] ?? '') : $fee->getId();
            $memberId = is_array($fee) ? ($fee['member_id'] ?? '') : $fee->getMemberId();
            $memberName = is_array($fee) ? ($fee['member_name'] ?? '') : '';
            $amount = is_array($fee) ? ($fee['amount'] ?? '') : $fee->getAmount();
            $period = is_array($fee) ? ($fee['period'] ?? '') : '';
            $status = is_array($fee) ? ($fee['status'] ?? '') : $fee->getStatus();
            $createdAt = is_array($fee) ? ($fee['created_at'] ?? '') : $fee->getCreatedAt();

            $data[] = [
                $id ?? '',
                $memberId ?? '',
                $memberName ?? '',
                $amount ?? '',
                $period ?? '',
                $status ?? '',
                $createdAt ?? '',
            ];
        }

        return [
            'headers' => $headers,
            'data' => $data,
        ];
    }
}
