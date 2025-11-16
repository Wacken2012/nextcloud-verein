<?php
/**
 * ValidationException.php - Validierungsfehler
 * 
 * Wirft bei ungültigen Eingaben wie IBAN, Email, etc.
 * 
 * @category Exception
 * @package Verein\Exception
 * @license AGPL-3.0
 */

namespace OCA\Verein\Exception;

use Exception;

class ValidationException extends Exception {
    
    protected $statusCode = 400;
    
    public function __construct(
        string $message = "Validierungsfehler",
        int $code = 400,
        Exception $previous = null
    ) {
        $this->statusCode = $code;
        parent::__construct($message, $code, $previous);
    }
    
    public function getStatusCode(): int {
        return $this->statusCode;
    }
}
