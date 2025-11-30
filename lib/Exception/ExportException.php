<?php
/**
 * ExportException.php - Export-Fehler
 * 
 * Wirft bei Fehlern während SEPA/PDF Exports.
 * 
 * @category Exception
 * @package Verein\Exception
 * @license AGPL-3.0
 */

namespace OCA\Verein\Exception;

use Exception;

class ExportException extends Exception {
    
    protected $statusCode = 500;
    
    public function __construct(
        string $message = "Export fehlgeschlagen",
        int $code = 500,
        Exception $previous = null
    ) {
        $this->statusCode = $code;
        parent::__construct($message, $code, $previous);
    }
    
    public function getStatusCode(): int {
        return $this->statusCode;
    }
}
