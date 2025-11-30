# 🤝 Contributing Guidelines

**[🇩🇪 Deutsch](#deutsch)** | **[🇬🇧 English](#english)**

> 📚 **Für detaillierte technische Informationen siehe den [Developer Guide](docs/DEVELOPER_GUIDE.md)**
> 
> 📚 **For detailed technical information see the [Developer Guide](docs/DEVELOPER_GUIDE.md)**

---

<a name="deutsch"></a>
# 🇩🇪 Beitragsrichtlinien (Deutsch)

Vielen Dank dass du zur Nextcloud Vereins-App beitragen möchtest! Diese Richtlinien helfen uns, Qualität und Konsistenz zu bewahren.

---

## 📋 Inhaltsverzeichnis

1. [Code of Conduct](#code-of-conduct)
2. [Wie kann ich beitragen?](#wie-kann-ich-beitragen)
3. [Entwicklungssetup](#entwicklungssetup)
4. [Code-Standards](#code-standards)
5. [Testing Guidelines](#testing-guidelines-de)
6. [Validierung & Error Handling](#validierung--error-handling)
7. [Commit Message Format](#commit-message-format-de)
8. [Pull Request Prozess](#pull-request-prozess)

---

## 📜 Code of Conduct

Ich verpflichte mich auf einen respektvollen und inklusiven Entwicklungsprozess.

**Bitte beachte:**
- Sei respektvoll gegenüber anderen Beitragenden
- Gib konstruktives Feedback
- Respektiere unterschiedliche Meinungen
- Melde problematisches Verhalten über GitHub Issues

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
1. Prüfe ob Bug bereits existiert (GitHub Issues)
2. Erstelle neue Issue mit:
   - Klare Beschreibung
   - Steps to Reproduce
   - Erwartetes vs. Aktuelles Verhalten
   - Screenshots (wenn UI-Bug)
   - System-Info (Browser, OS, Nextcloud-Version)

### Feature Requests
1. Öffne GitHub Discussion
2. Beschreibe die Feature mit:
   - Anwendungsfall/Problem das gelöst wird
   - Gewünschte Lösung
   - Alternative Lösungen
3. Warte auf Community Feedback

### Code Contributions
1. Fork das Repository
2. Erstelle Feature-Branch: `git checkout -b feature/meine-feature`
3. Implement, test, commit
4. Push und Create Pull Request
5. Code Review abwarten

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

# PHP-Tests
vendor/bin/phpunit tests/
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
        emit('update', data)
      } catch (error) {
        console.error('Update failed:', error)
      }
    }
    return { handleUpdate }
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

class MemberController extends Controller {
    
    public function __construct(
        string $appName,
        IRequest $request,
        private MemberService $memberService,
    ) {
        parent::__construct($appName, $request);
    }
    
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

---

<a name="testing-guidelines-de"></a>
## 🧪 Testing Guidelines

### PHPUnit Tests

```php
class MemberControllerTest extends TestCase {
    public function testAdminCanCreateMember(): void {
        // Arrange: Setup
        $memberService = $this->createMock(MemberService::class);
        $controller = new MemberController('verein', $memberService);
        
        $memberService->expects($this->once())
            ->method('create')
            ->willReturn(['id' => 1, 'name' => 'John']);
        
        // Act: Führe Operation aus
        $result = $controller->create('John', 'john@example.com');
        
        // Assert: Prüfe Ergebnis
        $this->assertEquals(200, $result->getStatus());
    }
}
```

**Test-Coverage Ziele:**
- ✅ Neue Features: mindestens 80% Coverage
- ✅ Bug Fixes: Test der Reproduktion + Fix
- ✅ Public APIs: 100% Coverage

---

## ✅ Validierung & Error Handling

### Backend-Validierung

```php
$validationService = new ValidationService();
$validation = $validationService->validateMember([
    'name' => $request->getParam('name'),
    'email' => $request->getParam('email'),
    'iban' => $request->getParam('iban')
]);

if (!$validation['valid']) {
    return new DataResponse([
        'status' => 'error',
        'errors' => $validation['errors']
    ], Http::STATUS_BAD_REQUEST);
}
```

**Validierte Felder:**
- ✅ Email: RFC 5322 Format
- ✅ IBAN: Mod-97-Checksum (ISO 13616)
- ✅ BIC: ISO 9362 Format
- ✅ Pflichtfelder: Name, Email

---

<a name="commit-message-format-de"></a>
## 📌 Commit Message Format

Wir verwenden **Conventional Commits**:

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
git commit -m "feat(member): Add IBAN validation"
git commit -m "fix(alert): Dark-mode colors not applying"
git commit -m "docs: Update CONTRIBUTING.md"
```

---

## 🔄 Pull Request Prozess

### 1. Vor dem PR

```bash
# Branch aktualisieren
git fetch origin
git rebase origin/develop

# Tests ausführen
vendor/bin/phpunit tests/

# Linting
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

### 3. Code Review

**Erwartete Feedback-Punkte:**
- ✅ Tests vorhanden?
- ✅ Error Handling?
- ✅ Performance-Impact?
- ✅ Documentation?

### 4. Merge

PR wird gemergt wenn:
- ✅ Alle Tests grün
- ✅ Code Review approved
- ✅ Keine Konflikte

---

## 📚 Weitere Ressourcen

- [Developer Guide](docs/DEVELOPER_GUIDE.md) - Technische Details
- [API Documentation](docs/api/README.md) - API Referenz
- [QUICK_START.md](./QUICK_START.md) - Getting Started

---

## ❓ Fragen?

- 📖 Siehe [FAQ.md](./wiki/FAQ.md)
- 💬 [GitHub Discussions](https://github.com/Wacken2012/nextcloud-verein/discussions)
- 📋 [GitHub Issues](https://github.com/Wacken2012/nextcloud-verein/issues)

---
---

<a name="english"></a>
# 🇬🇧 Contributing Guidelines (English)

Thank you for wanting to contribute to the Nextcloud Vereins-App! These guidelines help us maintain quality and consistency.

---

## 📋 Table of Contents

1. [Code of Conduct](#code-of-conduct-en)
2. [How can I contribute?](#how-can-i-contribute)
3. [Development Setup](#development-setup)
4. [Code Standards](#code-standards-en)
5. [Testing Guidelines](#testing-guidelines-en)
6. [Validation & Error Handling](#validation--error-handling-en)
7. [Commit Message Format](#commit-message-format-en)
8. [Pull Request Process](#pull-request-process)

---

<a name="code-of-conduct-en"></a>
## 📜 Code of Conduct

We are committed to a respectful and inclusive development process.

**Please note:**
- Be respectful towards other contributors
- Provide constructive feedback
- Respect different opinions
- Report problematic behavior via GitHub Issues

---

## 🎯 How can I contribute?

### Branch Workflow

**Important:** Please open Pull Requests against the **`develop`** branch!

```
main (v0.1.0 - Stable)
  ↑
  └─ develop (v0.2.0-beta - Features)
       ├─ feature/rbac
       ├─ feature/pdf-export
       ├─ feature/sepa-export
       └─ feature/error-handling
```

**Branch Convention**:
- `main` → Stable Production Version
- `develop` → Current Feature Development
- `feature/*` → New Features (based on `develop`)
- `bugfix/*` → Bug Fixes (based on `develop`)

### Bug Reports
1. Check if bug already exists (GitHub Issues)
2. Create new issue with:
   - Clear description
   - Steps to Reproduce
   - Expected vs. Actual behavior
   - Screenshots (for UI bugs)
   - System info (Browser, OS, Nextcloud version)

### Feature Requests
1. Open GitHub Discussion
2. Describe the feature with:
   - Use case/Problem being solved
   - Desired solution
   - Alternative solutions
3. Wait for community feedback

### Code Contributions
1. Fork the repository
2. Create feature branch: `git checkout -b feature/my-feature`
3. Implement, test, commit
4. Push and create Pull Request
5. Wait for code review

---

## 🛠️ Development Setup

### Installation

```bash
# Clone repository
git clone https://github.com/Wacken2012/nextcloud-verein.git
cd nextcloud-verein

# Install dependencies
npm install
composer install

# Create symlink (optional)
ln -s $(pwd) /var/www/nextcloud/apps/verein-dev

# Enable app
sudo -u www-data php /var/www/nextcloud/occ app:enable verein-dev
```

### Development Server

```bash
# Watch mode (auto-rebuild on changes)
npm run dev

# Build once
npm run build

# Check linting
npm run lint
npm run lint:fix
```

### Testing

```bash
# Run all tests
npm run test

# PHP tests
vendor/bin/phpunit tests/
```

---

<a name="code-standards-en"></a>
## 📝 Code Standards

### JavaScript/Vue.js

**Formatting:**
```bash
npm run lint:fix  # Auto-fix linting issues
```

**Best Practices:**
```javascript
// ✅ Good: Descriptive names, clear intent
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
        emit('update', data)
      } catch (error) {
        console.error('Update failed:', error)
      }
    }
    return { handleUpdate }
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

class MemberController extends Controller {
    
    public function __construct(
        string $appName,
        IRequest $request,
        private MemberService $memberService,
    ) {
        parent::__construct($appName, $request);
    }
    
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
- Use Type Hints
- DocBlocks for Public Methods
- Meaningful variable names

---

<a name="testing-guidelines-en"></a>
## 🧪 Testing Guidelines

### PHPUnit Tests

```php
class MemberControllerTest extends TestCase {
    public function testAdminCanCreateMember(): void {
        // Arrange: Setup
        $memberService = $this->createMock(MemberService::class);
        $controller = new MemberController('verein', $memberService);
        
        $memberService->expects($this->once())
            ->method('create')
            ->willReturn(['id' => 1, 'name' => 'John']);
        
        // Act: Execute operation
        $result = $controller->create('John', 'john@example.com');
        
        // Assert: Check result
        $this->assertEquals(200, $result->getStatus());
    }
}
```

**Test Coverage Goals:**
- ✅ New features: at least 80% coverage
- ✅ Bug fixes: Test reproduction + fix
- ✅ Public APIs: 100% coverage

---

<a name="validation--error-handling-en"></a>
## ✅ Validation & Error Handling

### Backend Validation

```php
$validationService = new ValidationService();
$validation = $validationService->validateMember([
    'name' => $request->getParam('name'),
    'email' => $request->getParam('email'),
    'iban' => $request->getParam('iban')
]);

if (!$validation['valid']) {
    return new DataResponse([
        'status' => 'error',
        'errors' => $validation['errors']
    ], Http::STATUS_BAD_REQUEST);
}
```

**Validated Fields:**
- ✅ Email: RFC 5322 format
- ✅ IBAN: Mod-97-Checksum (ISO 13616)
- ✅ BIC: ISO 9362 format
- ✅ Required fields: Name, Email

---

<a name="commit-message-format-en"></a>
## 📌 Commit Message Format

We use **Conventional Commits**:

```
<type>(<scope>): <subject>

<body>

<footer>
```

**Types:**
- `feat`: New functionality
- `fix`: Bug fix
- `refactor`: Code restructuring
- `perf`: Performance improvement
- `test`: Add/update tests
- `docs`: Documentation
- `style`: Formatting
- `ci`: CI/CD changes

**Examples:**
```bash
git commit -m "feat(member): Add IBAN validation"
git commit -m "fix(alert): Dark-mode colors not applying"
git commit -m "docs: Update CONTRIBUTING.md"
```

---

## 🔄 Pull Request Process

### 1. Before the PR

```bash
# Update branch
git fetch origin
git rebase origin/develop

# Run tests
vendor/bin/phpunit tests/

# Linting
npm run lint

# Build
npm run build
```

### 2. Create PR (Target: develop)

**⚠️ IMPORTANT**: Make sure `develop` is selected as target!

**Title:**
```
feat: Add IBAN validation to member creation
```

### 3. Code Review

**Expected feedback points:**
- ✅ Tests present?
- ✅ Error handling?
- ✅ Performance impact?
- ✅ Documentation?

### 4. Merge

PR will be merged when:
- ✅ All tests green
- ✅ Code review approved
- ✅ No conflicts

---

## 📚 Additional Resources

- [Developer Guide](docs/DEVELOPER_GUIDE.md) - Technical Details
- [API Documentation](docs/api/README.md) - API Reference
- [QUICK_START.md](./QUICK_START.md) - Getting Started

---

## ❓ Questions?

- 📖 See [FAQ.md](./wiki/FAQ.md)
- 💬 [GitHub Discussions](https://github.com/Wacken2012/nextcloud-verein/discussions)
- 📋 [GitHub Issues](https://github.com/Wacken2012/nextcloud-verein/issues)

---

**Thank you for being part of our community! 🙏**

*Developed with ❤️ by Stefan Schulz*
