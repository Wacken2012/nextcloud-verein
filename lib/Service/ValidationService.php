<?php
declare(strict_types=1);

namespace OCA\Verein\Service;

class ValidationService {
    /**
     * Validiert ein Mitglied auf Pflichtfelder
     *
     * @param string $name
     * @param string $email
     * @param string|null $iban
     * @return array Mit 'valid' (bool) und 'errors' (array)
     */
    public function validateMember(
        string $name,
        string $email,
        ?string $iban = null
    ): array {
        $errors = [];

        // Name validieren
        if (empty(trim($name))) {
            $errors[] = 'Name ist erforderlich';
        } elseif (strlen($name) < 2) {
            $errors[] = 'Name muss mindestens 2 Zeichen lang sein';
        } elseif (strlen($name) > 255) {
            $errors[] = 'Name darf maximal 255 Zeichen lang sein';
        }

        // Email validieren
        if (empty(trim($email))) {
            $errors[] = 'E-Mail ist erforderlich';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'E-Mail ist ungültig';
        }

        // IBAN validieren (wenn angegeben)
        if (!empty($iban) && !$this->validateIBAN($iban)) {
            $errors[] = 'IBAN ist ungültig (Format: DE89370400440532013000)';
        }

        return [
            'valid' => count($errors) === 0,
            'errors' => $errors,
        ];
    }

    /**
     * Validiert eine Gebühr auf Pflichtfelder
     *
     * @param int $memberId
     * @param float $amount
     * @param string $description
     * @return array Mit 'valid' (bool) und 'errors' (array)
     */
    public function validateFee(
        int $memberId,
        float $amount,
        string $description
    ): array {
        $errors = [];

        // Member ID validieren
        if ($memberId <= 0) {
            $errors[] = 'Gültige Mitglieds-ID erforderlich';
        }

        // Amount validieren
        if ($amount <= 0) {
            $errors[] = 'Betrag muss größer als 0 sein';
        } elseif ($amount > 100000) {
            $errors[] = 'Betrag ist zu hoch (max. 100.000)';
        }

        // Description validieren
        if (empty(trim($description))) {
            $errors[] = 'Beschreibung ist erforderlich';
        } elseif (strlen($description) < 2) {
            $errors[] = 'Beschreibung muss mindestens 2 Zeichen lang sein';
        } elseif (strlen($description) > 500) {
            $errors[] = 'Beschreibung darf maximal 500 Zeichen lang sein';
        }

        return [
            'valid' => count($errors) === 0,
            'errors' => $errors,
        ];
    }

    /**
     * Validiert eine IBAN
     * Basis-Check: DE + 20 Ziffern
     *
     * @param string $iban
     * @return bool
     */
    private function validateIBAN(string $iban): bool {
        // Leerzeichen entfernen und zu Großbuchstaben
        $iban = str_replace(' ', '', strtoupper($iban));

        // Deutsche IBAN: DE + 2 Prüfziffern + 18 Ziffern
        if (!preg_match('/^DE\d{20}$/', $iban)) {
            return false;
        }

        // Optional: IBAN Checksum validieren (Mod-97 Algorithmus)
        return $this->validateIBANChecksum($iban);
    }

    /**
     * Validiert die IBAN Checksum mittels Mod-97 Algorithmus
     *
     * @param string $iban
     * @return bool
     */
    private function validateIBANChecksum(string $iban): bool {
        // IBAN umstrukturieren: BAN + Ländercode + Checksum
        $rearranged = substr($iban, 4) . substr($iban, 0, 4);

        // Buchstaben in Zahlen konvertieren (A=10, B=11, ..., Z=35)
        $numeric = '';
        foreach (str_split($rearranged) as $char) {
            if (is_numeric($char)) {
                $numeric .= $char;
            } else {
                $numeric .= (ord($char) - ord('A') + 10);
            }
        }

        // Mod-97 Check
        return bcmod($numeric, '97') === '1';
    }

    /**
     * Validiert den Status einer Gebühr
     *
     * @param string $status
     * @return bool
     */
    public function validateFeeStatus(string $status): bool {
        $validStatuses = ['open', 'paid', 'overdue', 'cancelled'];
        return in_array($status, $validStatuses, true);
    }

    /**
     * Validiert die Rolle eines Mitglieds
     *
     * @param string $role
     * @return bool
     */
    public function validateRole(string $role): bool {
        $validRoles = ['Mitglied', 'Kassierer', 'Admin'];
        return in_array($role, $validRoles, true);
    }
}
