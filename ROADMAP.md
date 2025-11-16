# 🗺️ Roadmap – Nextcloud Vereins-App

Öffentliche Roadmap für die Entwicklung der Nextcloud Vereins-App. Status und geplante Features für die nächsten Versionen.

---

## 📊 Version Overview

| Version | Status | Release | Fokus |
|---------|--------|---------|-------|
| **v0.1.0** | ✅ Stable | Nov 2025 | Basis CRUD, MVP |
| **v0.2.0** | 🔄 In Development | Dez 2025 | Multi-Role RBAC, Validierung, Export |
| **v0.3.0** | 🔮 Geplant | Q2 2026 | Automatisierung, Integrationen |
| **v1.0.0** | 🎯 Ziel | Q4 2026 | Production-Ready |

---

## ✅ v0.1.0-alpha (AKTUELL)

**Release**: November 2025

### Features
- [x] Mitgliederverwaltung (CRUD)
- [x] Gebührenverwaltung (CRUD)
- [x] Responsive Vue 3 UI
- [x] Dark Mode
- [x] Basis API

### Known Issues
- [ ] Rollen & Berechtigungen (alle Nutzer = Admin)
- [ ] Keine Datenvalidierung (IBAN, E-Mail)
- [ ] Keine Export-Funktionalität
- [ ] Keine Benachrichtigungen

### Tech Schulden
- Unit Tests fehlen (0% Coverage)
- E2E Tests fehlen
- Dokumentation unvollständig
- Performance nicht optimiert

---

## 🔄 v0.2.0 (December 2025 - January 2026)

### 🎯 Fokus: Rollenbasierte Zugriffskontrolle (RBAC) mit Mehrfachrollen, Validierung & Export

---

### 🔐 1. Rollenmodell (Multi-Role RBAC)

#### Musikverein: Rollen & Permissions

| Rolle | CRUD Mitglieder | Finanzen | Export | Dokumente | Termine | Equipment | Beschreibung |
|-------|-----------------|----------|--------|-----------|---------|-----------|---|
| **Vorstand** | ✅ Volle | ✅ Volle | ✅ Alle | ✅ Volle | ✅ Erstellen | ✅ Verwalten | Mehrere Positionen (Vorsitz, Schriftführer, Schatzmeister, Beirat), Vollzugriff auf alle Daten |
| **Kassierer** | ❌ Lesen | ✅ CRUD | ✅ PDF/SEPA | ❌ Nein | ❌ Nein | ❌ Nein | Finanzen verwalten, SEPA/PDF-Export, Gebühren bearbeiten, Zahlungen nachverfolgen |
| **Dirigent** | ✅ Lesen | ❌ Nein | ✅ Listen | ✅ Lesen | ✅ Planen | ❌ Nein | Mitgliederlisten, Termine/Proben planen, Notencontent verwalten |
| **Notenwart** | ❌ Lesen | ❌ Nein | ❌ Nein | ✅ Volle | ❌ Nein | ❌ Nein | Noten, Partituren, PDF-Sammlungen (Upload, Versioning, Share) |
| **Zeugwart** | ❌ Lesen | ❌ Nein | ❌ Nein | ❌ Nein | ❌ Nein | ✅ Volle | Equipment-Verwaltung (Instrumente, Notenständer, Vereinsmaterial, Inventar) |
| **Mitglied** | ✅ Nur eigene | ❌ Nein | ❌ Nein | ✅ Für Gruppe/Instrument | ✅ Lesen | ❌ Nein | Eigenes Profil, Gruppentermine, Zugriff auf Gruppendokumente |

#### Sportverein: Analoge Rollen

| Rolle | CRUD Mitglieder | Finanzen | Export | Teams/Positionen | Termine | Equipment | Beschreibung |
|-------|-----------------|----------|--------|------------------|---------|-----------|---|
| **Trainer** | ✅ Lesen | ❌ Nein | ✅ Listen | ✅ Volle | ✅ Planen | ❌ Nein | Teammanagement, Trainingsplanung, Spielerlisten, Aufgabenverwaltung |
| **Zeugwart** | ❌ Lesen | ❌ Nein | ❌ Nein | ❌ Lesen | ❌ Nein | ✅ Volle | Equipment-Verwaltung (Trikots, Bälle, Geräte, Schuhe), Inventar |
| **Mitglied** | ✅ Nur eigene | ❌ Nein | ❌ Nein | ✅ Nur Gruppe | ✅ Lesen | ❌ Nein | Eigene Daten, Team-Zuweisungen, Termine |

---

### 📊 2. Datenmodell für Mehrfachrollen

#### Database Schema

```sql
-- Rollen-Defintion
CREATE TABLE roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) UNIQUE,           -- 'vorstand', 'kassierer', 'dirigent', 'notenwart', 'zeugwart'
    description VARCHAR(255),
    permissions JSON,                   -- JSON Array: ['members:read', 'finance:write', ...]
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Join-Tabelle: User ↔ Roles (Mehrfachrollen!)
CREATE TABLE user_roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,              -- Nextcloud User ID
    role_id INT NOT NULL,              -- FK zu roles.id
    granted_at TIMESTAMP,
    granted_by INT,                    -- Admin, der die Rolle vergeben hat
    UNIQUE(user_id, role_id),
    FOREIGN KEY(role_id) REFERENCES roles(id),
    INDEX(user_id, role_id)
);

-- Beispiel: Ein Nutzer kann mehrere Rollen haben
INSERT INTO user_roles (user_id, role_id) VALUES 
    (5, 1),  -- User 5 = Vorstand
    (5, 3);  -- User 5 = AUCH Kassierer
```

#### Backend: PHP Enum für Rollen

```php
// appinfo/Enums/Role.php
namespace OCA\Verein\Enums;

enum Role: string {
    case VORSTAND = 'vorstand';
    case KASSIERER = 'kassierer';
    case DIRIGENT = 'dirigent';
    case NOTENWART = 'notenwart';
    case ZEUGWART = 'zeugwart';
    case MITGLIED = 'mitglied';
    
    public function permissions(): array {
        return match($this) {
            self::VORSTAND => ['members:*', 'finance:*', 'export:*', 'documents:*', 'events:*', 'equipment:*'],
            self::KASSIERER => ['finance:read', 'finance:write', 'export:pdf', 'export:sepa'],
            self::DIRIGENT => ['members:read', 'events:*', 'documents:read'],
            self::NOTENWART => ['documents:*'],
            self::ZEUGWART => ['equipment:*'],
            self::MITGLIED => ['self:read', 'events:read', 'documents:read'],
        };
    }
}
```

#### Backend: Permission Checking (Middleware)

```php
// Service/PermissionService.php
class PermissionService {
    
    /**
     * Prüfe, ob Nutzer MINDESTENS EINE Rolle hat, die die Action erlaubt
     * @param string $userId
     * @param string $action (z.B. 'finance:write', 'equipment:*')
     * @return bool
     */
    public function hasPermission(string $userId, string $action): bool {
        // 1. Hole ALLE Rollen des Users
        $roles = $this->getUserRoles($userId);
        
        // 2. Für jede Rolle: Prüfe Permissions
        foreach ($roles as $role) {
            if ($this->roleHasPermission($role, $action)) {
                return true;  // Mindestens eine Rolle erlaubt = YES
            }
        }
        
        return false;  // Keine Rolle erlaubt
    }
    
    private function roleHasPermission(Role $role, string $action): bool {
        $permissions = $role->permissions();
        
        // Wildcard Support: 'finance:*' erlaubt alle finance:* Actions
        foreach ($permissions as $perm) {
            if ($perm === $action || 
                (str_ends_with($perm, ':*') && str_starts_with($action, substr($perm, 0, -1)))) {
                return true;
            }
        }
        
        return false;
    }
}
```

#### Middleware: Zugriffskontrolle

```php
// Middleware/RoleMiddleware.php
class RoleMiddleware {
    public function handle(Request $request, Closure $next, string ...$requiredActions): Response {
        $userId = $this->userId();
        
        // Prüfe: Hat User MINDESTENS EINE Rolle mit den geforderten Permissions?
        foreach ($requiredActions as $action) {
            if (!$this->permissionService->hasPermission($userId, $action)) {
                return new JSONResponse(
                    ['error' => 'Insufficient permissions'],
                    Http::STATUS_FORBIDDEN
                );
            }
        }
        
        return $next($request);
    }
}

// Verwendung in Controller:
// Route: /api/v1/finance/export
// Middleware: RoleMiddleware:finance:read,export:pdf
```

---

### 💻 3. Backend Implementation

#### Controller mit Mehrfachrollen-Checks

```php
// Controller/FinanceController.php
class FinanceController {
    
    #[Route(methods: ['GET'], requirements: ['roleMiddleware' => 'finance:read'])]
    public function getFinances(): JSONResponse {
        // Zugriff nur wenn User MIN 1 Rolle mit finance:read hat
        return new JSONResponse($this->financeService->getAll());
    }
    
    #[Route(methods: ['POST'], requirements: ['roleMiddleware' => 'finance:write'])]
    public function createPayment(Request $request): JSONResponse {
        // Zugriff nur wenn User MIN 1 Rolle mit finance:write hat
        return new JSONResponse($this->financeService->create($request));
    }
    
    #[Route(methods: ['POST'], requirements: ['roleMiddleware' => 'export:sepa,export:pdf'])]
    public function exportSEPA(Request $request): JSONResponse {
        // Zugriff nur wenn MIN 1 Rolle SEPA-Export erlaubt
        return $this->sepaService->generate($request);
    }
}
```

#### Admin-Interface: Rollen zuweisen

```php
// Controller/AdminController.php
class AdminController {
    
    /**
     * Mehrfachrollen einem User zuweisen
     * POST /api/v1/admin/users/{userId}/roles
     */
    #[Route(methods: ['POST'], requirements: ['roleMiddleware' => 'admin:*'])]
    public function assignRoles(string $userId, Request $request): JSONResponse {
        $roleIds = json_decode($request->getBody(), true)['role_ids'];  // [1, 3]
        
        // 1. Alte Rollen löschen
        $this->roleService->removeAllRoles($userId);
        
        // 2. Neue Rollen hinzufügen
        foreach ($roleIds as $roleId) {
            $this->roleService->assignRole($userId, $roleId);
        }
        
        return new JSONResponse(['success' => true]);
    }
    
    /**
     * Alle Rollen eines Users abrufen
     * GET /api/v1/admin/users/{userId}/roles
     */
    #[Route(methods: ['GET'])]
    public function getUserRoles(string $userId): JSONResponse {
        $roles = $this->roleService->getUserRoles($userId);
        return new JSONResponse($roles);
    }
}
```

---

### 🎨 4. Frontend: Vue.js Guards & Permission Checks

#### Vue Route Guards

```javascript
// router/guards.ts
import { useStore } from 'pinia';

export const roleGuard = (to, from, next) => {
    const store = useStore();
    const userRoles = store.currentUser.roles;  // Array: ['kassierer', 'vorstand']
    
    // Route erfordert: ['finance:read', 'export:pdf']
    const requiredPermissions = to.meta.requiresPermissions || [];
    
    const hasPermission = requiredPermissions.some(perm =>
        userRoles.some(role => hasRolePermission(role, perm))
    );
    
    if (!hasPermission) {
        next({ name: 'AccessDenied' });
    } else {
        next();
    }
};

// Router-Definition
const routes = [
    {
        path: '/finance/export',
        component: FinanceExport,
        meta: { requiresPermissions: ['export:sepa', 'export:pdf'] }
    },
    {
        path: '/equipment',
        component: Equipment,
        meta: { requiresPermissions: ['equipment:*'] }
    }
];
```

#### Vue Components: Conditional Rendering

```vue
<!-- components/FinancePanel.vue -->
<template>
  <div v-if="canAccessFinance" class="finance-panel">
    <button @click="exportSEPA" v-if="hasRole('export:sepa')">
      SEPA exportieren
    </button>
    
    <button @click="exportPDF" v-if="hasRole('export:pdf')">
      PDF exportieren
    </button>
    
    <div v-if="hasRole('finance:write')" class="edit-section">
      <!-- Bearbeitungsformular -->
    </div>
  </div>
  
  <div v-else class="access-denied">
    ⛔ Du hast keine Berechtigung
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useStore } from 'pinia';

const store = useStore();

const canAccessFinance = computed(() =>
  store.userRoles.some(role => 
    ['kassierer', 'vorstand'].includes(role)
  )
);

const hasRole = (permission) =>
  store.userRoles.some(role => checkPermission(role, permission));
</script>
```

---

### 🧪 5. Tests: 50+ neue Tests für Mehrfachrollen

#### PHPUnit Tests: Permission Service

```php
// tests/Service/PermissionServiceTest.php
class PermissionServiceTest extends TestCase {
    
    /**
     * Test 1: Single Role - Finance Access
     */
    public function testSingleRoleFinanceAccess(): void {
        $userId = 'user123';
        $this->setupUserRole($userId, Role::KASSIERER);
        
        $this->assertTrue(
            $this->permissionService->hasPermission($userId, 'finance:read')
        );
        $this->assertTrue(
            $this->permissionService->hasPermission($userId, 'finance:write')
        );
        $this->assertFalse(
            $this->permissionService->hasPermission($userId, 'members:write')
        );
    }
    
    /**
     * Test 2: Multi-Role - Vorstand + Kassierer
     * Der User kann ALLES vom Vorstand UND alles vom Kassierer
     */
    public function testMultiRoleVorstandPlusKassierer(): void {
        $userId = 'user456';
        $this->setupUserRole($userId, Role::VORSTAND);
        $this->setupUserRole($userId, Role::KASSIERER);
        
        // Vorstand Permissions
        $this->assertTrue($this->permissionService->hasPermission($userId, 'members:write'));
        $this->assertTrue($this->permissionService->hasPermission($userId, 'finance:write'));
        
        // Kassierer Permissions (redundant aber auch ok)
        $this->assertTrue($this->permissionService->hasPermission($userId, 'finance:read'));
        $this->assertTrue($this->permissionService->hasPermission($userId, 'export:sepa'));
    }
    
    /**
     * Test 3: Multi-Role - Dirigent + Notenwart
     */
    public function testMultiRoleDirigentPlusNotenwart(): void {
        $userId = 'user789';
        $this->setupUserRole($userId, Role::DIRIGENT);
        $this->setupUserRole($userId, Role::NOTENWART);
        
        // Dirigent
        $this->assertTrue($this->permissionService->hasPermission($userId, 'events:read'));
        $this->assertTrue($this->permissionService->hasPermission($userId, 'events:write'));
        
        // Notenwart
        $this->assertTrue($this->permissionService->hasPermission($userId, 'documents:write'));
        $this->assertFalse($this->permissionService->hasPermission($userId, 'equipment:read'));
    }
    
    /**
     * Test 4: Wildcard Permissions
     */
    public function testWildcardPermissions(): void {
        $userId = 'admin';
        $this->setupUserRole($userId, Role::VORSTAND);
        
        $this->assertTrue($this->permissionService->hasPermission($userId, 'members:read'));
        $this->assertTrue($this->permissionService->hasPermission($userId, 'members:write'));
        $this->assertTrue($this->permissionService->hasPermission($userId, 'members:delete'));
        $this->assertTrue($this->permissionService->hasPermission($userId, 'anything:under:members'));
    }
    
    /**
     * Test 5: Role Removal
     * User hat beide Rollen, beide werden entfernt
     */
    public function testRoleRemoval(): void {
        $userId = 'user999';
        $this->setupUserRole($userId, Role::KASSIERER);
        $this->setupUserRole($userId, Role::NOTENWART);
        
        $this->assertTrue($this->permissionService->hasPermission($userId, 'finance:read'));
        $this->assertTrue($this->permissionService->hasPermission($userId, 'documents:read'));
        
        // Rollen entfernen
        $this->roleService->removeRole($userId, Role::KASSIERER);
        
        $this->assertFalse($this->permissionService->hasPermission($userId, 'finance:read'));
        $this->assertTrue($this->permissionService->hasPermission($userId, 'documents:read'));
    }
    
    // ... 45+ weitere Tests für:
    // - Verschiedene Rollenkombinationen
    // - Edge Cases (keine Rollen, Admin vs. Mitglied)
    // - Datenbank-Queries (Performance)
    // - Cache-Verhalten
    // - Concurrent Role Changes
}
```

