<?php

namespace OCA\Verein\Service;

/**
 * ValidationService
 * 
 * Zentrale Validierungsdienst für alle Input-Daten
 * Validiert:
 * - Pflichtfelder (Name, Email, IBAN)
 * - Email-Format
 * - IBAN-Format mit Mod-97-Checksum
 * - Datentypen und Längen
 */
class ValidationService {
    
    /**
     * Validiere Member-Daten
     * 
     * @param array $data
     * @return array ['valid' => bool, 'errors' => array]
     */
    public function validateMember(array $data): array {
        $errors = [];
        
        // Pflichtfelder prüfen
        if (empty($data['name']) || !is_string($data['name'])) {
            $errors['name'] = 'Name ist erforderlich und muss ein Text sein';
        } elseif (strlen($data['name']) < 2 || strlen($data['name']) > 255) {
            $errors['name'] = 'Name muss zwischen 2 und 255 Zeichen lang sein';
        }
        
        if (empty($data['email']) || !is_string($data['email'])) {
            $errors['email'] = 'Email ist erforderlich';
        } elseif (!$this->validateEmail($data['email'])) {
            $errors['email'] = 'Email-Adresse hat ungültiges Format';
        }
        
        // Optional: IBAN Validierung
        if (!empty($data['iban'])) {
            if (!$this->validateIBAN($data['iban'])) {
                $errors['iban'] = 'IBAN hat ungültiges Format oder ungültige Checksum';
            }
        }
        
        // Optional: Phone
        if (!empty($data['phone'])) {
            if (!$this->validatePhone($data['phone'])) {
                $errors['phone'] = 'Telefonnummer hat ungültiges Format';
            }
        }
        
        return [
            'valid' => count($errors) === 0,
            'errors' => $errors
        ];
    }
    
    /**
     * Validiere Finance/Fee-Daten
     * 
     * @param array $data
     * @return array ['valid' => bool, 'errors' => array]
     */
    public function validateFinance(array $data): array {
        $errors = [];
        
        if (empty($data['member_id'])) {
            $errors['member_id'] = 'Mitglieder-ID ist erforderlich';
        } elseif (!is_numeric($data['member_id'])) {
            $errors['member_id'] = 'Mitglieder-ID muss numerisch sein';
        }
        
        if (empty($data['amount'])) {
            $errors['amount'] = 'Betrag ist erforderlich';
        } elseif (!is_numeric($data['amount']) || $data['amount'] <= 0) {
            $errors['amount'] = 'Betrag muss eine positive Zahl sein';
        }
        
        if (empty($data['type']) || !is_string($data['type'])) {
            $errors['type'] = 'Gebührentyp ist erforderlich';
        } elseif (!in_array($data['type'], ['monthly', 'annual', 'once'])) {
            $errors['type'] = 'Gebührentyp ungültig (monthly, annual, once)';
        }
        
        if (!empty($data['date']) && !$this->validateDate($data['date'])) {
            $errors['date'] = 'Datum hat ungültiges Format (Y-m-d erwartet)';
        }
        
        return [
            'valid' => count($errors) === 0,
            'errors' => $errors
        ];
    }
    
    /**
     * Validiere Email-Adresse
     * 
     * @param string $email
     * @return bool
     */
    public function validateEmail(string $email): bool {
        // RFC 5322 simplified regex
        $pattern = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';
        if (!preg_match($pattern, $email)) {
            return false;
        }
        
        // Weitere Checks
        $parts = explode('@', $email);
        if (strlen($parts[0]) > 64) {
            return false; // Local part zu lang
        }
        
        return true;
    }
    
