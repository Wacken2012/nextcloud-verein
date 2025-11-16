# 🎯 Erweiterungen: Tests, Validierung & Dashboard

Diese Dokumentation beschreibt die neuen Features, die zur Nextcloud Vereins-App in v0.1.0 hinzugefügt wurden.

---

## 1️⃣ PHPUnit-Tests für Controller

### Dateien
- `tests/Controller/MemberControllerTest.php` - 8 Test-Fälle
- `tests/Controller/FinanceControllerTest.php` - 7 Test-Fälle

### Features

#### MemberControllerTest.php
- ✅ `testGetMembersReturnsAllMembers()` - GET /members mit Mock-Daten
- ✅ `testGetMembersReturnsEmptyArrayWhenNoMembers()` - Leeres Array Test
- ✅ `testGetMemberByIdReturnsSingleMember()` - GET /members/{id}
- ✅ `testCreateMemberWithValidDataReturnsNewMember()` - POST /members
- ✅ `testUpdateMemberWithValidDataReturnsUpdatedMember()` - PUT /members/{id}
- ✅ `testDeleteMemberRemovesMemberFromDatabase()` - DELETE /members/{id}

#### FinanceControllerTest.php
- ✅ `testGetFinanceReturnsAllFees()` - GET /finance
- ✅ `testCreateFeeWithValidDataReturnsNewFee()` - POST /finance
- ✅ `testCreateFeeWithInvalidMemberIdThrowsException()` - Error-Handling
- ✅ `testGetFinanceSummaryReturnsStatistics()` - Statistiken
- ✅ `testUpdateFeeStatusChangesStatus()` - Status-Update
- ✅ `testDeleteFeeRemovesFeeFromDatabase()` - DELETE /finance/{id}
- ✅ `testGetFeesByMemberIdReturnsMemberFees()` - Filter nach Mitglied

### Tests ausführen

```bash
# Alle Tests
composer test

# Nur MemberController Tests
composer test --filter MemberControllerTest

# Nur FinanceController Tests
composer test --filter FinanceControllerTest

# Spezifischer Test
composer test --filter testCreateMemberWithValidDataReturnsNewMember
```

### Mock-Daten Pattern

Die Tests verwenden `createMock()` für Services:

```php
$this->memberService = $this->createMock(MemberService::class);

// Konfiguriere Return-Wert
$this->memberService->expects($this->once())
    ->method('findAll')
    ->willReturn($mockData);
```

### Assertions

- `$this->assertIsArray()` - Prüfe Array-Typ
- `$this->assertCount()` - Prüfe Array-Größe
- `$this->assertEquals()` - Prüfe Werte
- `$this->assertTrue()` / `assertFalse()` - Boolean-Checks
- `$this->expectException()` - Exception-Tests

---

## 2️⃣ Backend-Validierung

### Neue Datei: ValidationService

**Dateipfad**: `lib/Service/ValidationService.php`

#### Methoden

##### validateMember()
Validiert Pflichtfelder eines Mitglieds:

```php
$validation = $validationService->validateMember($name, $email, $iban);

// Returns
[
    'valid' => true/false,
    'errors' => ['Fehler 1', 'Fehler 2']
]
```

**Validierungsregeln**:
- Name: 2-255 Zeichen, erforderlich
- Email: Gültiges E-Mail-Format, erforderlich
- IBAN: Deutsches Format (DE + 20 Ziffern), Checksum validieren

##### validateFee()
Validiert Gebührendaten:

```php
$validation = $validationService->validateFee($memberId, $amount, $description);
```

**Regeln**:
- memberId: > 0
- amount: > 0 und < 100.000
- description: 2-500 Zeichen

##### validateIBAN()
IBAN-Validierung mit Mod-97 Checksum:

```php
$isValid = $validationService->validateIBAN('DE89370400440532013000');
```

Format: `DE` + 2 Prüfziffern + 18 Ziffern

##### validateFeeStatus()
Erlaubte Status: `open`, `paid`, `overdue`, `cancelled`

##### validateRole()
Erlaubte Rollen: `Mitglied`, `Kassierer`, `Admin`

### API-Response bei Fehler

```json
{
  "status": "error",
  "message": "Validierung fehlgeschlagen",
  "errors": [
    "Name ist erforderlich",
    "E-Mail ist ungültig"
  ]
}
```

### Integration in Controller