#### Controller Tests: Middleware Integration

```php
// tests/Controller/FinanceControllerTest.php
class FinanceControllerTest extends TestCase {
    
    /**
     * Test: Kassierer kann Finanzen exportieren
     */
    public function testKassiererCanExportSEPA(): void {
        $userId = 'user_kassierer';
        $this->setupUserRole($userId, Role::KASSIERER);
        
        $response = $this->client->post('/api/v1/finance/export-sepa', [
            'auth' => [$userId, 'password']
        ]);
        
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertStringContainsString('xml', $response->getHeader('Content-Type'));
    }
    
    /**
     * Test: Mitglied kann NOT exportieren
     */
    public function testMemberCannotExportSEPA(): void {
        $userId = 'user_mitglied';
        $this->setupUserRole($userId, Role::MITGLIED);
        
        $response = $this->client->post('/api/v1/finance/export-sepa', [
            'auth' => [$userId, 'password']
        ]);
        
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }
    
    /**
     * Test: Dirigent + Kassierer = beide Kombinationspermissions
     */
    public function testMultiRoleControllerAccess(): void {
        $userId = 'user_dirigent_kassierer';
        $this->setupUserRole($userId, Role::DIRIGENT);
        $this->setupUserRole($userId, Role::KASSIERER);
        
        // Dirigent-Feature: Events erstellen
        $eventResponse = $this->client->post('/api/v1/events', [...]);
        $this->assertEquals(Response::HTTP_CREATED, $eventResponse->getStatusCode());
        
        // Kassierer-Feature: Finanzen exportieren
        $exportResponse = $this->client->post('/api/v1/finance/export-sepa', [...]);
        $this->assertEquals(Response::HTTP_OK, $exportResponse->getStatusCode());
    }
}
```

#### Vue Test: Permission Guards

```javascript
// tests/unit/guards/roleGuard.test.ts
import { describe, it, expect, beforeEach } from 'vitest';
import { roleGuard } from '@/router/guards';

describe('Role Guard', () => {
    
    it('should allow access when user has required role', () => {
        const store = {
            currentUser: { roles: ['kassierer', 'vorstand'] }
        };
        
        const to = { meta: { requiresPermissions: ['export:pdf'] } };
        const from = {};
        const next = vi.fn();
        
        roleGuard(to, from, next);
        
        expect(next).toHaveBeenCalledWith();  // No redirect
    });
    
    it('should deny access when user lacks required role', () => {
        const store = {
            currentUser: { roles: ['mitglied'] }
        };
        
        const to = { meta: { requiresPermissions: ['finance:write'] } };
        const from = {};
        const next = vi.fn();
        
        roleGuard(to, from, next);
        
        expect(next).toHaveBeenCalledWith({ name: 'AccessDenied' });
    });
});
```

---

### 📅 Release Timeline (Updated)

#### v0.2.0-beta (December 1, 2025)
- [x] Multi-role RBAC Data Model (user_roles Join-Tabelle)
- [x] PHP Role Enum + Permission Service
- [x] Backend Middleware (RoleMiddleware)
- [x] API Endpoints für Admin Role Assignment
- [x] Vue Route Guards
- [x] Conditional Rendering in Components
- [x] 50+ Unit Tests (PHP + Vue)
- [x] Documentation: RBAC Guide

**Expected Output:**
- Working multi-role system
- 50+ passing tests
- All role combinations tested
- Documentation complete

#### v0.2.0-rc (December 15, 2025)
- [ ] SEPA XML Export (mit Kassierer-Permission Check)
- [ ] PDF Export (Mitgliederlisten, Gebühren)
- [ ] E-Mail Integration für Benachrichtigungen
- [ ] Error Handling für Permission Denials
- [ ] Integration Tests (End-to-End)

#### v0.2.0 Production (December 25, 2025)
- [ ] All v0.2.0-rc Features stable
- [ ] Security Audit (Permissions)
- [ ] Performance: < 1s Ladezeit
- [ ] Bug Fixes from Testing
- [ ] Final Documentation

---

### 🔧 Tech Stack Update

| Layer | Technology | Details |
|-------|-----------|---------|
| **Backend** | PHP 8.0+ | Role Enums, Permission Middleware, Multi-Role Checks |
| **Database** | SQL | New: user_roles Join-Tabelle, role-based queries |
| **Frontend** | Vue.js 3 | Guards, Conditional Rendering, Permission Arrays |
| **Testing** | PHPUnit + Jest | 50+ Multi-Role Test Scenarios |
| **Build** | Vite | Optimized for Permission Checking (Tree-Shakeable) |

---

### 🎯 Success Criteria for v0.2.0

✅ **Functional**
- [ ] Each role can only access their allowed features
- [ ] Multi-role users have union of all role permissions
- [ ] Admin can assign/revoke roles dynamically
- [ ] Permission denied returns 403, not 500

✅ **Tested**
- [ ] 50+ test cases covering all role combinations
- [ ] Edge cases tested (no roles, all roles, concurrent changes)
- [ ] Controller tests with real middleware
- [ ] Vue component tests with different user roles

✅ **Documented**
- [ ] RBAC Implementation Guide (Developers)
- [ ] Role Management Guide (Admins)
- [ ] API Reference (All Endpoints + Required Roles)
- [ ] LESSONS_LEARNED update: Multi-Role Architecture Patterns

---

### 💳 3. SEPA pain.001 XML Export (ISO 20022 Standard)

#### Ziel
Kassierer können Zahlungsaufträge für Banksammelaufträge (SEPA-Lastschrift) exportieren.

#### Features
- ISO 20022 XML Standard (pain.001)
- Mandate-Management (Daten speichern für Wiederholungszahlungen)
- IBAN/BIC-Validierung (mod-97, BIC-Verzeichnis)
- Duplikat-Schutz (Identische Zahlungen blockieren)
- Test-Modus (Sandbox für Bankverbindung-Prüfung)

#### Database Schema

```sql
-- SEPA-Mandate für Lastschriften
CREATE TABLE sepa_mandates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL,
    iban VARCHAR(34) NOT NULL,
    bic VARCHAR(11),
    mandate_reference VARCHAR(35) UNIQUE,          -- z.B. "MUS-2024-001"
    signature_date DATE,                           -- Unterschriftsdatum
    first_payment_date DATE,                       -- Erste Zahlung
    status ENUM('pending', 'active', 'cancelled'),
    cancellation_date DATE,
    cancellation_reason VARCHAR(255),
    created_at TIMESTAMP,
    FOREIGN KEY(member_id) REFERENCES members(id),
    UNIQUE(member_id, iban)                        -- Ein Member pro IBAN
);

-- Export-Historie für Audits
CREATE TABLE sepa_exports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    export_date TIMESTAMP,
    file_name VARCHAR(255),
    num_debits INT,
    total_amount DECIMAL(12, 2),
    status ENUM('draft', 'submitted', 'executed', 'failed'),
    exported_by INT,
    xml_content LONGBLOB,                          -- Original XML
    created_at TIMESTAMP,
    FOREIGN KEY(exported_by) REFERENCES oc_users(id)
);
```

#### Backend Implementation

```php
// Service/SepaExportService.php
namespace OCA\Verein\Service;

use OCA\Verein\Db\SepaMandateMapper;
use OCA\Verein\Db\SepaExportMapper;

class SepaExportService {
    
    /**
     * Generiere pain.001 XML für Zahlungen
     * @param Member[] $members - Zu zahlende Mitglieder
     * @param string $purpose - Zahlungszweck (z.B. "Beitragseinzug Dezember 2024")
     * @param \DateTime $collectionDate - Fälligkeitsdatum
     * @return string XML-Inhalt
     */
    public function generateSEPAExport(
        array $members,
        string $purpose,
        \DateTime $collectionDate
    ): string {
        
        // Validierung
        $this->validateMembers($members);
        $this->validateDates($collectionDate);
        
        // XML-Header (ISO 20022 pain.001.002.03)
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Document/>');
        $xml->addAttribute('xmlns', 'urn:iso:std:iso:20022:tech:xsd:pain.001.002.03');
        
        // Geschäftsvorfälle
        $cstmrCdtTrfInitn = $xml->addChild('CstmrCdtTrfInitn');
        
        // Kopfdaten
        $grpHdr = $cstmrCdtTrfInitn->addChild('GrpHdr');
        $grpHdr->addChild('MsgId', 'VEREIN-' . date('YmdHis') . rand(100, 999));
        $grpHdr->addChild('CreDtTm', date('c'));
        $grpHdr->addChild('NbOfTxns', count($members));
        $grpHdr->addChild('CtrlSum', $this->calculateTotalAmount($members));
        
        // Initiator (Verein)
        $initgPty = $grpHdr->addChild('InitgPty');
        $this->addPartyInfo($initgPty, $this->getClubInfo());
        
        // Zahlungen
        $pmtInf = $cstmrCdtTrfInitn->addChild('PmtInf');
        $pmtInf->addChild('PmtInfId', 'VEREIN-' . date('YmdHi'));
        $pmtInf->addChild('PmtMtd', 'TRF');  // Transfer
        $pmtInf->addChild('ReqdExctnDt', $collectionDate->format('Y-m-d'));
        
        // Debtor (Verein)
        $dbtr = $pmtInf->addChild('Dbtr');
        $this->addPartyInfo($dbtr, $this->getClubInfo());
        
        // Debtor Account
        $dbtrAcct = $pmtInf->addChild('DbtrAcct');
        $dbtrAcct->addChild('Id')->addChild('IBAN', $this->getClubIBAN());
        
        // Debtor Agent (Bank)
        $dbtrAgt = $pmtInf->addChild('DbtrAgt');
        $dbtrAgt->addChild('FinInstnId')->addChild('BIC', $this->getClubBIC());
        
        // Kreditor
        $cdtr = $pmtInf->addChild('Cdtr');
        $this->addPartyInfo($cdtr, $this->getClubInfo());
        
        // Transaktionen
        $totalAmount = 0;
        foreach ($members as $member) {
            $mandate = $this->getMandateForMember($member['id']);
            if (!$mandate) continue;  // Überspringe Mitglieder ohne Mandat
            
            $cdtTrfTxInf = $pmtInf->addChild('CdtTrfTxInf');
            
            // Payment ID
            $pmtId = $cdtTrfTxInf->addChild('PmtId');
            $pmtId->addChild('InstrId', 'VEREIN-' . $member['id'] . '-' . date('Ym'));
            $pmtId->addChild('EndToEndId', $member['id']);
            
            // Amount
            $amount = (float)$member['fee'];
            $cdtTrfTxInf->addChild('Amt')
                        ->addChild('InstdAmt', number_format($amount, 2, '.', ''))
                        ->addAttribute('Ccy', 'EUR');
            
            $totalAmount += $amount;
            
            // Kreditor (Empfänger = auch Verein)
            $cdtrInTxn = $cdtTrfTxInf->addChild('CdtrInTxn');
            $this->addPartyInfo($cdtrInTxn, $this->getClubInfo());
            
            // Kreditor-Konto
            $cdtrAcctInTxn = $cdtTrfTxInf->addChild('CdtrAcctInTxn');
            $cdtrAcctInTxn->addChild('Id')->addChild('IBAN', $this->getClubIBAN());
            
            // Kreditor Bank
            $cdtrAgtInTxn = $cdtTrfTxInf->addChild('CdtrAgtInTxn');
            $cdtrAgtInTxn->addChild('FinInstnId')->addChild('BIC', $this->getClubBIC());
            
            // Remittance Info
            $rmtInf = $cdtTrfTxInf->addChild('RmtInf');
            $rmtInf->addChild('Ustrd', $purpose);
        }
        
        // Speichern in DB
        $this->sepaExportMapper->insert([
            'export_date' => date('Y-m-d H:i:s'),
            'file_name' => 'SEPA-' . date('Y-m-d_His') . '.xml',
            'num_debits' => count($members),
            'total_amount' => $totalAmount,
            'status' => 'draft',
            'exported_by' => $this->userId,
            'xml_content' => $xml->asXML()
        ]);
        
        return $xml->asXML();
    }
    
    /**
     * Validiere Mitglieder für Export
     */
    private function validateMembers(array $members): void {
        foreach ($members as $member) {
            // Prüfe Mandat
            $mandate = $this->getMandateForMember($member['id']);
            if (!$mandate || $mandate['status'] !== 'active') {
                throw new \Exception("Member {$member['id']} has no active mandate");
            }
            
            // Prüfe IBAN
            if (!$this->validateIBAN($mandate['iban'])) {
                throw new \Exception("Invalid IBAN for member {$member['id']}");
            }
            
            // Prüfe Betrag
            if ($member['fee'] <= 0) {
                throw new \Exception("Invalid amount for member {$member['id']}");
            }
        }
    }
    
    /**
     * Validiere IBAN (mod-97 Checksumme)
     */
    public function validateIBAN(string $iban): bool {
        $iban = str_replace(' ', '', strtoupper($iban));
        
        // Format prüfen
        if (!preg_match('/^[A-Z]{2}[0-9]{2}[A-Z0-9]{1,30}$/', $iban)) {
            return false;
        }
        
        // mod-97 Checksumme
        $rearranged = substr($iban, 4) . substr($iban, 0, 4);
        $numeric = '';
        
        for ($i = 0; $i < strlen($rearranged); $i++) {
            $char = $rearranged[$i];
            $numeric .= is_numeric($char) ? $char : (ord($char) - ord('A') + 10);
        }
        
        return bcmod($numeric, 97) === '1';
    }
    
    private function addPartyInfo(\SimpleXMLElement $element, array $info): void {
        $element->addChild('Nm', $info['name']);
        $id = $element->addChild('Id');
        $orgId = $id->addChild('OrgId');
        $orgId->addChild('Othr')->addChild('Id', $info['vr_number']);
    }
}

// Controller/FinanceController.php
#[Route('/api/v1/finance')]
class FinanceController {
    
    /**
     * POST /api/v1/finance/export-sepa
     * Exportiere SEPA-Zahlungsdatei
     * @Permission: kassierer, vorstand
     */
    #[Route(methods: ['POST'], requirements: ['roles' => ['kassierer', 'vorstand']])]
    public function exportSEPA(IRequest $request): DataResponse {
        $data = json_decode($request->getBody(), true);
        
        $members = $this->memberService->getByIds($data['member_ids']);
        $purpose = $data['purpose'] ?? 'Beitragseinzug';
        $collectionDate = new \DateTime($data['collection_date']);
        
        $xmlContent = $this->sepaService->generateSEPAExport($members, $purpose, $collectionDate);
        
        return new DataResponse([
            'success' => true,
            'xml' => $xmlContent,
            'filename' => 'SEPA-' . date('Y-m-d_His') . '.xml',
            'num_transactions' => count($members),
            'total_amount' => array_sum(array_column($members, 'fee'))
        ]);
    }
    
    /**
     * POST /api/v1/finance/export-pdf
     * Exportiere Mitgliederliste oder Rechnung als PDF
     * @Permission: kassierer, vorstand, dirigent
     */
    #[Route(methods: ['POST'], requirements: ['roles' => ['kassierer', 'vorstand', 'dirigent']])]
    public function exportPDF(IRequest $request): StreamResponse {
        $data = json_decode($request->getBody(), true);
        $type = $data['type'] ?? 'members';  // members, invoice, report
        
        $pdfContent = match($type) {
            'members' => $this->generateMemberListPDF($data),
            'invoice' => $this->generateInvoicePDF($data),
            'report' => $this->generateReportPDF($data),
            default => throw new \Exception("Unknown PDF type: $type")
        };
        
        return new StreamResponse(
            $pdfContent,
            Http::STATUS_OK,
            ['Content-Type' => 'application/pdf']
        );
    }
}
```

