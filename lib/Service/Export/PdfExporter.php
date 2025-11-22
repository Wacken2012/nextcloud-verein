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
            $id = $member['id'] ?? $member->getId() ?? '';
            $name = $member['name'] ?? ($member->getFirstname() . ' ' . $member->getLastname() ?? '');
            $email = $member['email'] ?? $member->getEmail() ?? '';
            $role = $member['role'] ?? $member->getRole() ?? '';
            $iban = $member['iban'] ?? $member->getIban() ?? '';
            $bic = $member['bic'] ?? $member->getBic() ?? '';
            $createdAt = $member['created_at'] ?? $member->getCreatedAt() ?? '';

            // Format IBAN/BIC (truncate if too long)
            $iban = strlen($iban) > 20 ? substr($iban, 0, 20) . '...' : $iban;
            $bic = strlen($bic) > 12 ? substr($bic, 0, 12) . '...' : $bic;

            // Format created date
            if ($createdAt instanceof \DateTime) {
                $createdAt = $createdAt->format('d.m.Y');
            } elseif (is_string($createdAt)) {
                $createdAt = (new \DateTime($createdAt))->format('d.m.Y');
            }

            $pdf->Cell($w[0], 6, (string)$id, 1, 0, 'C', $fill);
            $pdf->Cell($w[1], 6, substr($name, 0, 25), 1, 0, 'L', $fill);
            $pdf->Cell($w[2], 6, substr($email, 0, 25), 1, 0, 'L', $fill);
            $pdf->Cell($w[3], 6, substr($role, 0, 12), 1, 0, 'C', $fill);
            $pdf->Cell($w[4], 6, $iban, 1, 0, 'L', $fill);
            $pdf->Cell($w[5], 6, $bic, 1, 0, 'L', $fill);
            $pdf->Cell($w[6], 6, $createdAt, 1, 0, 'C', $fill);
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
            $id = $fee['id'] ?? $fee->getId() ?? '';
            $memberId = $fee['member_id'] ?? $fee->getMemberId() ?? '';
            $memberName = $fee['member_name'] ?? ($fee['member']['name'] ?? '');
            $amount = $fee['amount'] ?? $fee->getAmount() ?? '';
            $period = $fee['period'] ?? $fee->getPeriod() ?? '';
            $status = $fee['status'] ?? $fee->getStatus() ?? '';
            $createdAt = $fee['created_at'] ?? $fee->getCreatedAt() ?? '';

            // Format amount
            if (is_numeric($amount)) {
                $amount = number_format((float)$amount, 2, ',', '.');
            }

            // Format created date
            if ($createdAt instanceof \DateTime) {
                $createdAt = $createdAt->format('d.m.Y');
            } elseif (is_string($createdAt)) {
                $createdAt = (new \DateTime($createdAt))->format('d.m.Y');
            }

            $pdf->Cell($w[0], 6, (string)$id, 1, 0, 'C', $fill);
            $pdf->Cell($w[1], 6, (string)$memberId, 1, 0, 'C', $fill);
            $pdf->Cell($w[2], 6, substr($memberName, 0, 23), 1, 0, 'L', $fill);
            $pdf->Cell($w[3], 6, $amount . ' €', 1, 0, 'R', $fill);
            $pdf->Cell($w[4], 6, substr($period, 0, 12), 1, 0, 'C', $fill);
            $pdf->Cell($w[5], 6, substr($status, 0, 12), 1, 0, 'C', $fill);
            $pdf->Cell($w[6], 6, $createdAt, 1, 0, 'C', $fill);
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
