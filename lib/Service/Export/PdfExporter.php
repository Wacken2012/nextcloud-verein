<?php

namespace OCA\Verein\Service\Export;

use TCPDF;

/**
 * PDF Exporter using TCPDF
 */
class PdfExporter {
    /**
     * Create TCPDF instance with common settings
     *
     * @return TCPDF
     */
    private function createPdf(): TCPDF {
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Set document properties
        $pdf->SetCreator('Vereins-App');
        $pdf->SetAuthor('Vereins-App');

        // Set margins
        $pdf->SetMargins(15, 25, 15);
        $pdf->SetAutoPageBreak(true, 15);

        // Set font
        $pdf->SetFont('helvetica', '', 10);

        // Add first page
        $pdf->AddPage();

        return $pdf;
    }

    /**
     * Export members as PDF
     *
     * @param array $members Array of member objects/arrays
     * @param string $organizationName Organization name for header
     * @return array with keys: content, filename, mimeType
     */
    public function exportMembers(array $members, string $organizationName = 'Vereins-App'): array {
        $pdf = $this->createPdf();

        // Header
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'Mitgliederliste', 0, 1, 'C');

        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(0, 5, 'Exportiert am: ' . date('d.m.Y H:i:s'), 0, 1, 'R');
        $pdf->Ln(5);

        // Table header
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(200, 220, 255);

        $w = [15, 35, 35, 20, 30, 25, 30];
        $headers = ['ID', 'Name', 'Email', 'Rolle', 'IBAN', 'BIC', 'Erstellt'];

        foreach ($headers as $i => $header) {
            $pdf->Cell($w[$i], 7, $header, 1, 0, 'C', true);
        }
        $pdf->Ln();

        // Table data
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetFillColor(240, 245, 255);

        $fill = false;
        foreach ($members as $member) {
            // Handle both array and object formats
            $id = is_array($member) ? ($member['id'] ?? '') : $member->getId();
            $name = is_array($member) ? ($member['name'] ?? '') : ($member->getFirstname() . ' ' . $member->getLastname());
            $email = is_array($member) ? ($member['email'] ?? '') : $member->getEmail();
            $role = is_array($member) ? ($member['role'] ?? '') : $member->getRole();
            $iban = is_array($member) ? ($member['iban'] ?? '') : $member->getIban();
            $bic = is_array($member) ? ($member['bic'] ?? '') : $member->getBic();
            $createdAt = is_array($member) ? ($member['created_at'] ?? '') : $member->getCreatedAt();

            // Format IBAN/BIC (truncate if too long)
            $iban = strlen((string)$iban) > 20 ? substr((string)$iban, 0, 20) . '...' : (string)$iban;
            $bic = strlen((string)$bic) > 12 ? substr((string)$bic, 0, 12) . '...' : (string)$bic;

            // Format created date
            if ($createdAt instanceof \DateTime) {
                $createdAt = $createdAt->format('d.m.Y');
            } elseif (is_string($createdAt)) {
                try {
                    $createdAt = (new \DateTime($createdAt))->format('d.m.Y');
                } catch (\Exception $e) {
                    $createdAt = (string)$createdAt;
                }
            } else {
                $createdAt = (string)$createdAt;
            }

            $pdf->Cell($w[0], 6, (string)$id, 1, 0, 'C', $fill);
            $pdf->Cell($w[1], 6, substr((string)$name, 0, 25), 1, 0, 'L', $fill);
            $pdf->Cell($w[2], 6, substr((string)$email, 0, 25), 1, 0, 'L', $fill);
            $pdf->Cell($w[3], 6, substr((string)$role, 0, 12), 1, 0, 'C', $fill);
            $pdf->Cell($w[4], 6, (string)$iban, 1, 0, 'L', $fill);
            $pdf->Cell($w[5], 6, (string)$bic, 1, 0, 'L', $fill);
            $pdf->Cell($w[6], 6, (string)$createdAt, 1, 0, 'C', $fill);
            $pdf->Ln();

            $fill = !$fill;
        }