#### Frontend: Export-Dialog

```vue
<!-- components/FinanceExport.vue -->
<template>
  <div class="finance-export">
    <h2>Finanzexport</h2>
    
    <!-- SEPA Export -->
    <div class="export-section">
      <h3>🏦 SEPA-Zahlungsdatei exportieren</h3>
      
      <div class="form-group">
        <label>Mitglieder zum Einzug:</label>
        <div class="member-selector">
          <input v-model="sepaSearch" type="text" placeholder="Suchen...">
          <div class="member-list">
            <div v-for="member in filteredMembers" :key="member.id" class="member-item">
              <input type="checkbox" v-model="selectedMembers" :value="member.id">
              <span>{{ member.firstname }} {{ member.lastname }}</span>
              <span class="fee">{{ member.fee }} €</span>
            </div>
          </div>
        </div>
      </div>
      
      <div class="form-group">
        <label>Zahlungszweck:</label>
        <input v-model="sepaData.purpose" type="text" placeholder="z.B. Beitragseinzug Dezember 2024">
      </div>
      
      <div class="form-group">
        <label>Fälligkeitsdatum:</label>
        <input v-model="sepaData.collectionDate" type="date">
      </div>
      
      <div class="summary">
        <p><strong>Zahlungen:</strong> {{ selectedMembers.length }}</p>
        <p><strong>Gesamtbetrag:</strong> {{ totalAmount }} €</p>
      </div>
      
      <button @click="downloadSEPA" class="btn btn-primary">
        📥 SEPA-Datei herunterladen
      </button>
    </div>
    
    <!-- PDF Export -->
    <div class="export-section">
      <h3>📄 PDF exportieren</h3>
      
      <div class="pdf-options">
        <label>
          <input type="radio" v-model="pdfType" value="members">
          Mitgliederliste
        </label>
        <label>
          <input type="radio" v-model="pdfType" value="invoice">
          Rechnungen
        </label>
        <label>
          <input type="radio" v-model="pdfType" value="report">
          Finanzbericht
        </label>
      </div>
      
      <button @click="downloadPDF" class="btn btn-secondary">
        � PDF herunterladen
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const selectedMembers = ref([]);
const sepaSearch = ref('');
const sepaData = ref({
  purpose: 'Beitragseinzug',
  collectionDate: new Date().toISOString().split('T')[0]
});
const pdfType = ref('members');

const members = ref([]);  // Laden von API

const filteredMembers = computed(() => {
  return members.value.filter(m => 
    `${m.firstname} ${m.lastname}`.toLowerCase().includes(sepaSearch.value.toLowerCase())
  );
});

const totalAmount = computed(() => {
  return selectedMembers.value
    .map(id => members.value.find(m => m.id === id))
    .reduce((sum, m) => sum + (m?.fee || 0), 0);
});

const downloadSEPA = async () => {
  const response = await fetch('/apps/verein/api/v1/finance/export-sepa', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      member_ids: selectedMembers.value,
      ...sepaData.value
    })
  });
  
  const data = await response.json();
  
  // Download XML
  const blob = new Blob([data.xml], { type: 'application/xml' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = data.filename;
  a.click();
};

const downloadPDF = async () => {
  const response = await fetch('/apps/verein/api/v1/finance/export-pdf', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ type: pdfType.value })
  });
  
  const blob = await response.blob();
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `export-${pdfType.value}-${new Date().toISOString().split('T')[0]}.pdf`;
  a.click();
};
</script>

<style scoped>
.finance-export {
  max-width: 800px;
  padding: 2rem;
}

.export-section {
  margin: 2rem 0;
  padding: 1.5rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  background: #f9f9f9;
}

.member-selector {
  border: 1px solid #ddd;
  border-radius: 4px;
  overflow: hidden;
}

.member-list {
  max-height: 300px;
  overflow-y: auto;
}

.member-item {
  padding: 0.75rem;
  border-bottom: 1px solid #eee;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.member-item .fee {
  margin-left: auto;
  font-weight: bold;
  color: #0ea5e9;
}

.summary {
  margin: 1rem 0;
  padding: 1rem;
  background: white;
  border-radius: 4px;
  border-left: 3px solid #0ea5e9;
}

.pdf-options label {
  display: block;
  margin: 0.5rem 0;
  cursor: pointer;
}

.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 1rem;
  transition: all 0.3s;
}

.btn-primary {
  background: #0ea5e9;
  color: white;
}

.btn-primary:hover {
  background: #0284c7;
}

.btn-secondary {
  background: #6b7280;
  color: white;
}
</style>
```

#### Tests für SEPA Export

```php
// tests/Service/SepaExportServiceTest.php
class SepaExportServiceTest extends TestCase {
    
    /**
     * Test: SEPA XML wird korrekt generiert
     */
    public function testGenerateSEPAExport(): void {
        $members = [
            ['id' => 1, 'fee' => 100.00],
            ['id' => 2, 'fee' => 50.00]
        ];
        
        $xml = $this->sepaService->generateSEPAExport(
            $members,
            'Beitragseinzug',
            new \DateTime('2024-12-01')
        );
        
        $this->assertStringContainsString('CstmrCdtTrfInitn', $xml);
        $this->assertStringContainsString('150.00', $xml);  // 100 + 50
    }
    
    /**
     * Test: IBAN-Validierung (mod-97)
     */
    public function testIBANValidation(): void {
        $this->assertTrue($this->sepaService->validateIBAN('DE89370400440532013000'));
        $this->assertFalse($this->sepaService->validateIBAN('INVALID'));
        $this->assertFalse($this->sepaService->validateIBAN('DE89370400440532013001'));  // Falsche Checksumme
    }
    
    /**
     * Test: Duplikate werden blockiert
     */
    public function testDuplicatePaymentDetection(): void {
        $export1 = $this->createExport([1, 2]);
        
        $this->expectException(\Exception::class);
        $export2 = $this->createExport([1, 2]);  // Identisch - sollte fehlschlagen
    }
}
```

---

### 📊 4. Datenvalidierung & Error Handling

#### Validierungsregeln

| Feld | Regel | Fehlermeldung |
|------|-------|---|
| E-Mail | RFC 5322 | "Ungültige E-Mail-Adresse" |
| IBAN | mod-97 Checksumme | "IBAN-Checksumme ungültig" |
| BIC | ISO 9362 Format | "BIC-Format ungültig" |
| Telefon | Optional, min. 9 Ziffern | "Mindestens 9 Ziffern erforderlich" |
| Gebühr | Numerisch > 0 | "Gebühr muss > 0 sein" |
| Datum | ISO 8601 | "Ungültiges Datumsformat" |

#### Implementation

```php
// Service/ValidationService.php
class ValidationService {
    
    public function validateEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    public function validateIBAN(string $iban): bool {
        // ... (siehe SepaExportService oben)
    }
    
    public function validateBIC(string $bic): bool {
        // ISO 9362: 6-11 alphanumerische Zeichen
        return preg_match('/^[A-Z0-9]{6}[A-Z0-9]{0,3}$/', $bic);
    }
    
    public function validatePhone(string $phone): bool {
        // Mindestens 9 Ziffern
        $digits = preg_replace('/\D/', '', $phone);
        return strlen($digits) >= 9;
    }
    
    public function validateFee(float $fee): bool {
        return $fee > 0 && $fee <= 10000;
    }
}

// Middleware/ValidationMiddleware.php
class ValidationMiddleware {
    
    public function validateRequest(Request $request, array $schema): void {
        $data = json_decode($request->getBody(), true);
        $errors = [];
        
        foreach ($schema as $field => $rules) {
            if (empty($data[$field]) && !isset($rules['required'])) {
                continue;
            }
            
            foreach ($rules as $rule => $value) {
                $error = $this->validateField($field, $data[$field] ?? null, $rule, $value);
                if ($error) $errors[] = $error;
            }
        }
        
        if (!empty($errors)) {
            throw new BadRequest(implode('; ', $errors));
        }
    }
}
```

---

### ✅ Success Criteria für v0.2.0

✅ **Funktional**
- [ ] Multi-Role RBAC vollständig (50+ Tests)
- [ ] Alle Validierungen implementiert (IBAN, Email, Phone, etc.)
- [ ] SEPA XML Export funktionsfähig (ISO 20022)
- [ ] PDF-Export für Rechnungen & Listen
- [ ] 85%+ Test Coverage
- [ ] 0 Build Errors

✅ **Sicherheit**
- [ ] Rolle Middleware auf jedem Protected Endpoint
- [ ] CSRF-Protection aktiv
- [ ] Input Sanitization
- [ ] SQL Injection Prevention

✅ **Performance**
- [ ] RBAC-Check < 10ms (Cache)
- [ ] Export < 2 Sekunden
- [ ] API-Responses < 500ms

---

### 🎯 Fokus: Notenverwaltung, Migration & Automatisierung

**Neue Priorität:** Musikvereine benötigen Notenverwaltung mit Instrumentengruppen und Softnote-Migration.

---

### 🎵 1. Notenverwaltung mit Instrumentengruppen

#### Rollenmodell für Noten

| Rolle | Upload | Freigabe | Zugriff | Beschreibung |
|-------|--------|----------|--------|---|
| **Notenwart** | ✅ Volle | ✅ Volle | ✅ Alle | Zentrale Verwaltung, Upload, Freigabevergabe |
| **Dirigent** | ❌ Nein | ❌ Nein | ✅ Nach Freigabe | Zugriff nur auf freigegebene Noten |
| **Mitglied** | ❌ Nein | ❌ Nein | ✅ Nur Instrument | Nur Stimme des eigenen Instruments (z.B. Trompete.pdf) |

#### Datenmodell

```sql
-- Tabelle für Noten/Partituren
CREATE TABLE scores (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255),                   -- 'Beethovens 9 - Sinfonie'
    file_path VARCHAR(500),              -- PDF-Datei
    uploaded_by INT NOT NULL,            -- User ID (Notenwart)
    uploaded_at TIMESTAMP,
    is_published BOOLEAN DEFAULT FALSE,  -- Veröffentlicht für Mitglieder
    INDEX(is_published)
);

-- Tabelle für Berechtigungen auf Instrumentgruppen-Basis
CREATE TABLE score_permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    score_id INT NOT NULL,               -- FK zu scores.id
    instrument_group_id INT NOT NULL,    -- z.B. 'Trompete', 'Horn', 'Posaune'
    permission_type ENUM('read', 'write'),
    granted_at TIMESTAMP,
    granted_by INT,                      -- Admin/Notenwart
    UNIQUE(score_id, instrument_group_id),
    FOREIGN KEY(score_id) REFERENCES scores(id),
    INDEX(instrument_group_id)
);

-- Erweiterung: user_roles mit Instrumentengruppe
ALTER TABLE user_roles 
    ADD COLUMN instrument_group_id INT,  -- Optional: Benutzer-Instrument
    ADD FOREIGN KEY(instrument_group_id) REFERENCES instrument_groups(id);
```

#### Backend Implementation

```php
// Enums/InstrumentGroup.php
namespace OCA\Verein\Enums;

enum InstrumentGroup: string {
    case SOPRAN = 'sopran';
    case ALT = 'alt';
    case TENOR = 'tenor';
    case BASS = 'bass';
    
    case VIOLINE_I = 'violine_i';
    case VIOLINE_II = 'violine_ii';
    case VIOLA = 'viola';
    case VIOLONCELLO = 'violoncello';
    
    case TROMPETE = 'trompete';
    case HORN = 'horn';
    case POSAUNE = 'posaune';
    case TUBA = 'tuba';
}

// Service/ScorePermissionService.php
class ScorePermissionService {
    
    /**
     * Prüfe: Darf User diese Partitur sehen?
     * Regeln:
     * 1. Notenwart: Sieht ALLES
     * 2. Dirigent: Sieht nur VERÖFFENTLICHTE Noten
     * 3. Mitglied: Sieht nur Noten für sein Instrument
     */
    public function canAccessScore(string $userId, int $scoreId): bool {
        $userRoles = $this->roleService->getUserRoles($userId);
        $userInstrument = $this->getUserInstrumentGroup($userId);
        $scorePermissions = $this->getScorePermissions($scoreId);
        
        // 1. Notenwart → Zugriff auf ALLES
        if ($this->hasRole($userRoles, Role::NOTENWART)) {
            return true;
        }
        
        $score = $this->getScore($scoreId);
        
        // 2. Score muss veröffentlicht sein
        if (!$score->is_published) {
            return false;
        }
        
        // 3. Dirigent → Zugriff auf veröffentlichte Noten
        if ($this->hasRole($userRoles, Role::DIRIGENT)) {
            return true;
        }
        
        // 4. Mitglied → Nur eigenes Instrument
        if ($this->hasRole($userRoles, Role::MITGLIED)) {
            return $scorePermissions->contains($userInstrument);
        }
        
        return false;
    }
    
    /**
     * Notenwart vergibt Berechtigung
     */
    public function grantScorePermission(int $scoreId, string $instrumentGroup, string $grantedBy): void {
        $this->db->insert('score_permissions', [
            'score_id' => $scoreId,
            'instrument_group_id' => $instrumentGroup,
            'permission_type' => 'read',
            'granted_at' => time(),
            'granted_by' => $grantedBy,
        ]);
    }
}

// Controller/ScoreController.php
class ScoreController {
    
    /**
     * GET /api/v1/scores
     * Nur Noten, die User sehen darf
     */
    #[Route(methods: ['GET'])]
    public function listScores(): JSONResponse {
        $userId = $this->userId();
        $allScores = $this->scoreService->getAll();
        $visibleScores = array_filter($allScores, 
            fn($score) => $this->scorePermissionService->canAccessScore($userId, $score['id'])
        );
        return new JSONResponse($visibleScores);
    }
    
    /**
     * POST /api/v1/scores
     * Nur Notenwart darf hochladen
     */
    #[Route(methods: ['POST'], requirements: ['roleMiddleware' => 'notenwart'])]
    public function uploadScore(Request $request): JSONResponse {
        $file = $request->getUploadedFiles()[0];
        $scoreId = $this->scoreService->create($file);
        return new JSONResponse(['id' => $scoreId], Http::STATUS_CREATED);
    }
    
    /**
     * POST /api/v1/scores/{scoreId}/permissions
     * Notenwart vergibt Rechte
     */
    #[Route(methods: ['POST'], requirements: ['roleMiddleware' => 'notenwart'])]
    public function grantPermission(int $scoreId, Request $request): JSONResponse {
        $instrumentGroup = json_decode($request->getBody(), true)['instrument_group'];
        $this->scorePermissionService->grantScorePermission($scoreId, $instrumentGroup, $this->userId());
        return new JSONResponse(['success' => true]);
    }
}
```

#### Frontend Implementation

