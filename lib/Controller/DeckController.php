<?php
namespace OCA\Verein\Controller;

use OCP\AppFramework\Controller;
use OCP\IRequest;

class DeckController extends Controller {
    public function __construct($AppName, IRequest $request) {
        parent::__construct($AppName, $request);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function index() {
        // TODO: integrate with Deck app
        return ['status' => 'ok', 'source' => 'deck', 'boards' => []];
    }
}