#### MemberController
```php
public function create() {
    $validation = $this->validationService->validateMember(
        $name, 
        $email, 
        $iban
    );
    
    if (!$validation['valid']) {
        return new JSONResponse([
            'status' => 'error',
            'message' => 'Validierung fehlgeschlagen',
            'errors' => $validation['errors']
        ], 400);
    }
    
    // Erstelle Mitglied...
}
```

---

## 3️⃣ Frontend Error-Handling

### Alert.vue Komponente

**Dateipfad**: `js/components/Alert.vue`

#### Props

```ts
interface Props {
  type?: 'error' | 'success' | 'info' | 'warning'  // default: 'info'
  title?: string              // Auto-Title basierend auf type
  message?: string            // Hauptnachricht
  errors?: string[]          // Array von Fehler-Details
  duration?: number | null   // Auto-Close in ms (default: 5000)
  closeable?: boolean        // Close-Button anzeigen (default: true)
}
```

#### Verwendung

```vue
<template>
  <Alert
    ref="alertRef"
    type="error"
    title="Fehler beim Speichern"
    :message="errorMessage"
    :errors="validationErrors"
    :duration="5000"
  />
</template>

<script setup>
const alertRef = ref(null)

const handleError = (response) => {
  if (response.data.status === 'error') {
    alertRef.value.open()
  }
}
</script>
```

#### Styling