        // Footer with page numbers
        $pdf->SetFont('helvetica', '', 8);
        $pageCount = $pdf->getAliasNbPages();
        $currentPage = $pdf->getAliasNumPage();
        $pdf->Cell(0, 10, 'Seite ' . $currentPage . ' von ' . $pageCount, 0, 0, 'R');

        $content = $pdf->Output('', 'S');

        return [
            'content' => $content,
            'filename' => 'members_' . date('Y-m-d_His') . '.pdf',
            'mimeType' => 'application/pdf',
        ];
    }

    /**
     * Export fees as PDF
     *
     * @param array $fees Array of fee objects/arrays
     * @param string $organizationName Organization name for header
     * @return array with keys: content, filename, mimeType
     */
    public function exportFees(array $fees, string $organizationName = 'Vereins-App'): array {
        $pdf = $this->createPdf();

        // Header
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'Gebührenliste', 0, 1, 'C');

        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(0, 5, 'Exportiert am: ' . date('d.m.Y H:i:s'), 0, 1, 'R');
        $pdf->Ln(5);

        // Table header
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(200, 220, 255);

        $w = [15, 25, 45, 20, 25, 20, 30];
        $headers = ['ID', 'Mit-ID', 'Mitglied', 'Betrag', 'Periode', 'Status', 'Erstellt'];

        foreach ($headers as $i => $header) {
            $pdf->Cell($w[$i], 7, $header, 1, 0, 'C', true);
        }
        $pdf->Ln();

        // Table data
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetFillColor(240, 245, 255);

        $fill = false;
        foreach ($fees as $fee) {
            // Handle both array and object formats
            $id = is_array($fee) ? ($fee['id'] ?? '') : $fee->getId();
            $memberId = is_array($fee) ? ($fee['member_id'] ?? '') : $fee->getMemberId();
            $memberName = is_array($fee) ? ($fee['member_name'] ?? '') : $fee->getMemberName();
            $amount = is_array($fee) ? ($fee['amount'] ?? '') : $fee->getAmount();
            $period = is_array($fee) ? ($fee['period'] ?? '') : $fee->getPeriod();
            $status = is_array($fee) ? ($fee['status'] ?? '') : $fee->getStatus();
            $createdAt = is_array($fee) ? ($fee['created_at'] ?? '') : $fee->getCreatedAt();

            // Format amount
            if (is_numeric($amount)) {
                $amount = number_format((float)$amount, 2, ',', '.');
            } else {
                $amount = (string)$amount;
            }

            // Format created date
            if ($createdAt instanceof \DateTime) {
                $createdAt = $createdAt->format('d.m.Y');
            } elseif (is_string($createdAt)) {
                try {
                    $createdAt = (new \DateTime($createdAt))->format('d.m.Y');
                } catch (\Exception $e) {
                    $createdAt = (string)$createdAt;
                }
            } else {
                $createdAt = (string)$createdAt;
            }

            $pdf->Cell($w[0], 6, (string)$id, 1, 0, 'C', $fill);
            $pdf->Cell($w[1], 6, (string)$memberId, 1, 0, 'C', $fill);
            $pdf->Cell($w[2], 6, substr((string)$memberName, 0, 23), 1, 0, 'L', $fill);
            $pdf->Cell($w[3], 6, (string)$amount . ' €', 1, 0, 'R', $fill);
            $pdf->Cell($w[4], 6, substr((string)$period, 0, 12), 1, 0, 'C', $fill);
            $pdf->Cell($w[5], 6, substr((string)$status, 0, 12), 1, 0, 'C', $fill);
            $pdf->Cell($w[6], 6, (string)$createdAt, 1, 0, 'C', $fill);
            $pdf->Ln();

            $fill = !$fill;
        }

        // Footer with page numbers
        $pdf->SetFont('helvetica', '', 8);
        $pageCount = $pdf->getAliasNbPages();
        $currentPage = $pdf->getAliasNumPage();
        $pdf->Cell(0, 10, 'Seite ' . $currentPage . ' von ' . $pageCount, 0, 0, 'R');

        $content = $pdf->Output('', 'S');

        return [
            'content' => $content,
            'filename' => 'fees_' . date('Y-m-d_His') . '.pdf',
            'mimeType' => 'application/pdf',
        ];
    }
}
