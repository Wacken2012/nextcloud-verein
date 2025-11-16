<?php
/**
 * RoleService.php - Multi-Role RBAC Service
 * 
 * Verwaltet Rollen, Permissions und Zuweisungen für Musik- und Sportvereine.
 * Unterstützt 10+ vordefinierte Rollen mit granularen Permissions.
 * 
 * v0.2.0 Feature: Multi-Role RBAC mit GUI
 * 
 * @category Service
 * @package Verein\Service\RBAC
 * @author Stefan Schulz <stefan@example.com>
 * @license AGPL-3.0
 */

namespace OCA\Verein\Service\RBAC;

use OCA\Verein\Exception\ValidationException;
use OCA\Verein\Exception\PermissionDeniedException;
use OCP\IUserSession;

class RoleService {
    
    /**
     * Vordefinierte Rollen für Musikvereine (6 Rollen)
     */
    private const MUSIC_CLUB_ROLES = [
        'admin' => [
            'label' => 'Administrator',
            'description' => 'Vollständiger Zugriff auf alle Funktionen',
            'permissions' => [
                'member.create', 'member.read', 'member.update', 'member.delete',
                'finance.create', 'finance.read', 'finance.update', 'finance.delete',
                'export.sepa', 'export.pdf',
                'role.assign', 'role.delete',
                'settings.manage',
                'audit.view'
            ]
        ],
        'treasurer' => [
            'label' => 'Kassier',
            'description' => 'Finanzverwaltung und Exports',
            'permissions' => [
                'member.read',
                'finance.create', 'finance.read', 'finance.update',
                'export.sepa', 'export.pdf',
                'audit.view'
            ]
        ],
        'musician' => [
            'label' => 'Musiker',
            'description' => 'Lesezugriff auf Mitglieder und Noten',
            'permissions' => [
                'member.read',
                'finance.read',
                'score.read',
                'score.update'
            ]
        ],
        'conductor' => [
            'label' => 'Dirigent',
            'description' => 'Verwaltung von Musikern und Noten',
            'permissions' => [
                'member.read',
                'finance.read',
                'score.create', 'score.read', 'score.update',
                'musician.assign'
            ]
        ],
        'secretary' => [
            'label' => 'Sekretär',
            'description' => 'Mitgliederverwaltung',
            'permissions' => [
                'member.create', 'member.read', 'member.update',
                'finance.read',
                'export.pdf'
            ]
        ],
        'viewer' => [
            'label' => 'Betrachter',
            'description' => 'Schreibgeschützter Zugriff',
            'permissions' => [
                'member.read',
                'finance.read'
            ]
        ]
    ];
    
    /**
     * Vordefinierte Rollen für Sportvereine (4 Rollen)
     */
    private const SPORT_CLUB_ROLES = [
        'admin' => [
            'label' => 'Administrator',
            'description' => 'Vollständiger Zugriff auf alle Funktionen',
            'permissions' => [
                'member.create', 'member.read', 'member.update', 'member.delete',
                'finance.create', 'finance.read', 'finance.update', 'finance.delete',
                'export.sepa', 'export.pdf',
                'role.assign', 'role.delete',
                'settings.manage',
                'audit.view'
            ]
        ],
        'coach' => [
            'label' => 'Trainer',
            'description' => 'Verwaltung von Athleten und Training',
            'permissions' => [
                'member.read', 'member.update',
                'finance.read',
                'athlete.assign',
                'training.create', 'training.read', 'training.update'
            ]
        ],
        'treasurer' => [
            'label' => 'Kassier',
            'description' => 'Finanzverwaltung und Exports',
            'permissions' => [
                'member.read',
                'finance.create', 'finance.read', 'finance.update',
                'export.sepa', 'export.pdf',
                'audit.view'
            ]
        ],
        'member' => [
            'label' => 'Mitglied',
            'description' => 'Lesezugriff auf eigene Daten',
            'permissions' => [
                'member.read_self',
                'finance.read_self'
            ]
        ]
    ];
    
    private IUserSession $userSession;
    
    public function __construct(IUserSession $userSession) {
        $this->userSession = $userSession;
    }
    
    /**
     * Gibt alle Rollen für einen Vereinstyp zurück
     * 
     * @param string $clubType 'music' oder 'sport'
     * @return array Array von Rollen mit Labels und Permissions
     * @throws ValidationException
     */
    public function getRolesForClubType(string $clubType): array {
        $clubType = strtolower($clubType);
        
        if ($clubType === 'music') {
            return self::MUSIC_CLUB_ROLES;
        } elseif ($clubType === 'sport') {
            return self::SPORT_CLUB_ROLES;
        }
        
        throw new ValidationException("Ungültiger Vereinstyp: $clubType");
    }
    
    /**
     * Prüft, ob der aktuelle Nutzer eine bestimmte Permission hat
     * 
     * @param string $permission z.B. 'member.create', 'export.sepa'
     * @return bool
     */
    public function hasPermission(string $permission): bool {
        $user = $this->userSession->getUser();
        
        if ($user === null) {
            return false;
        }
        
        // TODO: DB-Abfrage für Nutzer-Rollen und deren Permissions
        // Placeholder für jetzt
        return true;
    }
    
    /**
     * Validiert eine Permission
     * 
     * @param string $permission
     * @return bool
     */
    public function isValidPermission(string $permission): bool {
        $allPermissions = [
            // Member Permissions
            'member.create', 'member.read', 'member.update', 'member.delete', 'member.read_self',
            // Finance Permissions
            'finance.create', 'finance.read', 'finance.update', 'finance.delete', 'finance.read_self',
            // Export Permissions
            'export.sepa', 'export.pdf',
            // Role Permissions
            'role.assign', 'role.delete',
            // Settings
            'settings.manage',
            // Audit
            'audit.view',
            // Score/Music
            'score.create', 'score.read', 'score.update',
            'musician.assign',
            // Sport
            'athlete.assign', 'training.create', 'training.read', 'training.update'
        ];
        
        return in_array($permission, $allPermissions);
    }
    
    /**
     * Holt die Beschreibung einer Permission
     * 
     * @param string $permission
     * @return string
     */
    public function getPermissionDescription(string $permission): string {
        $descriptions = [
            'member.create' => 'Neue Mitglieder anlegen',
            'member.read' => 'Alle Mitglieder einsehen',
            'member.update' => 'Mitgliederdaten bearbeiten',
            'member.delete' => 'Mitglieder löschen',
            'member.read_self' => 'Nur eigene Daten einsehen',
            'finance.create' => 'Gebühren und Transaktionen anlegen',
            'finance.read' => 'Alle Finanzdaten einsehen',
            'finance.update' => 'Finanzdaten bearbeiten',
            'finance.delete' => 'Finanzdaten löschen',
            'finance.read_self' => 'Nur eigene Finanzinfo einsehen',
            'export.sepa' => 'SEPA XML Export (pain.001)',
            'export.pdf' => 'PDF Export (Rechnungen, Listen)',
            'role.assign' => 'Rollen zuweisen',
            'role.delete' => 'Rollen löschen',
            'settings.manage' => 'App-Einstellungen verwalten',
            'audit.view' => 'Audit-Log einsehen',
            'score.create' => 'Noten anlegen',
            'score.read' => 'Noten einsehen',
            'score.update' => 'Noten bearbeiten',
            'musician.assign' => 'Musiker zuweisen',
            'athlete.assign' => 'Athleten zuweisen',
            'training.create' => 'Training anlegen',
            'training.read' => 'Training einsehen',
            'training.update' => 'Training bearbeiten'
        ];
        
        return $descriptions[$permission] ?? $permission;
    }
}
