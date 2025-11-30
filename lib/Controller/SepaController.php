<?php
namespace OCA\Verein\Controller;

use OCA\Verein\Attributes\RequirePermission;
use OCP\IRequest;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\StreamResponse;
use OCP\AppFramework\ApiController;
use OCA\Verein\Service\SepaService;

/**
 * SEPA Controller for generating SEPA-XML files for direct debit
 */
class SepaController extends ApiController {
    private SepaService $service;

    public function __construct(
        string $appName,
        IRequest $request,
        SepaService $service
    ) {
        parent::__construct($appName, $request);
        $this->service = $service;
    }

    /**
     * @NoAdminRequired
     * 
     * Generate SEPA-XML file for open fees
     * 
     * @param string $creditorName Name of the creditor
     * @param string $creditorIban IBAN of the creditor
     * @param string $creditorBic BIC of the creditor
     * @param string $creditorId Creditor ID for SEPA
     * @return StreamResponse
     */
    #[RequirePermission('verein.sepa.export')]
    public function export(
        string $creditorName,
        string $creditorIban,
        string $creditorBic,
        string $creditorId
    ): StreamResponse {
        $xml = $this->service->generateSepaXml(
            $creditorName,
            $creditorIban,
            $creditorBic,
            $creditorId
        );

        $response = new StreamResponse($xml);
        $response->addHeader('Content-Type', 'application/xml');
        $response->addHeader('Content-Disposition', 'attachment; filename="sepa_export_' . date('Y-m-d') . '.xml"');
        
        return $response;
    }

    /**
     * @NoAdminRequired
     * 
     * Preview SEPA export (without downloading)
     */
    #[RequirePermission('verein.sepa.export')]
    public function preview(
        string $creditorName,
        string $creditorIban,
        string $creditorBic,
        string $creditorId
    ): DataResponse {
        $preview = $this->service->previewSepaExport(
            $creditorName,
            $creditorIban,
            $creditorBic,
            $creditorId
        );

        return new DataResponse($preview);
    }
}