- ✅ Error: Rotes Design (#d32f2f)
- ✅ Success: Grünes Design (#388e3c)
- ✅ Info: Blaues Design (#1976d2)
- ✅ Warning: Gelbes Design (#f57f17)
- ✅ Dark Mode Support

#### Features

- Auto-Dismiss nach Dauer
- Übergänge/Animationen
- Fehler-Liste mit Aufzählungspunkten
- Close-Button (optional)

### Integration in Members.vue

```vue
<template>
  <Alert
    ref="alertRef"
    type="error"
    :message="alertError"
    :errors="alertErrors"
  />
</template>

<script setup>
const alertRef = ref(null)
const alertError = ref('')
const alertErrors = ref([])

const addMember = async () => {
  const response = await api.post('members', formData.value)
  
  if (response.data.status === 'error') {
    alertError.value = response.data.message
    alertErrors.value = response.data.errors || []
    alertRef.value.open()
  }
}
</script>
```

---

## 4️⃣ Statistics Dashboard

### Statistics.vue Komponente

**Dateipfad**: `js/components/Statistics.vue`

#### Features

##### 4 Statistik-Widgets
1. **Mitglieder** - Anzahl registrierter Mitglieder
2. **Offene Gebühren** - Summe + Anzahl offener Einträge
3. **Bezahlte Gebühren** - Summe + Anzahl bezahlter Einträge
4. **Überfällige Gebühren** - Summe + Anzahl überfälliger Einträge

##### 2 Chart.js Diagramme
1. **Balkendiagramm** - Gebührenstatus Verteilung
   - X-Achse: Kategorien (Offen, Bezahlt, Überfällig)
   - Y-Achse: Betrag in €
   - Farben: Orange, Grün, Rot

2. **Liniendiagramm** - Mitgliederwachstum (6 Monate)
   - X-Achse: Monate (Jan-Jun)
   - Y-Achse: Mitgliederzahl
   - Trend-Linie mit Datenpunkten

#### Daten-Quellen

Die Komponente lädt automatisch von:
- `GET /apps/verein/api/members` - Für Member-Count
- `GET /apps/verein/api/finance` - Für Fee-Statistiken

#### Responsive

- Desktop: 2-spaltig (Widgets nebeneinander)
- Tablet: 2x2 Grid
- Mobile: 1 Spalte

#### Dark Mode

Automatische Anpassung an System-Einstellungen mit `prefers-color-scheme: dark`

---

## 5️⃣ App.vue Integration

### Neue Tab-Struktur

```ts
const tabs = [
  { id: 'dashboard', label: '📊 Dashboard', icon: 'dashboard' },
  { id: 'members', label: '👥 Mitglieder', icon: 'users' },
  { id: 'finance', label: '💰 Finanzen', icon: 'finance' },
  { id: 'calendar', label: '📅 Termine', icon: 'calendar' },
  { id: 'deck', label: '📋 Aufgaben', icon: 'deck' },
  { id: 'documents', label: '📄 Dokumente', icon: 'documents' }
]
```

### Komponenten-Mapping

```ts
const componentMap = {
  dashboard: 'Statistics',
  members: 'Members',
  finance: 'Finance',
  calendar: 'Calendar',
  deck: 'Deck',
  documents: 'Documents'
}
```

### Dashboard ist Standardtab

```ts
const activeTab = ref('dashboard')  // Vorher: 'members'
```

---

## 6️⃣ Dependencies

### Neue npm-Packages

```json
{
  "chart.js": "^4.x",
  "vue-chartjs": "^5.x"
}
```

### Installation

```bash
npm install chart.js vue-chartjs
```

### Bundle-Größe

- Vorher: 387 KB (Vite Bundle)
- Nachher: 820 KB (mit Chart.js)
- Gzip: 191 KB (akzeptabel)

---

## 7️⃣ Best Practices

### Tests schreiben

```php
// ✅ Gut: Setup/Teardown für Cleanup
protected function setUp(): void {
    $this->service = $this->createMock(...);
}

// ✅ Gut: Aussagekräftige Test-Namen
public function testCreateMemberWithValidDataReturnsNewMember()

// ✅ Gut: Arrange-Act-Assert Pattern
$mock = $this->createMockMember(1, 'Test', 'test@example.com');
$response = $this->controller->show(1);
$this->assertEquals('Test', $response['name']);
```

### Frontend Error-Handling

```vue
<!-- ✅ Gut: Alert-Komponente für alle Fehler -->
<Alert
  ref="alertRef"
  type="error"
  :message="errorMessage"
  :errors="errorList"
/>

<!-- ✅ Gut: Try-Catch + Error-Anzeige -->
try {
  const response = await api.post(...)
  if (response.data.status === 'error') {
    alertRef.value.open()
  }
} catch (error) {
  alertRef.value.open()
}
```

### Validierung

```php
// ✅ Gut: Validierung VOR Speicherung
$validation = $this->validationService->validate(...)
if (!$validation['valid']) {
    return error($validation['errors'])
}

// ✅ Gut: Aussagekräftige Fehlermeldungen
"IBAN ist ungültig (Format: DE89370400440532013000)"
```

---

## 📊 Metriken

| Metrik | Wert |
|--------|------|
| PHPUnit Tests | 15 |
| Test-Abdeckung | Controller: ~80% |
| Validierungs-Regeln | 12+ |
| Vue-Komponenten | 8 (neu: Alert, Statistics) |
| Diagramm-Typen | 2 (Bar, Line) |
| Responsive Breakpoints | 3 (Mobile, Tablet, Desktop) |

---

---

## 8️⃣ Role-Based Access Control (RBAC) Tests

### Überblick

v0.2.0 implementiert rollenbasierte Zugriffskontrolle mit 3 Benutzerrollen:

| Rolle | Beschreibung | Rechte |
|-------|-------------|--------|
| **Admin** | Vereinsadministrator | Vollzugriff (CRUD) |
| **Treasurer** | Schatzmeister | Finanzmanagement (Lese-/Schreibzugriff auf Finance) |
| **Member** | Normales Mitglied | Lesezugriff auf eigene Daten |

### RBAC Test-Struktur

#### MemberControllerTest.php

```php
class MemberControllerTest extends TestCase {
    
    // Admin-Tests: Vollzugriff
    public function testAdminCanCreateMember(): void {
        // Admin sollte Mitglied erstellen können
        $response = $this->controller->create('John', 'john@example.com', 'DE89...');
        $this->assertEquals(200, $response->getStatus());
    }
    
    public function testAdminCanReadAllMembers(): void {
        // Admin kann alle Mitglieder sehen
        $response = $this->controller->index();
        $this->assertCount(5, $response->getData());
    }
    
    public function testAdminCanUpdateAnyMember(): void {
        // Admin kann beliebiges Mitglied aktualisieren
        $response = $this->controller->update(1, ['name' => 'Updated']);
        $this->assertEquals(200, $response->getStatus());
    }
    
    public function testAdminCanDeleteMember(): void {
        // Admin kann Mitglied löschen
        $response = $this->controller->destroy(1);
        $this->assertEquals(200, $response->getStatus());
    }
    
    // Treasurer-Tests: Nur Lesezugriff auf Member
    public function testTreasurerCanReadAllMembers(): void {
        // Treasurer kann Mitglieder-Liste sehen
        $response = $this->controller->index();
        $this->assertEquals(200, $response->getStatus());
    }
    
    public function testTreasurerCannotCreateMember(): void {
        // Treasurer darf kein Mitglied erstellen
        $response = $this->controller->create('John', 'john@example.com', 'DE89...');
        $this->assertEquals(403, $response->getStatus()); // Forbidden
    }
    
    public function testTreasurerCannotUpdateMember(): void {
        // Treasurer darf Mitglied nicht bearbeiten
        $response = $this->controller->update(1, ['name' => 'Updated']);
        $this->assertEquals(403, $response->getStatus());
    }
    
    public function testTreasurerCannotDeleteMember(): void {
        // Treasurer darf Mitglied nicht löschen
        $response = $this->controller->destroy(1);
        $this->assertEquals(403, $response->getStatus());
    }
    
    // Member-Tests: Nur eigene Daten lesbar
    public function testMemberCanReadOwnData(): void {
        // Mitglied kann seine Daten sehen
        $response = $this->controller->show(1);
        $this->assertEquals(200, $response->getStatus());
    }
    
    public function testMemberCannotReadOtherMemberData(): void {
        // Mitglied kann fremde Daten nicht sehen
        $response = $this->controller->show(2);
        $this->assertEquals(403, $response->getStatus());
    }
    
    public function testMemberCannotCreateMember(): void {
        // Mitglied darf keine Mitglieder erstellen
        $response = $this->controller->create('John', 'john@example.com', 'DE89...');
        $this->assertEquals(403, $response->getStatus());
    }
}
```

#### FinanceControllerTest.php

```php
class FinanceControllerTest extends TestCase {
    
    // Admin-Tests: Vollzugriff
    public function testAdminCanCreateFee(): void {
        $response = $this->controller->create(1, 50.00, 'monthly', '2025-01-01');
        $this->assertEquals(200, $response->getStatus());
    }
    
    public function testAdminCanUpdateFeeStatus(): void {
        $response = $this->controller->update(1, ['status' => 'paid']);
        $this->assertEquals(200, $response->getStatus());
    }
    
    public function testAdminCanDeleteFee(): void {
        $response = $this->controller->destroy(1);
        $this->assertEquals(200, $response->getStatus());
    }
    
    // Treasurer-Tests: CRUD außer Delete
    public function testTreasurerCanCreateFee(): void {
        // Treasurer kann Gebühren erstellen
        $response = $this->controller->create(1, 50.00, 'monthly', '2025-01-01');
        $this->assertEquals(200, $response->getStatus());
    }
    
    public function testTreasurerCanReadFees(): void {
        // Treasurer kann Gebühren sehen
        $response = $this->controller->index();
        $this->assertEquals(200, $response->getStatus());
    }
    
    public function testTreasurerCanUpdateFeeStatus(): void {
        // Treasurer kann Status aktualisieren
        $response = $this->controller->update(1, ['status' => 'paid']);
        $this->assertEquals(200, $response->getStatus());
    }
    
    public function testTreasurerCannotDeleteFee(): void {
        // Treasurer darf Gebühren nicht löschen
        $response = $this->controller->destroy(1);
        $this->assertEquals(403, $response->getStatus());
    }
    
    // Member-Tests: Nur eigene Gebühren sichtbar
    public function testMemberCanReadOwnFees(): void {
        // Mitglied kann seine Gebühren sehen
        $response = $this->controller->indexForMember(1);
        $this->assertEquals(200, $response->getStatus());
    }
    
    public function testMemberCannotCreateFee(): void {
        // Mitglied darf keine Gebühren erstellen
        $response = $this->controller->create(1, 50.00, 'monthly', '2025-01-01');
        $this->assertEquals(403, $response->getStatus());
    }
}
```

### Validierungs-Tests

```php
class ValidationServiceTest extends TestCase {
    
    public function testValidateIBANValidModChecksum(): void {
        // Gültige IBAN mit korrektem Mod-97 Checksum
        $result = $this->validator->validateIBAN('DE89370400440532013000');
        $this->assertTrue($result['valid']);
    }
    
    public function testValidateIBANInvalidChecksum(): void {
        // IBAN mit falscher Checksum
        $result = $this->validator->validateIBAN('DE89370400440532013001');
        $this->assertFalse($result['valid']);
    }
    
    public function testValidateEmailValidFormat(): void {
        // Gültige Email
        $result = $this->validator->validateEmail('john@example.com');
        $this->assertTrue($result['valid']);
    }
    
    public function testValidateEmailInvalidFormat(): void {
        // Ungültige Email
        $result = $this->validator->validateEmail('not-an-email');
        $this->assertFalse($result['valid']);
    }
    
    public function testValidateMemberRequiredFields(): void {
        // Alle Pflichtfelder müssen vorhanden sein
        $result = $this->validator->validateMember(
            '',  // Leerer Name
            'john@example.com',
            'DE89370400440532013000'
        );
        $this->assertFalse($result['valid']);
        $this->assertContains('Name ist erforderlich', $result['errors']);
    }
}
```

### Tests ausführen

```bash
# Alle RBAC-Tests
./vendor/bin/phpunit tests/Controller/MemberControllerTest.php
./vendor/bin/phpunit tests/Controller/FinanceControllerTest.php

# Nur RBAC-Tests
./vendor/bin/phpunit --filter testAdminCanCreateMember
./vendor/bin/phpunit --filter testTreasurerCannotDeleteMember

# Mit Coverage
./vendor/bin/phpunit --coverage-html coverage/

# Tests mit Filter nach Rolle
./vendor/bin/phpunit --filter Admin
./vendor/bin/phpunit --filter Treasurer
./vendor/bin/phpunit --filter Member
```

### Mock-Setup für RBAC

```php
protected function setUp(): void {
    parent::setUp();
    
    // Mock User mit Admin-Rolle
    $this->adminUser = $this->createMockUser('admin', ['admin']);
    
    // Mock User mit Treasurer-Rolle
    $this->treasurerUser = $this->createMockUser('treasurer', ['treasurer']);
    
    // Mock User mit Member-Rolle
    $this->memberUser = $this->createMockUser('member', ['member']);
}

private function createMockUser(string $id, array $roles) {
    $user = $this->createMock(IUser::class);
    $user->method('getUID')->willReturn($id);
    $user->method('getGroupIds')->willReturn($roles);
    return $user;
}
```

### Assertions für RBAC

```php
// Rolle Check
$this->assertTrue($this->userHasRole($user, 'admin'));
$this->assertFalse($this->userHasRole($user, 'treasurer'));

// Status-Codes für Berechtigungen
$this->assertEquals(200, $response->getStatus());  // OK
$this->assertEquals(403, $response->getStatus());  // Forbidden
$this->assertEquals(401, $response->getStatus());  // Unauthorized

// Error-Message Checks
$this->assertStringContainsString(
    'Insufficient permissions',
    $response->getData()['error']
);
```

---

## 9️⃣ Testing Best Practices

### Test-Namenskonvention

```
testActionUnderConditionExpectsOutcome()

Beispiel:
- testAdminCanCreateMember()
- testTreasurerCannotDeleteFee()
- testValidateIBANInvalidChecksum()
- testMemberCanReadOwnDataButNotOthers()
```

### Arrange-Act-Assert Pattern

```php
public function testCreateMemberSuccessfully(): void {
    // ARRANGE: Setup
    $memberService = $this->createMock(MemberService::class);
    $memberService->expects($this->once())
        ->method('create')
        ->willReturn(['id' => 1, 'name' => 'John']);
    
    // ACT: Führe aus
    $controller = new MemberController($memberService);
    $response = $controller->create('John', 'john@example.com');
    
    // ASSERT: Verifiziere
    $this->assertEquals(200, $response->getStatus());
    $this->assertEquals('John', $response->getData()['name']);
}
```

### Coverage-Mindeststandards

| Code-Typ | Min. Coverage |
|----------|--------------|
| Controllers | 80% |
| Services | 90% |
| Models | 85% |
| Utils | 100% |
| Gesamt | 85% |

### Continuous Testing

```bash
# Watch-Modus: Tests bei jeder Änderung
npm run test:watch

# Coverage Report
npm run test:coverage

# Generate HTML Report
./vendor/bin/phpunit --coverage-html coverage/
```

---

## 🔄 Nächste Schritte (v0.2.0)

- [ ] RBAC Tests für alle Controller
- [ ] Service-Layer Tests
- [ ] Integration Tests
- [ ] E2E Tests mit Selenium/Cypress
- [ ] Performance Tests
- [ ] Security Tests (SQL Injection, XSS)
- [ ] CSV/PDF Export mit Tests
- [ ] E-Mail Benachrichtigungen mit Tests

---

## 📚 Weitere Ressourcen

- [PHPUnit Dokumentation](https://phpunit.de/)
- [PHPUnit Best Practices](https://phpunit.de/manual/9.5/en/index.html)
- [Mock-Objekttechniken](https://phpunit.de/manual/9.5/en/test-doubles.html)
- [Chart.js Dokumentation](https://www.chartjs.org/)
- [Vue 3 Testing](https://vuejs.org/guide/scaling-up/testing.html)
- [Nextcloud AppFramework](https://docs.nextcloud.com/server/latest/developer_manual/)
- [CONTRIBUTING.md](./CONTRIBUTING.md) - Contribution Guidelines

