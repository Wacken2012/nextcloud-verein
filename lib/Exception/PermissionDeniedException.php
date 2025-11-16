<?php
/**
 * PermissionDeniedException.php - Permission-Fehler
 * 
 * Wirft bei fehlenden Rechten für Operationen.
 * 
 * @category Exception
 * @package Verein\Exception
 * @license AGPL-3.0
 */

namespace OCA\Verein\Exception;

use Exception;

class PermissionDeniedException extends Exception {
    
    protected $statusCode = 403;
    
    public function __construct(
        string $message = "Berechtigung verweigert",
        int $code = 403,
        Exception $previous = null
    ) {
        $this->statusCode = $code;
        parent::__construct($message, $code, $previous);
    }
    
    public function getStatusCode(): int {
        return $this->statusCode;
    }
}
