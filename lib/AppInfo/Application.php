<?php
namespace OCA\Verein\AppInfo;

use OCP\AppFramework\App;

class Application extends App {
    public const APP_ID = 'verein';

    public function __construct() {
        parent::__construct(self::APP_ID);
    }
}