```vue
<!-- components/ScoreManager.vue -->
<template>
  <div class="score-manager">
    <!-- Notenwart: Upload & Freigabe -->
    <div v-if="hasRole('notenwart')" class="notenwart-section">
      <h3>Notenverwaltung</h3>
      
      <!-- Upload Form -->
      <form @submit.prevent="uploadScore">
        <input type="file" accept=".pdf" v-model="scoreFile">
        <input type="text" placeholder="Notenname" v-model="scoreName">
        <button type="submit">Hochladen</button>
      </form>
      
      <!-- Noten-Liste mit Freigaben -->
      <div v-for="score in allScores" :key="score.id" class="score-card">
        <h4>{{ score.name }}</h4>
        
        <!-- Instrumentgruppen-Freigabe -->
        <div class="permissions">
          <p>Freigegeben für:</p>
          <div v-for="instrument in instrumentGroups" :key="instrument">
            <label>
              <input type="checkbox" 
                     :checked="isGranted(score.id, instrument)"
                     @change="togglePermission(score.id, instrument)">
              {{ instrument }}
            </label>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Dirigent & Mitglied: Nur Lesezugriff -->
    <div v-else class="user-section">
      <h3>Verfügbare Noten</h3>
      <div v-for="score in visibleScores" :key="score.id" class="score-card">
        <h4>{{ score.name }}</h4>
        <a :href="score.download_url" target="_blank" download>
          📥 {{ score.file_name }}
        </a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useStore } from 'pinia';

const store = useStore();
const allScores = ref([]);
const visibleScores = ref([]);
const scoreFile = ref(null);
const scoreName = ref('');

const hasRole = (role) => store.userRoles.includes(role);

const loadScores = async () => {
  const response = await fetch('/api/v1/scores');
  const scores = await response.json();
  
  if (hasRole('notenwart')) {
    allScores.value = scores;  // Notenwart sieht ALLE
  } else {
    visibleScores.value = scores;  // Mitglieder sehen nur ihre
  }
};

const uploadScore = async () => {
  const formData = new FormData();
  formData.append('file', scoreFile.value);
  formData.append('name', scoreName.value);
  
  await fetch('/api/v1/scores', {
    method: 'POST',
    body: formData
  });
  
  await loadScores();
};

onMounted(loadScores);
</script>
```

#### Tests: Notenverwaltung

```php
// tests/Service/ScorePermissionServiceTest.php
class ScorePermissionServiceTest extends TestCase {
    
    /**
     * Test 1: Notenwart sieht alle Noten
     */
    public function testNotenwartSeesAllScores(): void {
        $userId = 'notenwart_user';
        $this->setupUserRole($userId, Role::NOTENWART);
        $scoreId = $this->createScore('Beethoven 9');
        
        $this->assertTrue(
            $this->scorePermissionService->canAccessScore($userId, $scoreId)
        );
    }
    
    /**
     * Test 2: Dirigent sieht nur veröffentlichte Noten
     */
    public function testDirigentSeesOnlyPublishedScores(): void {
        $userId = 'dirigent_user';
        $this->setupUserRole($userId, Role::DIRIGENT);
        
        $publishedScore = $this->createScore('Beethoven 9', true);
        $unpublishedScore = $this->createScore('Private Draft', false);
        
        $this->assertTrue(
            $this->scorePermissionService->canAccessScore($userId, $publishedScore)
        );
        $this->assertFalse(
            $this->scorePermissionService->canAccessScore($userId, $unpublishedScore)
        );
    }
    
    /**
     * Test 3: Trompeter sieht nur Trompete.pdf
     */
    public function testTrumpeterSeesOnlyTrumpetePart(): void {
        $userId = 'trumpeter_user';
        $this->setupUserRole($userId, Role::MITGLIED);
        $this->setUserInstrument($userId, InstrumentGroup::TROMPETE);
        
        $scoreId = $this->createScore('Beethoven 9', true);
        
        // Freigaben vergeben: Trompete + Horn
        $this->grantPermission($scoreId, InstrumentGroup::TROMPETE);
        $this->grantPermission($scoreId, InstrumentGroup::HORN);
        
        // Trompeter darf zugreifen
        $this->assertTrue(
            $this->scorePermissionService->canAccessScore($userId, $scoreId)
        );
        
        // Aber nicht als Hornist
        $this->setUserInstrument($userId, InstrumentGroup::HORN);
        $this->assertTrue(  // Horn war freigegeben
            $this->scorePermissionService->canAccessScore($userId, $scoreId)
        );
        
        // Nicht für Bläser
        $this->setUserInstrument($userId, InstrumentGroup::POSAUNE);
        $this->assertFalse(
            $this->scorePermissionService->canAccessScore($userId, $scoreId)
        );
    }
    
    /**
     * Test 4: Notenwart vergibt Berechtigung
     */
    public function testNotenwartGrantsPermission(): void {
        $scoreId = $this->createScore('Test Score', true);
        
        $this->scorePermissionService->grantScorePermission(
            $scoreId,
            InstrumentGroup::VIOLINE_I,
            'notenwart_admin'
        );
        
        // Violinist kann jetzt zugreifen
        $userId = 'violinist_user';
        $this->setupUserRole($userId, Role::MITGLIED);
        $this->setUserInstrument($userId, InstrumentGroup::VIOLINE_I);
        
        $this->assertTrue(
            $this->scorePermissionService->canAccessScore($userId, $scoreId)
        );
    }
}
```

---

### � 2. Softnote-Import-Werkzeug

#### Ziel
Migration von bestehenden Vereinsdaten aus Softnote (weit verbreitete deutsche Vereinsverwaltungs-Software) in die Vereins-App.

#### Unterstützte Daten
- Mitgliederlisten
- Instrumentengruppen & -zuweisungen
- Gebührenstrukturen
- Noten-PDFs

#### CLI-Tool: `softnote-import`

```bash
# Verwendungsbeispiel
php occ verein:softnote-import \
    --file=/path/to/softnote_export.csv \
    --format=csv \
    --validate \
    --dry-run

# Oder mit XML
php occ verein:softnote-import \
    --file=/path/to/softnote_export.xml \
    --format=xml \
    --verbose
```

#### Implementation

```php
// Command/SoftnoteImportCommand.php
namespace OCA\Verein\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class SoftnoteImportCommand extends Command {
    
    protected function configure(): void {
        $this->setName('verein:softnote-import')
            ->setDescription('Import members & data from Softnote CSV/XML')
            ->addOption('file', null, InputOption::VALUE_REQUIRED, 'Path to Softnote export file')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'Format: csv, xml', 'csv')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Validate without importing')
            ->addOption('verbose', null, InputOption::VALUE_NONE, 'Detailed output');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int {
        $file = $input->getOption('file');
        $format = $input->getOption('format');
        $dryRun = $input->getOption('dry-run');
        $verbose = $input->getOption('verbose');
        
        if (!file_exists($file)) {
            $output->writeln("<error>File not found: $file</error>");
            return 1;
        }
        
        try {
            // 1. Parse Datei
            $data = $this->parseFile($file, $format);
            $output->writeln("<info>✓ Parsed " . count($data['members']) . " members</info>");
            
            // 2. Validierung
            $errors = $this->validateData($data);
            if (!empty($errors)) {
                $output->writeln("<error>Validation errors:</error>");
                foreach ($errors as $error) {
                    $output->writeln("  - $error");
                }
                return 1;
            }
            
            if ($dryRun) {
                $output->writeln("<info>✓ Validation passed (dry-run, no data imported)</info>");
                return 0;
            }
            
            // 3. Import
            $results = $this->importData($data);
            
            $output->writeln("<info>✓ Import completed:</info>");
            $output->writeln("  - Members: " . $results['members_imported']);
            $output->writeln("  - Instruments: " . $results['instruments_assigned']);
            $output->writeln("  - Scores: " . $results['scores_imported']);
            
            return 0;
            
        } catch (\Exception $e) {
            $output->writeln("<error>Error: " . $e->getMessage() . "</error>");
            if ($verbose) {
                $output->writeln($e->getTraceAsString());
            }
            return 1;
        }
    }
    
    private function parseFile(string $file, string $format): array {
        return match($format) {
            'csv' => $this->parseCSV($file),
            'xml' => $this->parseXML($file),
            default => throw new \InvalidArgumentException("Unsupported format: $format")
        };
    }
    
    private function parseCSV(string $file): array {
        $members = [];
        $instruments = [];
        $scores = [];
        
        if (($handle = fopen($file, 'r')) !== false) {
            $headers = fgetcsv($handle);
            
            while (($row = fgetcsv($handle)) !== false) {
                $data = array_combine($headers, $row);
                
                // Map Softnote fields to Vereins-App fields
                $members[] = [
                    'firstname' => $data['Vorname'],
                    'lastname' => $data['Nachname'],
                    'email' => $data['Email'],
                    'phone' => $data['Telefon'],
                    'instrument_group' => $data['Instrument'],
                    'joined_at' => $data['Beitrittsdatum'],
                ];
                
                // Instrument-Zuweisungen
                if (!in_array($data['Instrument'], $instruments)) {
                    $instruments[] = $data['Instrument'];
                }
            }
            
            fclose($handle);
        }
        
        return [
            'members' => $members,
            'instruments' => $instruments,
            'scores' => $scores,
        ];
    }
    
    private function parseXML(string $file): array {
        // Similar to CSV but uses SimpleXML
        $xml = simplexml_load_file($file);
        // ... implementation
    }
    
    private function validateData(array $data): array {
        $errors = [];
        
        foreach ($data['members'] as $i => $member) {
            // Validate required fields
            if (empty($member['firstname'])) {
                $errors[] = "Row $i: Missing firstname";
            }
            if (empty($member['lastname'])) {
                $errors[] = "Row $i: Missing lastname";
            }
            
            // Validate email format
            if (!empty($member['email']) && !filter_var($member['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Row $i: Invalid email: {$member['email']}";
            }
            
            // Validate instrument group
            if (!empty($member['instrument_group'])) {
                if (!$this->isValidInstrument($member['instrument_group'])) {
                    $errors[] = "Row $i: Unknown instrument: {$member['instrument_group']}";
                }
            }
        }
        
        return $errors;
    }
    
    private function importData(array $data): array {
        $results = [
            'members_imported' => 0,
            'instruments_assigned' => 0,
            'scores_imported' => 0,
        ];
        
        // 1. Import members
        foreach ($data['members'] as $member) {
            $this->memberService->create($member);
            $results['members_imported']++;
        }
        
        // 2. Import instrument assignments
        foreach ($data['instruments'] as $instrument) {
            // Ensure instrument exists in system
            $this->instrumentService->ensureExists($instrument);
        }
        
        return $results;
    }
}
```

---

### 🔄 3. GUI-Import-Tools (Wizard-Design)

#### Ziel
Clubs können Daten einfach aus anderen Systemen importieren via grafisches Interface statt CLI.

#### Unterstützte Formate

| System | Formate | Features | Status |
|--------|---------|----------|--------|
| **Softnote** | CSV, XML | Mitglieder, Instrumente, Gebühren | ✅ v0.3.0 |
| **OpenJverein** | CSV, XML, DBF | Mitglieder, Beiträge, Finanzen | ✅ v0.3.0 |
| **ZKG** | CSV | Vereins-Datenaustausch (DV-Kopplung) | 🔮 v0.4.0+ |

#### Wizard-Workflow

**Schritt 1: Datei hochladen & Format erkennen**
```
┌─────────────────────────────────────┐
│  Daten importieren                  │
├─────────────────────────────────────┤
│                                     │
│  Quelle: Softnote / OpenJverein     │
│  Format: [Auto | CSV | XML | DBF]   │
│                                     │
│  [Datei hochladen]      📁          │
│  softnote_export.csv                │
│                                     │
│  [< Zurück]              [Weiter >] │
└─────────────────────────────────────┘
```

**Schritt 2: Vorschau & Feld-Mapping**
```
┌──────────────────────────────────────┐
│  Spalten zuordnen                    │
├──────────────────────────────────────┤
│                                      │
│  Softnote Spalte → Vereins-App Feld │
│  ──────────────────────────────────  │
│  Vorname          → firstname        │
│  Nachname         → lastname         │
│  E-Mail           → email            │
│  Mobil            → mobile (optional)│
│  Instrument       → instrument_group │
│  Beitrittsdatum   → joined_at        │
│                                      │
│  [< Zurück]              [Weiter >]  │
└──────────────────────────────────────┘
```

**Schritt 3: Validierung & Konflikt-Auflösung**
```
┌──────────────────────────────────────┐
│  ⚠️ Validierungswarnungen             │
├──────────────────────────────────────┤
│                                      │
│  ✓ 148 Reihen gültig                │
│  ⚠ 3 Warnungen:                      │
│    • Zeile 5: Email ungültig         │
│    • Zeile 12: IBAN ungültig         │
│    • Zeile 45: Instrument unbekannt  │
│                                      │
│  Duplikate erkannt: 2 (überspringen) │
│                                      │
│  Export-Modus:                       │
│  ○ Alle Fehler stoppen               │
│  ✓ Warnungen ignorieren              │
│  ○ Nur gültige importieren           │
│                                      │
│  [< Zurück]              [Weiter >]  │
└──────────────────────────────────────┘
```

**Schritt 4: Zusammenfassung & Undo-Option**
```
┌──────────────────────────────────────┐
│  ✓ Import abgeschlossen!             │
├──────────────────────────────────────┤
│                                      │
│  Erfolgreich importiert:             │
│  ├─ 148 Mitglieder hinzugefügt       │
│  ├─ 12 Instrumentgruppen             │
│  ├─ 3 Gebührenstrukturen             │
│  └─ 25 Noten-PDFs                    │
│                                      │
│  Fehlgeschlagen: 2                  │
│  Übersprungen: 0                    │
│                                      │
│  📋 Detaillierter Report:            │
│  [Fehlerprotokoll herunterladen]     │
│                                      │
│  [Rückgängig machen (Undo)]          │
│  [Dashboard anzeigen]                │
│                                      │
└──────────────────────────────────────┘
```

#### Implementation