    /**
     * Validiere IBAN mit Mod-97-Checksum (ISO 13616)
     * 
     * @param string $iban
     * @return bool
     */
    public function validateIBAN(string $iban): bool {
        // Entferne Leerzeichen und Konvertiere zu Großbuchstaben
        $iban = strtoupper(str_replace(' ', '', $iban));
        
        // Basis-Format prüfen (2 Buchstaben + 2 Ziffern + bis zu 30 alphanumerisch)
        if (!preg_match('/^[A-Z]{2}[0-9]{2}[A-Z0-9]{1,30}$/', $iban)) {
            return false;
        }
        
        // Länder-spezifische Länge prüfen (vereinfacht)
        $iban_lengths = [
            'DE' => 22, 'AT' => 20, 'CH' => 21, 'FR' => 27,
            'IT' => 27, 'ES' => 24, 'NL' => 18, 'BE' => 16,
            'GB' => 22, 'IE' => 22, 'PT' => 25
        ];
        
        $country = substr($iban, 0, 2);
        if (isset($iban_lengths[$country]) && strlen($iban) !== $iban_lengths[$country]) {
            return false;
        }
        
        // Mod-97-Checksum prüfen
        return $this->validateIBANChecksum($iban);
    }
    
    /**
     * Validiere IBAN-Checksum mit Mod-97 Algorithmus
     * 
     * @param string $iban
     * @return bool
     */
    private function validateIBANChecksum(string $iban): bool {
        // Verschiebe die ersten 4 Zeichen ans Ende
        $rearranged = substr($iban, 4) . substr($iban, 0, 4);
        
        // Ersetze Buchstaben mit Zahlen (A=10, B=11, ..., Z=35)
        $numeric = '';
        for ($i = 0; $i < strlen($rearranged); $i++) {
            $char = $rearranged[$i];
            if (is_numeric($char)) {
                $numeric .= $char;
            } else {
                $numeric .= (ord($char) - ord('A') + 10);
            }
        }
        
        // Berechne Mod-97 (brauchen bcmath für große Zahlen)
        if (!extension_loaded('bcmath')) {
            // Fallback für kleinere Zahlen
            return (intval($numeric) % 97) === 1;
        }
        
        return (bcmod($numeric, '97') === '1');
    }
    
    /**
     * Validiere Telefonnummer (einfaches Format)
     * 
     * @param string $phone
     * @return bool
     */
    public function validatePhone(string $phone): bool {
        // Vereinfachte Prüfung: mind. 7 Ziffern, Leerzeichen/Bindestriche erlaubt
        $cleaned = preg_replace('/[^0-9+]/', '', $phone);
        return strlen($cleaned) >= 7 && strlen($cleaned) <= 15;
    }
    
    /**
     * Validiere Datum (Format: Y-m-d)
     * 
     * @param string $date
     * @return bool
     */
    public function validateDate(string $date): bool {
        $pattern = '/^\d{4}-\d{2}-\d{2}$/';
        if (!preg_match($pattern, $date)) {
            return false;
        }
        
        // Prüfe ob Datum gültig ist
        list($year, $month, $day) = explode('-', $date);
        return checkdate((int)$month, (int)$day, (int)$year);
    }
    
    /**
     * Sanitize String Input
     * 
     * @param string $input
     * @return string
     */
    public function sanitizeString(string $input): string {
        return trim(htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
    }
    
    /**
     * Sanitize Integer Input
     * 
     * @param mixed $input
     * @return int|null
     */
    public function sanitizeInteger($input): ?int {
        $filtered = filter_var($input, FILTER_VALIDATE_INT);
        return $filtered !== false ? $filtered : null;
    }
    
    /**
     * Sanitize Float Input
     * 
     * @param mixed $input
     * @return float|null
     */
    public function sanitizeFloat($input): ?float {
        $filtered = filter_var($input, FILTER_VALIDATE_FLOAT);
        return $filtered !== false ? $filtered : null;
    }
    
    /**
     * Get Validation Error Messages
     * 
     * @param array $errors
     * @return string
     */
    public function getErrorMessage(array $errors): string {
        $messages = [];
        foreach ($errors as $field => $error) {
            $messages[] = "[$field] $error";
        }
        return implode(', ', $messages);
    }
}
