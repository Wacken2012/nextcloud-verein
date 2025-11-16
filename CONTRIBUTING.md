# 🤝 Contributing Guidelines

Vielen Dank dass du zur Nextcloud Vereins-App beitragen möchtest! Diese Richtlinien helfen uns, Qualität und Konsistenz zu bewahren.

---

## 📋 Inhaltsverzeichnis

1. [Code of Conduct](#code-of-conduct)
2. [Wie kann ich beitragen?](#wie-kann-ich-beitragen)
3. [Entwicklungssetup](#entwicklungssetup)
4. [Code-Standards](#code-standards)
5. [Testing Guidelines](#testing-guidelines)
6. [Validierung & Error Handling](#validierung--error-handling)
7. [Commit Message Format](#commit-message-format)
8. [Pull Request Prozess](#pull-request-prozess)

---

## 📜 Code of Conduct

Ich verpflichte mich auf einen respektvollen und inklusiven Entwicklungsprozess.

**Bitte beachte:**
- Sei respektvoll gegenüber anderen Beitragenden
- Gib konstruktives Feedback
- Respektiere unterschiedliche Meinungen
- Melde problematisches Verhalten an: [github-issue-link]

---

## 🎯 Wie kann ich beitragen?

### Branch-Workflow

**Wichtig:** Bitte öffne Pull Requests gegen den **`develop`** Branch!

```
main (v0.1.0 - Stabil)
  ↑
  └─ develop (v0.2.0-beta - Features)
       ├─ feature/rbac
       ├─ feature/pdf-export
       ├─ feature/sepa-export
       └─ feature/error-handling
```

**Branch-Konvention**:
- `main` → Stabile Production-Version
- `develop` → Aktuelle Feature-Entwicklung
- `feature/*` → Neue Features (basierend auf `develop`)
- `bugfix/*` → Bugfixes (basierend auf `develop`)

### Bug Reports
```
1. Prüfe ob Bug bereits existiert (GitHub Issues)
2. Erstelle neue Issue mit:
   - Klare Beschreibung
   - Steps to Reproduce
   - Erwartetes vs. Aktuelles Verhalten
   - Screenshots (wenn UI-Bug)
   - System-Info (Browser, OS, Nextcloud-Version)
```

### Feature Requests
```
1. Öffne GitHub Discussion
2. Beschreibe die Feature mit:
   - Anwendungsfall/Problem das gelöst wird
   - Gewünschte Lösung
   - Alternative Lösungen
3. Warte auf Community Feedback
```

### Code Contributions
```
1. Fork das Repository
2. Erstelle Feature-Branch: git checkout -b feature/meine-feature
3. Implement, test, commit
4. Push und Create Pull Request
5. Code Review abwarten
```

---

## 🛠️ Entwicklungssetup

### Installation

```bash
# Repository klonen
git clone https://github.com/Wacken2012/nextcloud-verein.git
cd nextcloud-verein

# Dependencies installieren
npm install
composer install

# Symlink erstellen (optional)
ln -s $(pwd) /var/www/nextcloud/apps/verein-dev

# App aktivieren
sudo -u www-data php /var/www/nextcloud/occ app:enable verein-dev
```

### Development Server

```bash
# Watch-Modus (Auto-Rebuild bei Änderungen)
npm run dev

# Einmalig bauen
npm run build

# Linting prüfen
npm run lint
npm run lint:fix
```

### Testing

```bash
# Alle Tests ausführen
npm run test

# Nur Unit-Tests
npm run test:unit

# Nur E2E-Tests
npm run test:e2e

# Mit Coverage
npm run test:coverage

# PHP-Tests
phpunit tests/
```

---

## 📝 Code-Standards

### JavaScript/Vue.js

**Formatierung:**
```bash
npm run lint:fix  # Auto-fix linting issues
```

**Best Practices:**
```javascript
// ✅ Gut: Descriptive names, clear intent
export default {
  name: 'MemberCard',
  props: {
    member: {
      type: Object,
      required: true,
      validator: (obj) => obj.id && obj.name
    }
  },
  emits: ['update', 'delete'],
  setup(props, { emit }) {
    const handleUpdate = async (data) => {
      try {
        // Implementation
        emit('update', data)
      } catch (error) {
        console.error('Update failed:', error)
      }
    }
    return { handleUpdate }
  }
}

// ❌ Schlecht: Kryptische Namen, keine Error Handling
export default {
  name: 'MC',
  props: ['m'],
  setup(p) {
    const upd = async (d) => {
      emit('u', d)
    }
    return { upd }
  }
}
```

### PHP / Backend

**PSR-12 Standard:**
```php
<?php
declare(strict_types=1);

namespace OCA\Verein\Controller;

use OCP\AppFramework\Controller;
use OCP\IRequest;

/**
 * MemberController
 * 
 * Dokumentiere den Zweck der Klasse
 */
class MemberController extends Controller {
    
    public function __construct(
        string $appName,
        IRequest $request,
        private MemberService $memberService,
    ) {
        parent::__construct($appName, $request);
    }
    
    /**
     * Get all members
     * 
     * @return DataResponse
     */
    public function index(): DataResponse {
        try {
            $members = $this->memberService->findAll();
            return new DataResponse($members);
        } catch (\Exception $e) {
            return new DataResponse(
                ['error' => 'Failed to fetch members'],
                Http::STATUS_INTERNAL_SERVER_ERROR
            );
        }
    }
}
```

**Standards:**
- PSR-12 Code Style
- Type Hints verwenden
- DocBlocks für Public Methods
- Aussagekräftige Variable-Namen
- Deutsche/Englische Comments (konsistent)

---

## 🧪 Testing Guidelines

### Unit Tests (JavaScript)

```javascript
// ✅ Gut
describe('MemberCard', () => {
  it('renders member name', () => {
    const member = { id: 1, name: 'John Doe' }
    const wrapper = mount(MemberCard, { props: { member } })
    expect(wrapper.text()).toContain('John Doe')
  })
  
  it('emits update event when save clicked', async () => {
    const wrapper = mount(MemberCard, { props: { member } })
    await wrapper.find('button.save').trigger('click')
    expect(wrapper.emitted('update')).toBeTruthy()
  })
})

// ❌ Schlecht
describe('Component', () => {
  it('works', () => {
    const w = mount(Component)
    expect(w).toBeTruthy()
  })
})
```

### PHPUnit Tests

```php
class MemberControllerTest extends TestCase {
    public function testAdminCanCreateMember(): void {
        // Arrange: Setup
        $memberService = $this->createMock(MemberService::class);
        $controller = new MemberController('verein', $memberService);
        
        $memberService->expects($this->once())
            ->method('create')
            ->with('John', 'john@example.com')
            ->willReturn(['id' => 1, 'name' => 'John']);
        
        // Act: Führe Operation aus
        $result = $controller->create('John', 'john@example.com');
        
        // Assert: Prüfe Ergebnis
        $this->assertEquals(200, $result->getStatus());
        $this->assertEquals(1, $result->getData()['id']);
    }
}
```

**Test-Coverage Ziele:**
- ✅ Neue Features: mindestens 80% Coverage
- ✅ Bug Fixes: Test der Reproduktion + Fix
- ✅ Public APIs: 100% Coverage
- ✅ Edge Cases: Tests für Fehlerfälle

---

## ✅ Validierung & Error Handling

### Backend-Validierung (ValidationService.php)

```php
// Verwendung im Controller:
$validationService = new ValidationService();
$validation = $validationService->validateMember([
    'name' => $request->getParam('name'),
    'email' => $request->getParam('email'),
    'iban' => $request->getParam('iban')
]);

if (!$validation['valid']) {
    return new DataResponse([
        'status' => 'error',
        'errors' => $validation['errors'],
        'message' => $validationService->getErrorMessage($validation['errors'])
    ], Http::STATUS_BAD_REQUEST);
}
```

**Validierte Felder:**
- ✅ Email: RFC 5322 Format
- ✅ IBAN: Mod-97-Checksum (ISO 13616)
- ✅ Telefon: Längenprüfung (7-15 Ziffern)
- ✅ Datum: Format + gültiges Datum
- ✅ Pflichtfelder: Name, Email, IBAN

### Frontend-Error Handling (Alert.vue)

```vue
<template>
  <div>
    <Alert
      v-if="alert.show"
      :type="alert.type"
      :title="alert.title"
      :message="alert.message"
      :errors="alert.errors"
      :duration="5000"
    />
  </div>
</template>

<script setup lang="ts">
import { reactive } from 'vue'

const alert = reactive({
  show: false,
  type: 'info',
  title: '',
  message: '',
  errors: []
})

const showError = (title, errors = [], message = '') => {
  alert.show = true
  alert.type = 'error'
  alert.title = title
  alert.message = message
  alert.errors = errors
}

const showSuccess = (title, message = '') => {
  alert.show = true
  alert.type = 'success'
  alert.title = title
  alert.message = message
  alert.errors = []
}

// Verwendung:
try {
  await memberService.create(formData)
  showSuccess('Mitglied erstellt', 'Das Mitglied wurde erfolgreich hinzugefügt')
} catch (error) {
  if (error.response?.data?.errors) {
    showError('Eingabefehler', error.response.data.errors)
  } else {
    showError('Fehler', [], error.message)
  }
}
</script>
```

---

## 📌 Commit Message Format

Ich verwende **Conventional Commits**:

```
<type>(<scope>): <subject>

<body>

<footer>
```

**Types:**
- `feat`: Neue Funktionalität
- `fix`: Bug-Behebung
- `refactor`: Code-Umstrukturierung
- `perf`: Performance-Verbesserung
- `test`: Tests hinzufügen/aktualisieren
- `docs`: Dokumentation
- `style`: Formatierung
- `ci`: CI/CD Änderungen

**Beispiele:**

```bash
# Feature
git commit -m "feat(member): Add IBAN validation

- Implement Mod-97-Checksum validation
- Add country-specific IBAN length checks
- Add unit tests for edge cases"

# Bug Fix
git commit -m "fix(alert): Dark-mode colors not applying

Fixed CSS variable fallbacks in dark mode
Fixes #123"

# Refactor
git commit -m "refactor(validation): Extract email validation to utility"

# Documentation
git commit -m "docs: Update CONTRIBUTING.md with testing guidelines"
```

---

## 🔄 Pull Request Prozess

### 1. Vor dem PR

```bash
# Ensure local branch is up to date
git fetch origin
git rebase origin/develop    # ⚠️ rebase on develop, not main!

# Run all tests
npm run test
npm run test:coverage

# Run linting
npm run lint

# Build
npm run build
```

### 2. PR erstellen (Target: develop)

**⚠️ WICHTIG**: Stelle sicher, dass `develop` als Target ausgewählt ist!

**Title:**
```
feat: Add IBAN validation to member creation
```

**Description:**
```markdown
## What
Implement IBAN validation with Mod-97-Checksum

## Why
Improves data quality and prevents invalid payment information

## Branch Info
- Base: `develop` (v0.2.0-beta)
- Feature for: v0.2.0 release (Dez 2025)

## How
- Created ValidationService with IBAN validation method
- Added unit tests for edge cases
- Updated MemberController to use validation

## Testing
- ✅ Unit tests (80% coverage)
- ✅ Manual testing on Desktop/Mobile/Dark-Mode
- ✅ No regressions detected

## Checklist
- [x] Tests written
- [x] Code reviewed locally
- [x] Documentation updated
- [x] No breaking changes
- [x] Target branch is 'develop'
```

### 3. Code Review

**Erwartete Feedback-Punkte:**
- ✅ Tests vorhanden?
- ✅ Error Handling?
- ✅ Performance-Impact?
- ✅ Accessibility?
- ✅ Dark-Mode Support?
- ✅ Documentation?

**Als Reviewer:**
```
✅ "Great implementation! Just one suggestion..."
✅ "This could be refactored to..."
❌ "This is wrong" (zu unspezifisch)
```

### 4. Merge

PR wird gemergt wenn:
- ✅ Alle Tests grün
- ✅ Code Review approved
- ✅ Keine Konflikte
- ✅ CI/CD erfolgreich

---

## 📚 Weitere Ressourcen

- [DEVELOPMENT.md](./wiki/Development.md) - Setup & Workflow
- [QUICK_START.md](./QUICK_START.md) - Getting Started
- [Nextcloud Developer Docs](https://docs.nextcloud.com/server/latest/developer_manual/)
- [Vue 3 Guide](https://vuejs.org/guide/)

---

## ❓ Fragen?

- 📖 Siehe [FAQ.md](./wiki/FAQ.md)
- 💬 Schreib in [GitHub Discussions](https://github.com/Wacken2012/nextcloud-verein/discussions)
- 📋 Erstelle ein [GitHub Issue](https://github.com/Wacken2012/nextcloud-verein/issues)

---

**Danke, dass du ein Teil meiner Community bist! 🙏**

*Entwickelt mit ❤️ von Stefan Schulz*