```php
// Controller/ImportWizardController.php
namespace OCA\Verein\Controller;

class ImportWizardController {
    
    /**
     * POST /api/v1/import/upload
     * Datei hochladen & Format prüfen
     */
    #[Route(methods: ['POST'])]
    public function uploadFile(IRequest $request): DataResponse {
        $file = $request->getUploadedFiles()['file'];
        $uploadedFile = $file->getStream();
        
        // Format erkennen
        $format = $this->detectFormat($file->getClientFilename());
        
        // In Temp-Speicher sichern
        $tempPath = $this->storageService->storeTemporary($uploadedFile);
        
        // Vorschau generieren (erste 5 Reihen)
        $preview = $this->generatePreview($tempPath, $format, 5);
        
        return new DataResponse([
            'temp_id' => bin2hex(random_bytes(16)),
            'filename' => $file->getClientFilename(),
            'format' => $format,
            'preview' => $preview,
            'rowCount' => $this->countRows($tempPath, $format)
        ]);
    }
    
    /**
     * POST /api/v1/import/map-fields
     * Feld-Mapping speichern
     */
    #[Route(methods: ['POST'])]
    public function mapFields(IRequest $request): DataResponse {
        $data = json_decode($request->getBody(), true);
        $tempId = $data['temp_id'];
        $mapping = $data['mapping'];  // { 'Vorname' => 'firstname', ... }
        
        $session = $this->sessionService->get('import_' . $tempId);
        $session['mapping'] = $mapping;
        
        return new DataResponse(['success' => true]);
    }
    
    /**
     * POST /api/v1/import/validate
     * Validiere alle Reihen
     */
    #[Route(methods: ['POST'])]
    public function validate(IRequest $request): DataResponse {
        $data = json_decode($request->getBody(), true);
        $tempId = $data['temp_id'];
        
        $session = $this->sessionService->get('import_' . $tempId);
        $tempPath = $session['temp_path'];
        $mapping = $session['mapping'];
        
        $results = [
            'valid' => 0,
            'warnings' => [],
            'errors' => [],
            'duplicates' => 0,
            'rows' => []
        ];
        
        $rows = $this->parseFile($tempPath, $session['format']);
        
        foreach ($rows as $index => $row) {
            $mappedRow = $this->mapRow($row, $mapping);
            $validation = $this->validateRow($mappedRow);
            
            if ($validation['errors']) {
                $results['errors'][] = [
                    'row' => $index + 1,
                    'messages' => $validation['errors']
                ];
            } else if ($validation['warnings']) {
                $results['warnings'][] = [
                    'row' => $index + 1,
                    'messages' => $validation['warnings']
                ];
                $results['valid']++;
            } else {
                // Duplikat-Prüfung
                if ($this->isDuplicate($mappedRow)) {
                    $results['duplicates']++;
                } else {
                    $results['valid']++;
                }
            }
            
            $results['rows'][] = $mappedRow;
        }
        
        // Speichern für Schritt 4
        $session['validation_results'] = $results;
        $this->sessionService->set('import_' . $tempId, $session);
        
        return new DataResponse($results);
    }
    
    /**
     * POST /api/v1/import/execute
     * Führe Import durch mit Transaktionen
     */
    #[Route(methods: ['POST'])]
    public function execute(IRequest $request): DataResponse {
        $data = json_decode($request->getBody(), true);
        $tempId = $data['temp_id'];
        $mode = $data['mode'] ?? 'warnings_ok';  // 'strict', 'warnings_ok', 'valid_only'
        
        $session = $this->sessionService->get('import_' . $tempId);
        $results = $session['validation_results'];
        
        try {
            // Starte Transaktion für Rollback
            $this->db->beginTransaction();
            
            $importResults = [
                'members_created' => 0,
                'members_updated' => 0,
                'members_skipped' => 0,
                'instruments_created' => 0,
                'errors' => [],
                'undo_token' => bin2hex(random_bytes(16))  // Für Undo
            ];
            
            foreach ($results['rows'] as $index => $row) {
                try {
                    // Duplikate überspringen
                    if ($this->isDuplicate($row)) {
                        $importResults['members_skipped']++;
                        continue;
                    }
                    
                    // Member importieren/updaten
                    $member = $this->memberService->createOrUpdate($row);
                    $importResults['members_created']++;
                    
                    // Instrumente zuweisen
                    if (isset($row['instrument_group'])) {
                        $this->assignInstrument($member['id'], $row['instrument_group']);
                        $importResults['instruments_created']++;
                    }
                    
                } catch (\Exception $e) {
                    $importResults['errors'][] = [
                        'row' => $index + 1,
                        'message' => $e->getMessage()
                    ];
                    
                    if ($mode === 'strict') {
                        throw $e;
                    }
                }
            }
            
            $this->db->commit();
            
            // Speichere Undo-Token
            $this->cacheService->set(
                'import_undo_' . $importResults['undo_token'],
                $session,
                3600  // 1 Stunde
            );
            
            return new DataResponse($importResults);
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            return new DataResponse(
                ['error' => $e->getMessage()],
                Http::STATUS_BAD_REQUEST
            );
        }
    }
    
    /**
     * POST /api/v1/import/undo/{token}
     * Rückgängig machen
     */
    #[Route(methods: ['POST'])]
    public function undo(string $token): DataResponse {
        $session = $this->cacheService->get('import_undo_' . $token);
        if (!$session) {
            return new DataResponse(
                ['error' => 'Undo-Token abgelaufen'],
                Http::STATUS_NOT_FOUND
            );
        }
        
        try {
            $this->db->beginTransaction();
            
            // Lösche alle importierten Member
            // (In echter Impl. müsste man prüfen, welche neuen Member hinzugefügt wurden)
            // Hier: Beispiel-Implementierung
            
            $this->db->commit();
            
            return new DataResponse(['success' => true]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            return new DataResponse(
                ['error' => $e->getMessage()],
                Http::STATUS_BAD_REQUEST
            );
        }
    }
    
    private function validateRow(array $row): array {
        $errors = [];
        $warnings = [];
        
        // Email
        if (!empty($row['email']) && !filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Ungültige Email-Adresse';
        }
        
        // IBAN
        if (!empty($row['iban']) && !$this->sepaService->validateIBAN($row['iban'])) {
            $errors[] = 'IBAN ungültig (mod-97)';
        }
        
        // Instrument
        if (!empty($row['instrument_group'])) {
            try {
                InstrumentGroup::from($row['instrument_group']);
            } catch (\ValueError $e) {
                $warnings[] = "Unbekannte Instrumentengruppe: {$row['instrument_group']}";
            }
        }
        
        return compact('errors', 'warnings');
    }
}
```

#### Vue.js Import-Wizard Frontend

```vue
<!-- components/ImportWizard.vue -->
<template>
  <div class="import-wizard">
    <div class="wizard-header">
      <h1>📥 Daten importieren</h1>
      <p>Schritt {{ currentStep }} von 4</p>
    </div>
    
    <!-- Step 1: Upload -->
    <div v-if="currentStep === 1" class="wizard-step">
      <h2>Schritt 1: Datei hochladen</h2>
      
      <div class="form-group">
        <label>Quell-System:</label>
        <div class="source-select">
          <button 
            v-for="source in sources"
            :key="source.value"
            :class="['source-btn', { active: formData.source === source.value }]"
            @click="formData.source = source.value"
          >
            {{ source.label }}
          </button>
        </div>
      </div>
      
      <div class="form-group">
        <label>Datei hochladen</label>
        <div class="file-upload">
          <input 
            type="file" 
            @change="onFileSelected"
            :accept="getAcceptedFormats()"
          >
          <div v-if="uploadedFile" class="file-info">
            ✓ {{ uploadedFile.name }} ({{ formatBytes(uploadedFile.size) }})
            <button @click="uploadedFile = null">Ändern</button>
          </div>
        </div>
      </div>
      
      <div v-if="preview" class="preview">
        <h3>Vorschau (erste 5 Reihen):</h3>
        <table>
          <thead>
            <tr>
              <th v-for="col in preview.columns" :key="col">{{ col }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(row, idx) in preview.rows" :key="idx">
              <td v-for="col in preview.columns" :key="col">{{ row[col] }}</td>
            </tr>
          </tbody>
        </table>
        <p class="info">Gesamt: {{ preview.rowCount }} Reihen</p>
      </div>
    </div>
    
    <!-- Step 2: Field Mapping -->
    <div v-if="currentStep === 2" class="wizard-step">
      <h2>Schritt 2: Spalten zuordnen</h2>
      <p>Ordnen Sie die Spalten Ihrer Datei den Vereins-App-Feldern zu:</p>
      
      <div class="mapping-grid">
        <div v-for="(sourceCol, idx) in preview.columns" :key="idx" class="mapping-row">
          <span class="source-col">{{ sourceCol }}</span>
          <span class="arrow">→</span>
          <select v-model="formData.mapping[sourceCol]" class="target-field">
            <option value="">-- Ignorieren --</option>
            <option v-for="field in targetFields" :key="field" :value="field">
              {{ field }}
            </option>
          </select>
        </div>
      </div>
    </div>
    
    <!-- Step 3: Validation -->
    <div v-if="currentStep === 3" class="wizard-step">
      <h2>Schritt 3: Validierung & Konflikt-Auflösung</h2>
      
      <div class="validation-results">
        <div class="result-card success">
          <strong>✓ {{ validation.valid }} gültig</strong>
        </div>
        <div v-if="validation.warnings.length" class="result-card warning">
          <strong>⚠ {{ validation.warnings.length }} Warnungen</strong>
        </div>
        <div v-if="validation.errors.length" class="result-card error">
          <strong>❌ {{ validation.errors.length }} Fehler</strong>
        </div>
        <div class="result-card info">
          <strong>📋 {{ validation.duplicates }} Duplikate</strong>
        </div>
      </div>
      
      <div v-if="validation.warnings.length" class="warnings">
        <h3>Warnungen:</h3>
        <ul>
          <li v-for="(warn, idx) in validation.warnings.slice(0, 10)" :key="idx">
            Zeile {{ warn.row }}: {{ warn.messages.join('; ') }}
          </li>
        </ul>
      </div>
      
      <div v-if="validation.errors.length" class="errors">
        <h3>Fehler:</h3>
        <ul>
          <li v-for="(err, idx) in validation.errors.slice(0, 10)" :key="idx">
            Zeile {{ err.row }}: {{ err.messages.join('; ') }}
          </li>
        </ul>
      </div>
      
      <div class="form-group">
        <label>Fehlerbehandlung:</label>
        <label>
          <input type="radio" v-model="formData.errorMode" value="strict">
          Alle Fehler stoppen
        </label>
        <label>
          <input type="radio" v-model="formData.errorMode" value="warnings_ok">
          Warnungen ignorieren
        </label>
        <label>
          <input type="radio" v-model="formData.errorMode" value="valid_only">
          Nur gültige importieren
        </label>
      </div>
    </div>
    
    <!-- Step 4: Summary -->
    <div v-if="currentStep === 4" class="wizard-step summary">
      <h2>✓ Import abgeschlossen!</h2>
      
      <div class="results-summary">
        <p v-if="importResults.members_created">
          <strong>{{ importResults.members_created }} Mitglieder</strong> hinzugefügt
        </p>
        <p v-if="importResults.instruments_created">
          <strong>{{ importResults.instruments_created }}</strong> Instrumentzuweisungen
        </p>
        <p v-if="importResults.members_skipped">
          <strong>{{ importResults.members_skipped }}</strong> übersprungen (Duplikate)
        </p>
        <p v-if="importResults.errors.length" class="error-text">
          <strong>{{ importResults.errors.length }}</strong> Fehler
        </p>
      </div>
      
      <div v-if="importResults.errors.length" class="error-details">
        <h4>Fehlerprotokoll:</h4>
        <ul>
          <li v-for="(err, idx) in importResults.errors" :key="idx">
            Zeile {{ err.row }}: {{ err.message }}
          </li>
        </ul>
        <button @click="downloadErrorLog" class="btn btn-secondary">
          📥 Fehlerprotokoll herunterladen
        </button>
      </div>
      
      <div class="undo-section">
        <button @click="undoImport" class="btn btn-warning">
          ↶ Rückgängig machen (Undo)
        </button>
      </div>
    </div>
    
    <!-- Navigation -->
    <div class="wizard-nav">
      <button 
        @click="previousStep" 
        v-if="currentStep > 1"
        class="btn btn-secondary"
      >
        ← Zurück
      </button>
      <button 
        @click="nextStep" 
        v-if="currentStep < 4"
        :disabled="!canProceed"
        class="btn btn-primary"
      >
        Weiter →
      </button>
      <button 
        @click="executeImport" 
        v-if="currentStep === 3"
        :disabled="!canExecute"
        class="btn btn-success"
      >
        ► Import starten
      </button>
      <button 
        @click="finish" 
        v-if="currentStep === 4"
        class="btn btn-primary"
      >
        ✓ Fertig
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const currentStep = ref(1);
const uploadedFile = ref(null);
const preview = ref(null);
const validation = ref(null);
const importResults = ref(null);

const sources = [
  { value: 'softnote', label: 'Softnote' },
  { value: 'openjverein', label: 'OpenJverein' },
  { value: 'zkr', label: 'ZKG/DBF' }
];

const targetFields = [
  'firstname', 'lastname', 'email', 'phone', 'iban', 'bic',
  'instrument_group', 'joined_at', 'fee'
];

const formData = ref({
  source: 'softnote',
  mapping: {},
  errorMode: 'warnings_ok'
});

const canProceed = computed(() => {
  if (currentStep.value === 1) return !!uploadedFile.value && !!preview.value;
  if (currentStep.value === 2) return Object.values(formData.value.mapping).some(v => v);
  if (currentStep.value === 3) return !!validation.value;
  return true;
});

const canExecute = computed(() => {
  return validation.value && validation.value.valid > 0;
});

const onFileSelected = async (event) => {
  uploadedFile.value = event.target.files[0];
  
  const formDataObj = new FormData();
  formDataObj.append('file', uploadedFile.value);
  
  const response = await fetch('/apps/verein/api/v1/import/upload', {
    method: 'POST',
    body: formDataObj
  });
  
  const data = await response.json();
  preview.value = data.preview;
  formData.value.tempId = data.temp_id;
};

const nextStep = async () => {
  if (currentStep.value === 2) {
    // Speichere Mapping
    await fetch('/apps/verein/api/v1/import/map-fields', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        temp_id: formData.value.tempId,
        mapping: formData.value.mapping
      })
    });
    
    // Validiere
    const response = await fetch('/apps/verein/api/v1/import/validate', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ temp_id: formData.value.tempId })
    });
    
    validation.value = await response.json();
  }
  
  currentStep.value++;
};

const previousStep = () => {
  currentStep.value--;
};

const executeImport = async () => {
  const response = await fetch('/apps/verein/api/v1/import/execute', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      temp_id: formData.value.tempId,
      mode: formData.value.errorMode
    })
  });
  
  importResults.value = await response.json();
  currentStep.value = 4;
};

const undoImport = async () => {
  if (confirm('Möchten Sie den Import wirklich rückgängig machen?')) {
    await fetch(`/apps/verein/api/v1/import/undo/${importResults.value.undo_token}`, {
      method: 'POST'
    });
    alert('Import rückgängig gemacht!');
  }
};

const downloadErrorLog = () => {
  const csv = [
    ['Reihe', 'Fehler'],
    ...importResults.value.errors.map(e => [e.row, e.message])
  ].map(row => row.join(',')).join('\n');
  
  const blob = new Blob([csv], { type: 'text/csv' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `import-errors-${new Date().toISOString().split('T')[0]}.csv`;
  a.click();
};

const finish = () => {
  // Redirect to members list
  location.href = '/apps/verein/members';
};

const getAcceptedFormats = () => {
  return formData.value.source === 'zkr' ? '.dbf,.csv' : '.csv,.xml';
};

const formatBytes = (bytes) => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};
</script>

<style scoped>
/* Similar to SetupWizard.vue - see above for styling */
</style>
```

#### Tests für Import-Wizard

```php
// tests/Controller/ImportWizardControllerTest.php
class ImportWizardControllerTest extends TestCase {
    
    /**
     * Test: Softnote CSV Upload & Format-Erkennung
     */
    public function testSoftnoteCSVUpload(): void {
        $csv = <<<CSV
Vorname,Nachname,Email,Instrument,Beitrittsdatum
Max,Müller,max@example.com,Trompete,2020-01-15
Anna,Schmidt,anna@example.com,Violine,2019-06-20
CSV;
        
        $file = $this->createUploadedFile($csv, 'softnote.csv');
        $response = $this->controller->uploadFile($this->createMockRequest(['file' => $file]));
        
        $data = $response->getData();
        $this->assertEquals('csv', $data['format']);
        $this->assertEquals(2, $data['rowCount']);
        $this->assertNotEmpty($data['preview']);
    }
    
    /**
     * Test: Feld-Mapping speichern
     */
    public function testFieldMapping(): void {
        $mapping = [
            'Vorname' => 'firstname',
            'Nachname' => 'lastname',
            'Email' => 'email',
            'Instrument' => 'instrument_group'
        ];
        
        $response = $this->controller->mapFields($this->createMockRequest([
            'temp_id' => 'test123',
            'mapping' => $mapping
        ]));
        
        $this->assertEquals(['success' => true], $response->getData());
    }
    
    /**
     * Test: Validierung mit Warnungen & Fehlern
     */
    public function testValidationWithWarningsAndErrors(): void {
        // ... Setup Test-Daten ...
        
        $response = $this->controller->validate($this->createMockRequest([
            'temp_id' => 'test123'
        ]));
        
        $results = $response->getData();
        $this->assertGreater($results['valid'], 0);
        $this->assertGreater($results['duplicates'], 0);
    }
    
    /**
     * Test: Import mit Transaktion & Rollback
     */
    public function testImportWithRollback(): void {
        $response = $this->controller->execute($this->createMockRequest([
            'temp_id' => 'test123',
            'mode' => 'warnings_ok'
        ]));
        
        $results = $response->getData();
        $this->assertGreater($results['members_created'], 0);
        $this->assertNotEmpty($results['undo_token']);
    }
    
    /**
     * Test: Undo Import
     */
    public function testUndoImport(): void {
        // Execute import first
        $executeResponse = $this->controller->execute(...);
        $undoToken = $executeResponse->getData()['undo_token'];
        
        // Verify members were created
        $this->assertDatabaseHas('members', ['firstname' => 'Max']);
        
        // Undo
        $response = $this->controller->undo($undoToken);
        $this->assertEquals(['success' => true], $response->getData());
        
        // Verify members were deleted
        $this->assertDatabaseMissing('members', ['firstname' => 'Max']);
    }
}
```

---

### 📅 3. Release Timeline (v0.3.0)
        $csv = <<<CSV
