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

## 📋 v0.3.0 (Q2 2026)

### 🎯 Fokus: Automatisierung & Integrationen

**Geplante Features:**
- [ ] Automatische Mahnungen
  - Cronjob für Beiträge
  - E-Mail Benachrichtigungen
  - Mahnstufen (1., 2., Mahnung)
- [ ] Kalender Integration
  - Mitgliederverwaltung im Kalender
  - Gebühren-Fristen als Events
- [ ] Deck Integration
  - Aufgaben-Management
  - Beitragsabrechnung
- [ ] Direktnachrichten (Talk)
  - Benachrichtigungen via Chat
  - Admin-Alerts

### 🔐 Erweiterte Rollen & Sicherheit
- [ ] Custom Permissions (Admin definierbar)
- [ ] Audit Logs (Wer ändert was, wann)
- [ ] Datenschutz (GDPR)
  - Data Export für Mitglieder
  - Datenretention Policies
  - Right to be Forgotten

### 💾 Data Export & Reporting
- [ ] PDF Export (mit erweiterten Layouts)
  - Gebührenlisten pro Mitglied
  - Jahresabschlüsse
  - Statistik-Reports
- [ ] SEPA XML (Bankentransfers)
  - Direct Debit Setup
  - Payment Tracking

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

### v0.2.0 (PLANNED)
| Issue | Severity | Status | ETA |
|-------|----------|--------|-----|
| Multi-Role RBAC nicht implementiert | 🔴 Kritisch | In Development | ✅ v0.2.0-beta (1. Dez) |
| SEPA/PDF Export fehlt | 🟡 Medium | Geplant | ✅ v0.2.0-rc (15. Dez) |
| Datenvalidierung unvollständig | 🟡 Medium | Teilweise | ✅ v0.2.0-beta (1. Dez) |
| Audit Logs fehlen | 🟢 Low | Backlog | v1.0.0 |

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
- **v0.2.0**: 50+ Installationen
- **v0.3.0**: 200+ Installationen
- **v1.0.0**: 500+ Installationen (Ziel)

### Quality Goals
| Metrik | v0.1 | v0.2 | v0.3 | v1.0 |
|--------|------|------|------|------|
| Test Coverage | 0% | 50% | 80% | 100% |
| Bug Response | - | <7 days | <3 days | <1 day |
| Performance | - | < 2s | < 1s | < 500ms |

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
    │  ├─ Input Validierung (IBAN, Email, Phone)
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
├── Q1: Feature Development & Community
├── Q2: v0.3.0 (Automation, Integrations)
├── Q3: Bug Fixes & Optimization
└── Q4: v1.0.0 Production Release
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
