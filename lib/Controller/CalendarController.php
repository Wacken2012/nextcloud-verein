<?php
namespace OCA\Verein\Controller;

use OCA\Verein\Attributes\RequirePermission;
use OCP\AppFramework\Controller;
use OCP\IRequest;

class CalendarController extends Controller {
    public function __construct($AppName, IRequest $request) {
        parent::__construct($AppName, $request);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    #[RequirePermission('verein.member.view')]
    public function index() {
        // TODO: integrate with Calendar app (CalDAV)
        return ['status' => 'ok', 'source' => 'calendar', 'events' => []];
    }
}