Vorname,Nachname,Email,Telefon,Instrument,Beitrittsdatum
Max,Müller,max@example.com,01234567890,Trompete,2020-01-15
Anna,Schmidt,anna@example.com,09876543210,Violine,2019-06-20
CSV;
        
        $file = tempnam(sys_get_temp_dir(), 'softnote_');
        file_put_contents($file, $csv);
        
        $tester = $this->executeCommand('verein:softnote-import', [
            '--file' => $file,
            '--format' => 'csv',
            '--dry-run' => true,
        ]);
        
        $this->assertStringContainsString('Validation passed', $tester->getDisplay());
        
        unlink($file);
    }
    
    /**
     * Test 2: CSV Import mit ungültigem Email
     */
    public function testImportInvalidEmail(): void {
        $csv = <<<CSV
Vorname,Nachname,Email,Telefon,Instrument,Beitrittsdatum
Max,Müller,not-an-email,01234567890,Trompete,2020-01-15
CSV;
        
        $file = tempnam(sys_get_temp_dir(), 'softnote_');
        file_put_contents($file, $csv);
        
        $tester = $this->executeCommand('verein:softnote-import', [
            '--file' => $file,
            '--format' => 'csv',
            '--dry-run' => true,
        ]);
        
        $this->assertStringContainsString('Invalid email', $tester->getDisplay());
        
        unlink($file);
    }
    
    /**
     * Test 3: XML Import
     */
    public function testImportXML(): void {
        $xml = <<<XML
<?xml version="1.0"?>
<softnote>
    <member>
        <vorname>Max</vorname>
        <nachname>Müller</nachname>
        <email>max@example.com</email>
        <instrument>Trompete</instrument>
    </member>
</softnote>
XML;
        
        $file = tempnam(sys_get_temp_dir(), 'softnote_', '.xml');
        file_put_contents($file, $xml);
        
        $tester = $this->executeCommand('verein:softnote-import', [
            '--file' => $file,
            '--format' => 'xml',
        ]);
        
        $this->assertStringContainsString('Import completed', $tester->getDisplay());
        $this->assertDatabaseHas('members', ['firstname' => 'Max']);
        
        unlink($file);
    }
}
```

---

### 📅 3. Release Timeline (Updated)

#### v0.2.0 (December 25, 2025)
- [x] Multi-Role RBAC Complete
- [x] Input Validierung (IBAN, Email, Phone)
- [x] SEPA XML Export
- [x] PDF Export
- [x] Error Handling
- [x] 85%+ Test Coverage
- [x] Security Audit (Permissions)

**Deliverables:**
- Production-ready app with permissions
- All v0.2.0-rc features stable
- 0 build errors
- 85%+ test coverage

#### v0.3.0 (Q1 2026 - March 31, 2026)
- [ ] Notenverwaltung mit Instrumentengruppen (NEW PRIORITY!)
  - Score upload & management
  - Instrument-based permissions
  - 20+ new tests
- [ ] Softnote-Import Tool (NEW PRIORITY!)
  - CSV/XML import
  - Data validation
  - Migration helper
- [ ] Automatische Mahnungen
  - Cronjob für Beiträge
  - E-Mail Benachrichtigungen
- [ ] Kalender Integration
- [ ] Weitere Integrationen (Deck, Talk)

**Deliverables:**
- Score management complete
- Softnote migration tool ready
- Automation framework
- 80%+ test coverage (new tests)

---

### 🎯 Success Criteria for v0.3.0

✅ **Notenverwaltung (Score Management)**
- [ ] Notenwart can upload PDFs
- [ ] Notenwart can grant instrument-based permissions
- [ ] Mitglieder see only their instrument parts
- [ ] Dirigent sees all published scores
- [ ] Score list filtered correctly per user

✅ **Softnote Import**
- [ ] CSV import validated & working
- [ ] XML import validated & working
- [ ] Error handling & reporting
- [ ] Data integrity checks
- [ ] Migration successful (test migration)

✅ **Tested**
- [ ] 25+ tests for score permissions
- [ ] 15+ tests for import validation
- [ ] Edge cases (missing data, invalid instruments)
- [ ] Integration tests (import → permission check)

✅ **Documented**
- [ ] Score Management Guide (Notenwart)
- [ ] Import Tool Documentation
- [ ] Migration Best Practices
- [ ] Troubleshooting Guide

---

## 🎯 v1.0.0 (Q4 2026)

### 🎯 Fokus: Production Release

**Stabilität & Polish:**
- [ ] 100% Unit Test Coverage
- [ ] Security Audit (Third-Party)
- [ ] Performance: < 1s Ladezeit
- [ ] i18n (Internationalisierung)
  - English
  - Deutsch
  - Weitere Sprachen

### 🌟 New Features
- [ ] Mitgliedsbeitrag-Automationen
  - Automatische Einzüge (SEPA)
  - Zahlungsplan
  - Kündigungen
- [ ] Berichts-Generator
  - Jahresberichte
  - Kassenprüfung
  - Statistiken
- [ ] Web-Shop (optional)
  - Merchandise-Verkauf
  - Ticketing

### 📦 Deployment
- [ ] Nextcloud App Store Release
- [ ] Docker Image
- [ ] Installationsscript

---

## 🐛 Known Issues & Limitations

### v0.1.0 (CURRENT)
| Issue | Severity | Workaround | ETA |
|-------|----------|-----------|-----|
| **Keine Berechtigungen (alle Nutzer = Admin)** | 🔴 Kritisch | Nur mit Admin-Nutzer verwenden | v0.2.0-beta (1. Dez) |
| Keine Mehrfachrollen | 🔴 Kritisch | Nutzer auf einzelne Rolle beschränkt | v0.2.0-beta (1. Dez) |
| IBAN nicht validiert | 🟡 Medium | Manuell validieren | v0.2.0-beta (1. Dez) |
| Kein Export (SEPA/PDF) | 🟡 Medium | Manueller DB-Export | v0.2.0-rc (15. Dez) |
| Keine Benachrichtigungen | 🟢 Low | E-Mail selbst versenden | v0.3.0 (Q2 2026) |

### v0.3.0 (PLANNED - Q1 2026)
| Issue | Severity | Status | ETA |
|-------|----------|--------|-----|
| Score Management nicht implementiert | 🔴 Kritisch | Geplant | ✅ v0.3.0 (Mar 2026) |
| Softnote Import fehlt | � Kritisch | Geplant | ✅ v0.3.0 (Mar 2026) |
| Automatische Mahnungen fehlen | 🟡 Medium | Backlog | v0.3.0 (Mar 2026) |
| GDPR Compliance unvollständig | � Medium | Backlog | v0.3.1 |

### Performance
- Bundle-Größe: 387 KB (Ziel v0.2.0: < 200 KB mit Code Splitting)
- Datenbankqueries: nicht optimiert (Ziel v0.2.0: Indexed Joins für Rollen)
- Keine Caching-Strategie (Ziel v0.3.0: Redis Cache für Permissions)

### Security (Todo vor v1.0.0)
- [ ] Rate Limiting (Ziel v0.2.0: Basic Rate Limiting)
- [ ] CSRF Protection (Ziel v0.2.0: Token Validation)
- [ ] Input Sanitization (in allen Feldern)
- [ ] Output Escaping (Vue XSS Protection)
- [ ] Security Headers (X-Frame-Options, CSP)

---

## 📈 Metrics & Goals

### Adoption Goals
- **v0.2.0**: 50+ Installationen (RBAC, Validation, Export)
- **v0.3.0**: 150+ Installationen (Score Management, Softnote Import)
- **v1.0.0**: 500+ Installationen (Production-Ready, Full Features)

### Quality Goals
| Metrik | v0.1 | v0.2 | v0.3 | v1.0 |
|--------|------|------|------|------|
| Test Coverage | 0% | 85% | 85%+ | 100% |
| Score Management | ❌ | ❌ | ✅ | ✅ |
| Migration Tools | ❌ | ❌ | ✅ | ✅ |
| Bug Response | - | <7 days | <5 days | <1 day |
| Performance | - | < 2s | < 1.5s | < 500ms |

---

## 🔧 v0.4.0 (Q2 2026 - April-Juni 2026)

### 🎯 Fokus: Setup-Wizard & Dokumentvorlagen

---

### 1️⃣ Setup-Wizard für Ersteinrichtung

#### Ziel
Neue Nutzer können die App ohne technische Vorkenntnisse in 5 Minuten einrichten.

#### Workflow

**Schritt 1: Vereinstyp auswählen**
```
┌─────────────────────────────────────┐
│  Welche Art von Verein?             │
├─────────────────────────────────────┤
│  ○ Musikverein (Orchester, Band)    │
│  ○ Sportverein (Fußball, Tennis)    │
│  ○ Kulturverein (Theater, Film)     │
│  ○ Allgemein (Andere Verbände)      │
│                                     │
│  [< Zurück]              [Weiter >] │
└─────────────────────────────────────┘
```

**Schritt 2: Automatische Rollen-Initialisierung**
```
Nach Vereinstyp-Auswahl:
  Musikverein:
    ✓ Vorstand
    ✓ Kassierer
    ✓ Dirigent
    ✓ Notenwart
    ✓ Zeugwart
    ✓ Mitglied

  Sportverein:
    ✓ Trainer
    ✓ Zeugwart
    ✓ Mitglied
    ✓ Admin

  Kulturverein:
    ✓ Leitung
    ✓ Assistenz
    ✓ Mitglied
    ✓ Gast (optional)
```

**Schritt 3: Finanzmodul-Konfiguration**
```
┌─────────────────────────────────────┐
│  Finanzen einrichten                │
├─────────────────────────────────────┤
│  Beitragsordnung:                   │
│  [Standardgebühr]      [100,00 €]   │
│  [Reduzierten Satz]    [50,00 €]    │
│                                     │
│  SEPA-Mandate:                      │
│  [Gläubiger-ID]        [DE...]      │
│  [Sequenznummer]       [001]        │
│                                     │
│  Zahlungsverfahren:                 │
│  ☑ Banküberweisung                 │
│  ☑ SEPA Lastschrift                │
│  ☐ PayPal (erwerbbar)              │
│  ☐ Stripe (erwerbbar)              │
│                                     │
│  [< Zurück]              [Weiter >] │
└─────────────────────────────────────┘
```

**Schritt 4: Zusammenfassung & Abschluss**
```
┌──────────────────────────────────────┐
│  Zusammenfassung                     │
├──────────────────────────────────────┤
│  Vereinstyp:        Musikverein      │
│  Rollen:            6 Rollen         │
│  Finanzmodul:       Aktiv            │
│  SEPA:              Konfiguriert     │
│                                      │
│  Setup abschließen:                  │
│  [Abschließen & Start]               │
│                                      │
│  Oder später anpassen via            │
│  → Einstellungen → Verein            │
│                                      │
│                        [Abschließen] │
└──────────────────────────────────────┘
```

#### Implementation

```php
// Controller/SetupWizardController.php
namespace OCA\Verein\Controller;

use OCP\AppFramework\Controller;
use OCP\IRequest;

class SetupWizardController extends Controller {
    
    /**
     * GET: Setup-Wizard Frontend laden
     */
    public function index(): TemplateResponse {
        return new TemplateResponse('verein', 'setup-wizard');
    }
    
    /**
     * POST: Vereinstyp speichern & Rollen initialisieren
     */
    public function initializeRoles(IRequest $request): DataResponse {
        $clubType = $request->getParam('club_type');
        // Valid: 'music', 'sport', 'culture', 'general'
        
        $roles = $this->getRolesForClubType($clubType);
        
        foreach ($roles as $roleData) {
            $this->roleService->createRole(
                $roleData['name'],
                $roleData['permissions'],
                $roleData['description']
            );
        }
        
        return new DataResponse(['status' => 'ok']);
    }
    
    /**
     * POST: Finanzmodul-Konfiguration speichern
     */
    public function configureFinance(IRequest $request): DataResponse {
        $settings = [
            'standard_fee' => (float)$request->getParam('standard_fee'),
            'reduced_fee' => (float)$request->getParam('reduced_fee'),
            'creditor_id' => $request->getParam('creditor_id'),
            'sepa_sequence' => $request->getParam('sepa_sequence'),
            'enabled_methods' => $request->getParam('enabled_methods'),
        ];
        
        $this->configService->setAllSettings('finance', $settings);
        
        return new DataResponse(['status' => 'ok']);
    }
    
    /**
     * POST: Setup abschließen
     */
    public function completeSetup(IRequest $request): DataResponse {
        $this->configService->setConfig('setup_completed', true);
        $this->configService->setConfig('setup_date', date('c'));
        
        return new DataResponse(['redirect' => '/apps/verein/dashboard']);
    }
    
    private function getRolesForClubType(string $clubType): array {
        return match($clubType) {
            'music' => $this->getMusicRoles(),
            'sport' => $this->getSportRoles(),
            'culture' => $this->getCultureRoles(),
            'general' => $this->getGeneralRoles(),
            default => []
        };
    }
    
    private function getMusicRoles(): array {
        return [
            [
                'name' => 'vorstand',
                'permissions' => ['*'],  // Alle Berechtigungen
                'description' => 'Vereinsvorstand: Vollzugriff'
            ],
            [
                'name' => 'kassierer',
                'permissions' => ['finance.*', 'export.sepa', 'export.pdf'],
                'description' => 'Schatzmeister: Finanzverwaltung'
            ],
            [
                'name' => 'dirigent',
                'permissions' => ['events.write', 'members.read', 'scores.read'],
                'description' => 'Dirigent: Proben- & Terminplanung'
            ],
            [
                'name' => 'notenwart',
                'permissions' => ['scores.*', 'members.read'],
                'description' => 'Notenwart: Notenverwaltung'
            ],
            [
                'name' => 'zeugwart',
                'permissions' => ['equipment.*'],
                'description' => 'Zeugwart: Instrumentenverwaltung'
            ],
            [
                'name' => 'mitglied',
                'permissions' => ['members.read:own', 'scores.read', 'events.read'],
                'description' => 'Mitglied: Lesezugriff auf eigene Daten'
            ]
        ];
    }
}
```

#### Vue.js Frontend

```vue
<!-- components/SetupWizard.vue -->
<template>
  <div class="setup-wizard">
    <div class="wizard-header">
      <h1>🎉 Willkommen bei der Nextcloud Vereins-App!</h1>
      <p>Starten Sie mit dem Setup-Assistenten</p>
    </div>
    
    <!-- Step 1: Club Type Selection -->
    <div v-if="currentStep === 1" class="wizard-step">
      <h2>Schritt 1: Vereinstyp auswählen</h2>
      <div class="club-type-options">
        <div 
          v-for="type in clubTypes" 
          :key="type.value"
          :class="['type-card', { selected: formData.clubType === type.value }]"
          @click="selectClubType(type.value)"
        >
          <div class="icon">{{ type.icon }}</div>
          <div class="name">{{ type.label }}</div>
          <div class="description">{{ type.description }}</div>
        </div>
      </div>
    </div>
    
    <!-- Step 2: Role Review -->
    <div v-if="currentStep === 2" class="wizard-step">
      <h2>Schritt 2: Rollen überprüfen</h2>
      <p>Diese Rollen werden automatisch erstellt:</p>
      <div class="roles-list">
        <div v-for="role in roles" :key="role.name" class="role-item">
          <strong>{{ role.label }}</strong>
          <p>{{ role.description }}</p>
          <div class="permissions">
            <span v-for="perm in role.permissions" :key="perm" class="permission-badge">
              {{ perm }}
            </span>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Step 3: Finance Configuration -->
    <div v-if="currentStep === 3" class="wizard-step">
      <h2>Schritt 3: Finanzen konfigurieren</h2>
      
      <div class="form-group">
        <label>Standardgebühr (€)</label>
        <input v-model.number="formData.standardFee" type="number" step="0.01" min="0">
      </div>
      
      <div class="form-group">
        <label>Reduzierter Satz (€)</label>
        <input v-model.number="formData.reducedFee" type="number" step="0.01" min="0">
      </div>
      
      <div class="form-group">
        <label>Gläubiger-ID (für SEPA)</label>
        <input v-model="formData.creditorId" placeholder="z.B. DE98ZZZ09999999999">
      </div>
      
      <div class="form-group">
        <label>Sequenznummer</label>
        <input v-model="formData.sepaSequence" placeholder="z.B. 001">
      </div>
      
      <div class="form-group">
        <label>Zahlungsmethoden</label>
        <div class="checkboxes">
          <label>
            <input v-model="formData.enabledMethods" type="checkbox" value="bank_transfer">
            Banküberweisung
          </label>
          <label>
            <input v-model="formData.enabledMethods" type="checkbox" value="sepa">
            SEPA Lastschrift
          </label>
          <label>
            <input v-model="formData.enabledMethods" type="checkbox" value="paypal">
            PayPal (optional)
          </label>
        </div>
      </div>
    </div>
    
    <!-- Step 4: Summary -->
    <div v-if="currentStep === 4" class="wizard-step summary">
      <h2>Schritt 4: Zusammenfassung</h2>
      <div class="summary-content">
        <div class="summary-item">
          <strong>Vereinstyp:</strong> {{ getClubTypeLabel() }}
        </div>
        <div class="summary-item">
          <strong>Rollen:</strong> {{ roles.length }} Rollen werden erstellt
        </div>
        <div class="summary-item">
          <strong>Finanzmodul:</strong> Aktiviert
        </div>
        <div class="summary-item">
          <strong>SEPA-Mandate:</strong> {{ formData.creditorId ? 'Konfiguriert' : 'Übersprungen' }}
        </div>
      </div>
      <p class="hint">Sie können alle Einstellungen später im Admin-Bereich ändern.</p>
    </div>
    
    <!-- Navigation -->
    <div class="wizard-nav">
      <button 
        @click="previousStep" 
        v-if="currentStep > 1"
        class="btn btn-secondary"
      >
        ← Zurück
      </button>
      <button 
        @click="nextStep" 
        v-if="currentStep < 4"
        :disabled="!canProceed"
        class="btn btn-primary"
      >
        Weiter →
      </button>
      <button 
        @click="completeSetup" 
        v-if="currentStep === 4"
        class="btn btn-success"
      >
        ✓ Setup abschließen
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const currentStep = ref(1);
const formData = ref({
  clubType: null,
  standardFee: 100.00,
  reducedFee: 50.00,
  creditorId: '',
  sepaSequence: '001',
  enabledMethods: ['bank_transfer', 'sepa']
});

