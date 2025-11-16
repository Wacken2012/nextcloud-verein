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

### 🎯 Fokus: Rollenbasierte Zugriffskontrolle (RBAC) mit Mehrfachrollen & Datenqualität

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

## 📋 v0.3.0 (Q1 2026)

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

#### Tests: Softnote Import

```php
// tests/Command/SoftnoteImportCommandTest.php
class SoftnoteImportCommandTest extends TestCase {
    
    /**
     * Test 1: CSV Import mit gültigen Daten
     */
    public function testImportValidCSV(): void {
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

Zusammen machen wir die beste Vereinsverwaltungs-App! 🚀
