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
use Psr\Log\LoggerInterface;

/**
 * Export Controller
 * Provides CSV and PDF export endpoints for members and fees
 */
class ExportController extends Controller {
    private MemberService $memberService;
    private FeeService $feeService;
    private CsvExporter $csvExporter;
    private PdfExporter $pdfExporter;
    private LoggerInterface $logger;

    public function __construct(
        string $appName,
        IRequest $request,
        MemberService $memberService,
        FeeService $feeService,
        CsvExporter $csvExporter,
        PdfExporter $pdfExporter,
        LoggerInterface $logger
    ) {
        parent::__construct($appName, $request);
        $this->memberService = $memberService;
        $this->feeService = $feeService;
        $this->csvExporter = $csvExporter;
        $this->pdfExporter = $pdfExporter;
        $this->logger = $logger;
    }

    /**
     * Export members as CSV
     *
     * @NoCSRFRequired
     * @NoAdminRequired
     * @return DataDownloadResponse
     */
    public function exportMembersAsCsv(): DataDownloadResponse {
        try {
            // Get all members
            $members = $this->memberService->findAll();

            // Format and export (works with empty array too)
            $formatted = $this->csvExporter->formatMembers($members);
            $result = $this->csvExporter->export($formatted['data'], $formatted['headers'], 'members');

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
            $this->logger->error('CSV Export Error: ' . $e->getMessage(), ['exception' => $e]);
            return new DataDownloadResponse(
                "Error: " . $e->getMessage(),
                'error.txt',
                'text/plain'
            );
        }
    }

    /**
     * Export members as PDF
     *
     * @NoCSRFRequired
     * @NoAdminRequired
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
            $this->logger->error('PDF Export Error: ' . $e->getMessage(), ['exception' => $e]);
            return new DataDownloadResponse(
                "Error: " . $e->getMessage(),
                'error.txt',
                'text/plain'
            );
        }
    }

    /**
     * Export fees as CSV
     *
     * @NoCSRFRequired
     * @NoAdminRequired
     * @return DataDownloadResponse
     */
    public function exportFeesAsCsv(): DataDownloadResponse {
        try {
            // Get all fees
            $fees = $this->feeService->findAll();

            // Format and export (works with empty array too)
            $formatted = $this->csvExporter->formatFees($fees);
            $result = $this->csvExporter->export($formatted['data'], $formatted['headers'], 'fees');

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
            $this->logger->error('CSV Export Error: ' . $e->getMessage(), ['exception' => $e]);
            return new DataDownloadResponse(
                "Error: " . $e->getMessage(),
                'error.txt',
                'text/plain'
            );
        }
    }

    /**
     * Export fees as PDF
     *
     * @NoCSRFRequired
     * @NoAdminRequired
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
            $this->logger->error('PDF Export Error: ' . $e->getMessage(), ['exception' => $e]);
            return new DataDownloadResponse(
                "Error: " . $e->getMessage(),
                'error.txt',
                'text/plain'
            );
        }
    }
}