const clubTypes = [
  {
    value: 'music',
    label: 'Musikverein',
    icon: '🎵',
    description: 'Orchester, Band, Musikgruppe'
  },
  {
    value: 'sport',
    label: 'Sportverein',
    icon: '⚽',
    description: 'Fußball, Tennis, Leichtathletik'
  },
  {
    value: 'culture',
    label: 'Kulturverein',
    icon: '🎭',
    description: 'Theater, Kunstverein, Filmclub'
  },
  {
    value: 'general',
    label: 'Allgemeiner Verein',
    icon: '🏢',
    description: 'Andere Verbände'
  }
];

const roles = computed(() => {
  const roleMap = {
    music: [
      { name: 'vorstand', label: 'Vorstand', permissions: ['members:write', 'finance:write', 'export:*'], description: 'Vereinsvorstand mit Vollzugriff' },
      { name: 'kassierer', label: 'Kassierer', permissions: ['finance:write', 'export:sepa', 'export:pdf'], description: 'Schatzmeister' },
      { name: 'dirigent', label: 'Dirigent', permissions: ['events:write', 'members:read', 'scores:read'], description: 'Dirigent und Leitung' },
      { name: 'notenwart', label: 'Notenwart', permissions: ['scores:*', 'members:read'], description: 'Notenverwaltung' },
      { name: 'zeugwart', label: 'Zeugwart', permissions: ['equipment:*'], description: 'Instrumentenverwaltung' },
      { name: 'mitglied', label: 'Mitglied', permissions: ['members:read:own', 'scores:read', 'events:read'], description: 'Normales Mitglied' }
    ],
    sport: [
      { name: 'trainer', label: 'Trainer', permissions: ['members:write', 'events:write', 'teams:write'], description: 'Trainer und Team-Leitung' },
      { name: 'zeugwart', label: 'Zeugwart', permissions: ['equipment:*'], description: 'Equipment-Verwaltung' },
      { name: 'mitglied', label: 'Mitglied', permissions: ['members:read:own', 'teams:read'], description: 'Normales Mitglied' }
    ],
    culture: [
      { name: 'leitung', label: 'Leitung', permissions: ['*'], description: 'Vereinsleitung' },
      { name: 'assistenz', label: 'Assistenz', permissions: ['members:write', 'events:write'], description: 'Administrative Assistenz' },
      { name: 'mitglied', label: 'Mitglied', permissions: ['members:read:own', 'events:read'], description: 'Normales Mitglied' }
    ]
  };
  return roleMap[formData.value.clubType] || [];
});

const canProceed = computed(() => {
  if (currentStep.value === 1) return formData.value.clubType !== null;
  if (currentStep.value === 3) return formData.value.standardFee > 0;
  return true;
});

const selectClubType = (type) => {
  formData.value.clubType = type;
};

const nextStep = () => {
  if (canProceed.value && currentStep.value < 4) {
    currentStep.value++;
  }
};

const previousStep = () => {
  if (currentStep.value > 1) {
    currentStep.value--;
  }
};

const getClubTypeLabel = () => {
  return clubTypes.find(t => t.value === formData.value.clubType)?.label || '';
};

const completeSetup = async () => {
  try {
    // Step 1: Initialize roles
    await fetch('/apps/verein/api/v1/setup/initialize-roles', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ club_type: formData.value.clubType })
    });
    
    // Step 2: Configure finance
    await fetch('/apps/verein/api/v1/setup/configure-finance', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        standard_fee: formData.value.standardFee,
        reduced_fee: formData.value.reducedFee,
        creditor_id: formData.value.creditorId,
        sepa_sequence: formData.value.sepaSequence,
        enabled_methods: formData.value.enabledMethods
      })
    });
    
    // Step 3: Mark setup as complete
    await fetch('/apps/verein/api/v1/setup/complete', {
      method: 'POST'
    });
    
    // Redirect to dashboard
    router.push('/apps/verein/dashboard');
  } catch (error) {
    console.error('Setup failed:', error);
    alert('Setup konnte nicht abgeschlossen werden. Bitte versuchen Sie es später erneut.');
  }
};
</script>

<style scoped>
.setup-wizard {
  max-width: 600px;
  margin: 2rem auto;
  padding: 2rem;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.wizard-header {
  text-align: center;
  margin-bottom: 2rem;
}

.club-type-options {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin: 1rem 0;
}

.type-card {
  padding: 1.5rem;
  border: 2px solid #ccc;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s;
  text-align: center;
}

.type-card:hover,
.type-card.selected {
  border-color: #0ea5e9;
  background: #f0f9ff;
}

.type-card .icon {
  font-size: 2rem;
  margin-bottom: 0.5rem;
}

.form-group {
  margin: 1.5rem 0;
}

.form-group label {
  display: block;
  font-weight: bold;
  margin-bottom: 0.5rem;
}

.form-group input,
.form-group select {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #ccc;
  border-radius: 4px;
}

.checkboxes label {
  display: block;
  margin: 0.5rem 0;
  cursor: pointer;
}

.roles-list {
  margin: 1rem 0;
}

.role-item {
  padding: 1rem;
  margin: 0.5rem 0;
  background: #f5f5f5;
  border-radius: 4px;
  border-left: 3px solid #0ea5e9;
}

.permission-badge {
  display: inline-block;
  background: #0ea5e9;
  color: white;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.85rem;
  margin: 0.25rem 0.25rem 0 0;
}

.summary {
  background: #f9fafb;
  padding: 1.5rem;
  border-radius: 8px;
}

.summary-item {
  padding: 0.75rem 0;
  border-bottom: 1px solid #e5e7eb;
}

.wizard-nav {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  margin-top: 2rem;
  padding-top: 1rem;
  border-top: 1px solid #eee;
}

.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 1rem;
  transition: all 0.3s;
}

.btn-primary {
  background: #0ea5e9;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #0284c7;
}

.btn-secondary {
  background: #e5e7eb;
  color: #1f2937;
}

.btn-success {
  background: #10b981;
  color: white;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
```

#### Tests für Setup-Wizard

```php
// tests/Controller/SetupWizardControllerTest.php
class SetupWizardControllerTest extends TestCase {
    
    /**
     * Test: Musik-Verein Rollen werden initialisiert
     */
    public function testMusicClubRolesInitialization(): void {
        $response = $this->controller->initializeRoles(
            $this->createMockRequest(['club_type' => 'music'])
        );
        
        $this->assertEquals(['status' => 'ok'], $response->getData());
        
        // Verify 6 roles created for music club
        $roles = $this->roleService->getAllRoles();
        $this->assertCount(6, $roles);
        $this->assertContains('vorstand', array_column($roles, 'name'));
        $this->assertContains('kassierer', array_column($roles, 'name'));
        $this->assertContains('dirigent', array_column($roles, 'name'));
        $this->assertContains('notenwart', array_column($roles, 'name'));
    }
    
    /**
     * Test: Finanzmodule-Konfiguration wird gespeichert
     */
    public function testFinanceConfigurationSaved(): void {
        $request = $this->createMockRequest([
            'standard_fee' => 100.00,
            'reduced_fee' => 50.00,
            'creditor_id' => 'DE98ZZZ09999999999',
            'sepa_sequence' => '001'
        ]);
        
        $this->controller->configureFinance($request);
        
        $settings = $this->configService->getSettings('finance');
        $this->assertEquals(100.00, $settings['standard_fee']);
        $this->assertEquals('DE98ZZZ09999999999', $settings['creditor_id']);
    }
    
    /**
     * Test: Setup-Abschluss setzt Flag
     */
    public function testSetupCompletionFlag(): void {
        $this->controller->completeSetup($this->createMockRequest([]));
        
        $isCompleted = $this->configService->getConfig('setup_completed');
        $this->assertTrue($isCompleted);
    }
}
```

---

### 2️⃣ Dokumentvorlagen mit Briefkopf & Fußzeile

#### Ziel
Clubs können professionelle Dokumente (Rechnungen, Anschreiben, Protokolle) mit eigenem Branding generieren.

#### Komponenten

**1. Template-Management Interface**
```
┌─────────────────────────────────────┐
│  Dokumentvorlagen                   │
├─────────────────────────────────────┤
│                                     │
│  ☐ Rechnung                         │
│    [Bearbeiten] [Vorschau]          │
│                                     │
│  ☐ Anschreiben                      │
│    [Bearbeiten] [Vorschau]          │
│                                     │
│  ☐ Protokoll                        │
│    [Bearbeiten] [Vorschau]          │
│                                     │
│  ☐ Mahnschreiben                    │
│    [Bearbeiten] [Vorschau]          │
│                                     │
│  [+ Neue Vorlage erstellen]         │
└─────────────────────────────────────┘
```

**2. Template-Editor Dialog**
```
┌────────────────────────────────────────┐
│  Vorlage: Rechnung                     │
├────────────────────────────────────────┤
│                                        │
│  Briefkopf-Konfiguration:              │
│                                        │
│  Logo:           [Logo-Upload]  🖼️     │
│  Logo-Größe:     [Breite: 100mm]       │
│                                        │
│  Vereinsname:    [Musikverein Beispiel]│
│  Adresse:        [Straße & Hausnummer] │
│  Postleitzahl:   [12345]               │
│  Stadt:          [Beispielstadt]       │
│                                        │
│  Bankdaten:                            │
│  IBAN:           [DE89370400440532013000]│
│  BIC:            [COBADEFFXXX]         │
│                                        │
│  Vereinsregister:                      │
│  VR-Nummer:      [123456]              │
│  Finanzamt:      [Stuttgart]           │
│                                        │
│  ─────────────────────────────────────│
│                                        │
│  Fußzeile-Text:                        │
│  ┌─────────────────────────────────┐   │
│  │ Bankverbindung: [IBAN/BIC]      │   │
│  │ Vereinsregister: [VR-Number]    │   │
│  │ Vorstand: [Name1], [Name2]      │   │
│  └─────────────────────────────────┘   │
│                                        │
│          [Abbrechen]  [Speichern]     │
└────────────────────────────────────────┘
```

**3. Vorschau-Funktion**
```
Zeigt PDF-Vorschau mit:
  ✓ Logo-Platzierung
  ✓ Tabellenformatierung
  ✓ Fußzeile mit Bankdaten
  ✓ Zeilenumbrüche & Abstände
  ✓ Farben & Schriftarten
```

#### Database Schema

```sql
-- Dokumentvorlagen
CREATE TABLE document_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100),                          -- 'invoice', 'letter', 'protocol'
    club_type VARCHAR(50),                      -- 'music', 'sport', 'general'
    template_html LONGTEXT,                     -- HTML template with placeholders
    header_config JSON,                         -- Logo, address, bank data
    footer_config JSON,                         -- Footer text, styles
    is_default BOOLEAN DEFAULT false,           -- True = system template
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    created_by INT,
    FOREIGN KEY(created_by) REFERENCES oc_users(id)
);

-- Branding-Konfiguration
CREATE TABLE club_branding (
    id INT PRIMARY KEY AUTO_INCREMENT,
    club_id INT,
    logo_path VARCHAR(255),                     -- Path to uploaded logo
    club_name VARCHAR(255),
    address_street VARCHAR(255),
    address_city VARCHAR(100),
    address_postal_code VARCHAR(10),
    bank_iban VARCHAR(34),
    bank_bic VARCHAR(11),
    vr_number VARCHAR(50),                      -- Vereinsregister-Nummer
    vr_court VARCHAR(100),                      -- Amtsgericht
    tax_id VARCHAR(50),                         -- Steuernummer
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE(club_id)
);
```

#### Implementation

```php
// Service/DocumentTemplateService.php
namespace OCA\Verein\Service;

use OCA\Verein\Db\DocumentTemplate;
use OCA\Verein\Db\ClubBranding;

class DocumentTemplateService {
    
    /**
     * Generate PDF from template
     */
    public function generateDocument(
        string $templateName,
        array $data,
        ?ClubBranding $branding = null
    ): \TCPDF {
        
        $template = $this->getTemplate($templateName);
        $branding = $branding ?? $this->getDefaultBranding();
        
        $html = $this->renderTemplate($template, $data, $branding);
        
        return $this->htmlToPdf($html, $branding);
    }
    
    /**
     * Render template with data
     */
    private function renderTemplate(
        DocumentTemplate $template,
        array $data,
        ClubBranding $branding
    ): string {
        
        $html = $template->getTemplateHtml();
        
        // Replace placeholders
        $placeholders = [
            '{{ club_name }}' => $branding->getClubName(),
            '{{ address }}' => $branding->getFullAddress(),
            '{{ bank_iban }}' => $branding->getBankIban(),
            '{{ bank_bic }}' => $branding->getBankBic(),
            '{{ vr_number }}' => $branding->getVrNumber(),
            '{{ logo_path }}' => $this->getLogoUrl($branding),
        ];
        
        // Replace document-specific data
        foreach ($data as $key => $value) {
            $placeholders['{{ ' . $key . ' }}'] = $value;
        }
        
        return strtr($html, $placeholders);
    }
    
    /**
     * Convert HTML to PDF using TCPDF
     */
    private function htmlToPdf(string $html, ClubBranding $branding): \TCPDF {
        $pdf = new \TCPDF();
        
        // Set branding
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->AddPage();
        
        // Add header
        $this->addHeader($pdf, $branding);
        
        // Add content
        $pdf->writeHTML($html);
        
        // Add footer
        $this->addFooter($pdf, $branding);
        
        return $pdf;
    }
    
