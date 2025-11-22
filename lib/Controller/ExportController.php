<?php

namespace OCA\Verein\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataDownloadResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCA\Verein\Service\MemberService;
use OCA\Verein\Service\FeeService;
use OCA\Verein\Service\Export\CsvExporter;
use OCA\Verein\Service\Export\PdfExporter;
use OCA\Verein\Attributes\RequirePermission;

/**
 * Export Controller
 * Provides CSV and PDF export endpoints for members and fees
 */
class ExportController extends Controller {
    private MemberService $memberService;
    private FeeService $feeService;
    private CsvExporter $csvExporter;
    private PdfExporter $pdfExporter;

    public function __construct(
        string $appName,
        IRequest $request,
        MemberService $memberService,
        FeeService $feeService,
        CsvExporter $csvExporter,
        PdfExporter $pdfExporter
    ) {
        parent::__construct($appName, $request);
        $this->memberService = $memberService;
        $this->feeService = $feeService;
        $this->csvExporter = $csvExporter;
        $this->pdfExporter = $pdfExporter;
    }

    /**
     * Export members as CSV
     *
     * @NoCSRFRequired
     * @RequirePermission verein.member.export
     * @return DataDownloadResponse
     */
    public function exportMembersAsCsv(): DataDownloadResponse {
        try {
            // Get all members
            $members = $this->memberService->findAll();

            if (empty($members)) {
                // Return empty CSV with headers only
                $formatted = $this->csvExporter->formatMembers([]);
                $result = $this->csvExporter->export($formatted['data'], $formatted['headers'], 'members');
            } else {
                // Format and export
                $formatted = $this->csvExporter->formatMembers($members);
                $result = $this->csvExporter->export($formatted['data'], $formatted['headers'], 'members');
            }

            $response = new DataDownloadResponse(
                $result['content'],
                $result['filename'],
                $result['mimeType']
            );

            // Set Content-Disposition header for proper download
            $response->addHeader(
                'Content-Disposition',
                'attachment; filename="' . $result['filename'] . '"'
            );

            return $response;
        } catch (\Exception $e) {
            return new DataDownloadResponse(
                '',
                '',
                'application/octet-stream'
            );
        }
    }

    /**
     * Export members as PDF
     *
     * @NoCSRFRequired
     * @RequirePermission verein.member.export
     * @return DataDownloadResponse
     */
    public function exportMembersAsPdf(): DataDownloadResponse {
        try {
            // Get all members
            $members = $this->memberService->findAll();

            // Export to PDF
            $result = $this->pdfExporter->exportMembers($members);

            $response = new DataDownloadResponse(
                $result['content'],
                $result['filename'],
                $result['mimeType']
            );

            // Set Content-Disposition header for proper download
            $response->addHeader(
                'Content-Disposition',
                'attachment; filename="' . $result['filename'] . '"'
            );

            return $response;
        } catch (\Exception $e) {
            return new DataDownloadResponse(
                '',
                '',
                'application/octet-stream'
            );
        }
    }

    /**
     * Export fees as CSV
     *
     * @NoCSRFRequired
     * @RequirePermission verein.fee.export
     * @return DataDownloadResponse
     */
    public function exportFeesAsCsv(): DataDownloadResponse {
        try {
            // Get all fees
            $fees = $this->feeService->findAll();

            if (empty($fees)) {
                // Return empty CSV with headers only
                $formatted = $this->csvExporter->formatFees([]);
                $result = $this->csvExporter->export($formatted['data'], $formatted['headers'], 'fees');
            } else {
                // Format and export
                $formatted = $this->csvExporter->formatFees($fees);
                $result = $this->csvExporter->export($formatted['data'], $formatted['headers'], 'fees');
            }

            $response = new DataDownloadResponse(
                $result['content'],
                $result['filename'],
                $result['mimeType']
            );

            // Set Content-Disposition header for proper download
            $response->addHeader(
                'Content-Disposition',
                'attachment; filename="' . $result['filename'] . '"'
            );

            return $response;
        } catch (\Exception $e) {
            return new DataDownloadResponse(
                '',
                '',
                'application/octet-stream'
            );
        }
    }

    /**
     * Export fees as PDF
     *
     * @NoCSRFRequired
     * @RequirePermission verein.fee.export
     * @return DataDownloadResponse
     */
    public function exportFeesAsPdf(): DataDownloadResponse {
        try {
            // Get all fees
            $fees = $this->feeService->findAll();

            // Export to PDF
            $result = $this->pdfExporter->exportFees($fees);

            $response = new DataDownloadResponse(
                $result['content'],
                $result['filename'],
                $result['mimeType']
            );

            // Set Content-Disposition header for proper download
            $response->addHeader(
                'Content-Disposition',
                'attachment; filename="' . $result['filename'] . '"'
            );

            return $response;
        } catch (\Exception $e) {
            return new DataDownloadResponse(
                '',
                '',
                'application/octet-stream'
            );
        }
    }
}