    private function addHeader(\TCPDF $pdf, ClubBranding $branding): void {
        $logoPath = $branding->getLogoPath();
        if ($logoPath && file_exists($logoPath)) {
            $pdf->Image($logoPath, 15, 10, 30);  // x, y, width
        }
        
        $pdf->SetFont('Helvetica', 'B', 12);
        $pdf->SetXY(50, 15);
        $pdf->Cell(0, 10, $branding->getClubName());
        
        $pdf->SetFont('Helvetica', '', 8);
        $pdf->SetXY(50, 25);
        $pdf->Cell(0, 5, $branding->getAddressStreet());
        $pdf->SetXY(50, 30);
        $pdf->Cell(0, 5, $branding->getAddressPostalCode() . ' ' . $branding->getAddressCity());
    }
    
    private function addFooter(\TCPDF $pdf, ClubBranding $branding): void {
        $pdf->SetY(-30);
        $pdf->SetFont('Helvetica', '', 7);
        $pdf->SetTextColor(100, 100, 100);
        
        $footerText = sprintf(
            "IBAN: %s | BIC: %s | VR: %s | Seite: %s",
            $branding->getBankIban(),
            $branding->getBankBic(),
            $branding->getVrNumber(),
            $pdf->PageNo()
        );
        
        $pdf->Cell(0, 10, $footerText, 0, false, 'C');
    }
}
```

#### Vue.js Template Editor

```vue
<!-- components/DocumentTemplateEditor.vue -->
<template>
  <div class="template-editor">
    <div class="editor-header">
      <h2>Dokumentvorlage: {{ template.name }}</h2>
      <div class="tabs">
        <button 
          v-for="tab in tabs" 
          :key="tab"
          :class="['tab', { active: activeTab === tab }]"
          @click="activeTab = tab"
        >
          {{ tab }}
        </button>
      </div>
    </div>
    
    <!-- Branding Tab -->
    <div v-if="activeTab === 'Branding'" class="tab-content">
      <div class="form-section">
        <h3>Logo & Briefkopf</h3>
        
        <div class="form-group">
          <label>Logo hochladen</label>
          <input type="file" accept="image/*" @change="uploadLogo">
          <div v-if="branding.logoPath" class="logo-preview">
            <img :src="branding.logoPath" alt="Logo">
            <button @click="removeLogo" class="btn-small">Entfernen</button>
          </div>
        </div>
        
        <div class="form-group">
          <label>Vereinsname</label>
          <input v-model="branding.clubName" type="text">
        </div>
        
        <div class="form-group">
          <label>Straße & Hausnummer</label>
          <input v-model="branding.addressStreet" type="text">
        </div>
        
        <div class="form-group">
          <label>Postleitzahl</label>
          <input v-model="branding.addressPostalCode" type="text" maxlength="10">
        </div>
        
        <div class="form-group">
          <label>Stadt</label>
          <input v-model="branding.addressCity" type="text">
        </div>
      </div>
      
      <div class="form-section">
        <h3>Bankdaten</h3>
        
        <div class="form-group">
          <label>IBAN</label>
          <input v-model="branding.bankIban" type="text" placeholder="DE...">
        </div>
        
        <div class="form-group">
          <label>BIC</label>
          <input v-model="branding.bankBic" type="text" placeholder="XXXX...">
        </div>
      </div>
      
      <div class="form-section">
        <h3>Vereinsregister</h3>
        
        <div class="form-group">
          <label>VR-Nummer</label>
          <input v-model="branding.vrNumber" type="text">
        </div>
        
        <div class="form-group">
          <label>Amtsgericht</label>
          <input v-model="branding.vrCourt" type="text">
        </div>
        
        <div class="form-group">
          <label>Steuernummer</label>
          <input v-model="branding.taxId" type="text">
        </div>
      </div>
    </div>
    
    <!-- Template Tab -->
    <div v-if="activeTab === 'Template'" class="tab-content">
      <div class="template-editor-area">
        <textarea 
          v-model="template.templateHtml" 
          class="template-textarea"
          placeholder="HTML-Template mit {{Platzhaltern}}"
        ></textarea>
        <div class="template-help">
          <strong>Verfügbare Platzhalter:</strong>
          <ul>
            <li>{{ club_name }} - Vereinsname</li>
            <li>{{ address }} - Vollständige Adresse</li>
            <li>{{ member_name }} - Mitgliedername</li>
            <li>{{ invoice_number }} - Rechnungsnummer</li>
            <li>{{ amount }} - Betrag</li>
            <li>{{ date }} - Aktuelles Datum</li>
          </ul>
        </div>
      </div>
    </div>
    
    <!-- Preview Tab -->
    <div v-if="activeTab === 'Vorschau'" class="tab-content">
      <div class="preview-container">
        <iframe 
          :src="previewUrl"
          class="pdf-preview"
          v-if="previewUrl"
        ></iframe>
        <div v-else class="preview-loading">
          <p>Vorschau wird generiert...</p>
        </div>
      </div>
    </div>
    
    <!-- Actions -->
    <div class="editor-actions">
      <button @click="cancel" class="btn btn-secondary">Abbrechen</button>
      <button @click="generatePreview" class="btn btn-secondary">Vorschau generieren</button>
      <button @click="save" class="btn btn-primary">Speichern</button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const activeTab = ref('Branding');
const previewUrl = ref(null);

const tabs = ['Branding', 'Template', 'Vorschau'];

const branding = ref({
  logoPath: null,
  clubName: '',
  addressStreet: '',
  addressPostalCode: '',
  addressCity: '',
  bankIban: '',
  bankBic: '',
  vrNumber: '',
  vrCourt: '',
  taxId: ''
});

const template = ref({
  name: 'invoice',
  templateHtml: '<p>Template HTML</p>'
});

const uploadLogo = async (event) => {
  const file = event.target.files[0];
  const formData = new FormData();
  formData.append('file', file);
  
  const response = await fetch('/apps/verein/api/v1/branding/upload-logo', {
    method: 'POST',
    body: formData
  });
  
  const data = await response.json();
  branding.value.logoPath = data.url;
};

const removeLogo = () => {
  branding.value.logoPath = null;
};

const generatePreview = async () => {
  const response = await fetch('/apps/verein/api/v1/templates/preview', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      template: template.value,
      branding: branding.value,
      testData: getTestData()
    })
  });
  
  const blob = await response.blob();
  previewUrl.value = URL.createObjectURL(blob);
};

const save = async () => {
  await fetch('/apps/verein/api/v1/templates', {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      template: template.value,
      branding: branding.value
    })
  });
  
  alert('Vorlage gespeichert!');
};

const cancel = () => {
  // Close dialog / Go back
};

const getTestData = () => {
  return {
    club_name: branding.value.clubName,
    address: `${branding.value.addressStreet}, ${branding.value.addressPostalCode} ${branding.value.addressCity}`,
    member_name: 'Max Mustermann',
    invoice_number: '2024-001',
    amount: '100,00 €',
    date: new Date().toLocaleDateString('de-DE')
  };
};
</script>

<style scoped>
.template-editor {
  max-width: 1000px;
  margin: 0 auto;
  padding: 2rem;
}

.editor-header {
  margin-bottom: 2rem;
}

.tabs {
  display: flex;
  gap: 0.5rem;
  margin-top: 1rem;
  border-bottom: 2px solid #e5e7eb;
}

.tab {
  padding: 0.75rem 1rem;
  background: none;
  border: none;
  cursor: pointer;
  border-bottom: 3px solid transparent;
  color: #6b7280;
}

.tab.active {
  color: #0ea5e9;
  border-bottom-color: #0ea5e9;
}

.tab-content {
  padding: 2rem 0;
}

.form-section {
  margin-bottom: 2rem;
}

.form-section h3 {
  font-size: 1.1rem;
  margin-bottom: 1rem;
  color: #1f2937;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  font-weight: 500;
  margin-bottom: 0.5rem;
  color: #374151;
}

.form-group input,
.form-group textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  font-size: 1rem;
}

.logo-preview {
  margin-top: 1rem;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.logo-preview img {
  max-height: 100px;
  max-width: 200px;
  border: 1px solid #d1d5db;
  border-radius: 4px;
}

.template-editor-area {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
}

.template-textarea {
  height: 400px;
  font-family: monospace;
  font-size: 0.9rem;
}

.template-help {
  padding: 1rem;
  background: #f9fafb;
  border-radius: 4px;
  border-left: 3px solid #0ea5e9;
}

.template-help strong {
  display: block;
  margin-bottom: 0.5rem;
}

.template-help ul {
  list-style-position: inside;
  font-family: monospace;
  font-size: 0.85rem;
}

.preview-container {
  background: white;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  overflow: hidden;
  height: 600px;
}

.pdf-preview {
  width: 100%;
  height: 100%;
  border: none;
}

.preview-loading {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  color: #6b7280;
}

.editor-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 2rem;
  padding-top: 1rem;
  border-top: 1px solid #e5e7eb;
}

.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 1rem;
}

.btn-primary {
  background: #0ea5e9;
  color: white;
}

.btn-secondary {
  background: #e5e7eb;
  color: #1f2937;
}

.btn-small {
  padding: 0.5rem 1rem;
  background: #ef4444;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.85rem;
}
</style>
```

#### Tests für Dokumentvorlagen

```php
// tests/Service/DocumentTemplateServiceTest.php
class DocumentTemplateServiceTest extends TestCase {
    
    /**
     * Test: PDF mit Header/Footer generieren
     */
    public function testGeneratePdfWithHeaderFooter(): void {
        $template = $this->createTemplate('invoice');
        $branding = $this->createBranding([
            'club_name' => 'Musikverein Beispiel',
            'bank_iban' => 'DE89370400440532013000',
            'bank_bic' => 'COBADEFFXXX'
        ]);
        
        $pdf = $this->service->generateDocument('invoice', ['amount' => '100,00€'], $branding);
        
        // Verify PDF content
        $content = $pdf->Output('', 'S');
        $this->assertStringContainsString('Musikverein Beispiel', $content);
        $this->assertStringContainsString('COBADEFFXXX', $content);
    }
    
    /**
     * Test: Logo wird in PDF eingebettet
     */
    public function testLogoEmbeddedInPdf(): void {
        $template = $this->createTemplate('invoice');
        $logoPath = $this->uploadTestLogo();
        $branding = $this->createBranding(['logoPath' => $logoPath]);
        
        $pdf = $this->service->generateDocument('invoice', [], $branding);
        $content = $pdf->Output('', 'S');
        
        // Verify logo is embedded
        $this->assertStringContainsString('xref', $content);  // PDF structure
    }
    
    /**
     * Test: Lange Texte umbrechen korrekt
     */
    public function testLongTextWrapping(): void {
        $template = $this->createTemplate('letter');
        $longText = str_repeat('Lorem ipsum dolor sit amet ', 50);
        
        $pdf = $this->service->generateDocument('letter', [
            'content' => $longText
        ]);
        
        // Verify no overflow
        $lines = $pdf->getNumberOfPages();
        $this->assertGreaterThan(1, $lines);  // Should span multiple pages
    }
}
```

---

## 🎨 v0.5.0+ (Geplant - Q3 2026+)

### Zukünftige Features (Roadmap Backlog)

#### 1. Custom Permissions (Admin-definierbar)
- Admins definieren Custom Permission Sets
- Dynamische Rollenerstellung
- Granulare Berechtigungsmodelle
- Wildcard-Support (`finance:*`, `export:*`)
- Tests: 15+ Szenarien

#### 2. Audit Logs (Compliance & Sicherheit)
- Alle Operationen protokollieren: Wer? Was? Wann?
- Unveränderliche Protokolle (Write-Once)
- Export für Audits & Compliance
- Retention-Policies (z.B. 7 Jahre)
- Tests: 10+ Szenarien

#### 3. GDPR Compliance
- Data Export (DSGVO Art. 20)
- Right to Be Forgotten (Art. 17)
- Data Subject Access Request (DSAR)
- Automatische Daten-Retention
- Tests: 15+ Szenarien

#### 4. Advanced Features (Community-basiert)
- [ ] Mobile App (iOS/Android via React Native)
- [ ] Multi-Language Support (i18n)
- [ ] Member Self-Service Portal
- [ ] SMS/Email Notifications
- [ ] Payment Integration (Stripe, PayPal)
- [ ] Advanced Reporting & Analytics
- [ ] AI-powered Insights (ML-based anomaly detection)

---

## 🎁 Community Features (Backlog)

Geplant, aber zeitlich nicht gebunden:

- [ ] Mobile App (iOS/Android)
- [ ] Multi-Language Support
- [ ] Member Portal (Self-Service)
- [ ] SMS Notifications
- [ ] Payment Gateway Integration (Stripe, PayPal)
- [ ] Advanced Reporting
- [ ] AI-powered Insights

---

## 🤝 How to Contribute

Du möchtest an dieser Roadmap mitarbeiten?

1. **Feature vorschlagen**: [GitHub Discussions](https://github.com/yourusername/nextcloud-verein/discussions)
2. **Bug melden**: [GitHub Issues](https://github.com/yourusername/nextcloud-verein/issues)
3. **Code beitragen**: [Pull Requests](https://github.com/yourusername/nextcloud-verein/pulls)
4. **Testen**: Download & Feedback geben

---

## 📅 Timeline

```
2025
├── Nov 16: v0.1.0 Stable (CURRENT)
│   ├─ Basis CRUD Features
│   ├─ Vue.js 3 Frontend
│   ├─ 35+ Unit Tests
│   └─ Documentation Suite (3.300+ Zeilen)
│
└── Dez 1-25: v0.2.0 Development (INCOMING)
    ├─ v0.2.0-beta (1. Dez)
    │  ├─ Multi-Role RBAC Complete
    │  ├─ Input Validierung
    │  ├─ 50+ New Tests
    │  └─ 0 Build Errors
    │
    ├─ v0.2.0-rc (15. Dez)
    │  ├─ SEPA XML Export
    │  ├─ PDF Export
    │  ├─ Error Handling Complete
    │  └─ Integration Tests
    │
    └─ v0.2.0 Production (25. Dez)
       ├─ All Features Stable
       ├─ 85%+ Test Coverage
       ├─ Security Review Complete
       └─ Ready for Production

2026
├── Q1 (Jan-Mar): v0.3.0 Development
│   ├─ Score Management (NEW!)
│   │  ├─ Upload & Permissions
│   │  ├─ Instrument-based Access
│   │  └─ 20+ New Tests
│   │
│   ├─ Softnote Import (NEW!)
│   │  ├─ CSV/XML Parser
│   │  ├─ Data Validation
│   │  └─ 15+ New Tests
│   │
│   ├─ Automatische Mahnungen
│   ├─ Kalender Integration
│   └─ v0.3.0 Release (31. Mar)
│
├── Q2: Community Phase & Bug Fixes
│   └─ Community Contributions
│
├── Q3: v0.3.1+ Features
│   ├─ Custom Permissions
│   ├─ Audit Logs
│   └─ GDPR Compliance
│
└── Q4: v1.0.0 Production Release
    ├─ 100% Test Coverage
    ├─ Third-Party Security Audit
    └─ App Store Release
```

---

## 💡 Vision

**Langfristig**: Nextcloud Vereins-App soll die **Standard-Lösung** für Vereinsverwaltung in Nextcloud sein – mit modernem UI, stabiler API und aktiver Community.

**Mittelfristig**: Features, die große Vereine brauchen (Automatisierung, Reporting, Integrations).

**Kurzfristig**: Stabilität, Berechtigungen, gute Dokumentation.

---

## 📞 Feedback

Meinung zu dieser Roadmap?

- 💬 [GitHub Discussions](https://github.com/yourusername/nextcloud-verein/discussions)
- 📧 Email: (Deine Kontaktadresse)
- 🐦 Twitter/X: @yourusername

---

**Danke für dein Interesse an der Nextcloud Vereins-App!** 🎉

Ich freue mich, mit dir zusammen die beste Vereinsverwaltungs-App zu bauen! 🚀

*Entwickelt mit ❤️ von Stefan Schulz*
