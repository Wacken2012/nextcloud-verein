# 🛠️ Developer Guide – Nextcloud Vereins-App

Dieses Dokument richtet sich an Entwickler, die zur Vereins-App beitragen oder sie erweitern möchten.

---

## 📋 Inhaltsverzeichnis

1. [Voraussetzungen](#voraussetzungen)
2. [Entwicklungsumgebung einrichten](#entwicklungsumgebung-einrichten)
3. [Projektstruktur](#projektstruktur)
4. [Architektur](#architektur)
5. [Backend (PHP)](#backend-php)
6. [Frontend (Vue 3)](#frontend-vue-3)
7. [Testing](#testing)
8. [Deployment](#deployment)
9. [Contributing](#contributing)
10. [Code Style](#code-style)

---

## 🔧 Voraussetzungen

### Systemanforderungen

| Komponente | Version | Zweck |
|------------|---------|-------|
| **PHP** | ≥ 8.0 | Backend |
| **Node.js** | ≥ 18.x | Frontend Build |
| **npm** | ≥ 9.x | Package Manager |
| **Composer** | ≥ 2.x | PHP Dependencies |
| **Nextcloud** | ≥ 28.0 | App-Plattform |
| **SQLite/MySQL/PostgreSQL** | - | Datenbank |

### Empfohlene Tools

- **VS Code** mit Extensions:
  - PHP Intelephense
  - Volar (Vue 3)
  - ESLint
  - PHP CS Fixer
- **Git** für Versionskontrolle
- **Postman** oder **curl** für API-Tests

---

## 🚀 Entwicklungsumgebung einrichten

### 1. Repository klonen

```bash
cd /path/to/nextcloud/apps
git clone https://github.com/Wacken2012/nextcloud-verein.git verein
cd verein
```

### 2. PHP Dependencies installieren

```bash
composer install
```

### 3. Frontend Dependencies installieren

```bash
npm install
```

### 4. Frontend für Entwicklung starten

```bash
# Watch-Modus (automatisches Rebuild bei Änderungen)
npm run watch

# Oder einmaliger Build
npm run build
```

### 5. App in Nextcloud aktivieren

```bash
# Via occ CLI
sudo -u www-data php /var/www/html/nextcloud/occ app:enable verein

# Oder über Nextcloud Admin UI
# Einstellungen → Apps → Verein aktivieren
```

### 6. Datenbank-Migrationen ausführen

```bash
sudo -u www-data php /var/www/html/nextcloud/occ migrations:execute verein
```

---

## 📁 Projektstruktur

```
verein/
├── appinfo/
│   ├── database.xml      # Datenbankschema
│   ├── info.xml          # App-Metadaten
│   └── routes.php        # API-Routen
├── docs/
│   ├── api/              # API-Dokumentation
│   │   ├── openapi.yaml  # OpenAPI Spec
│   │   └── README.md     # API Guide
│   └── DEVELOPER_GUIDE.md
├── img/                  # Icons und Bilder
├── js/
│   ├── components/       # Vue-Komponenten
│   │   ├── MemberList.vue
│   │   ├── FeeList.vue
│   │   ├── Finance.vue
│   │   ├── Statistics.vue
│   │   └── ...
│   ├── dist/             # Kompilierte Assets
│   ├── App.vue           # Haupt-Vue-App
│   ├── main.js           # Vue Entry Point
│   ├── router.js         # Vue Router
│   ├── api.js            # API-Client
│   └── notify.js         # Notification Utility
├── lib/
│   ├── AppInfo/
│   │   └── Application.php   # App Bootstrap
│   ├── Controller/           # HTTP Controller
│   │   ├── MemberController.php
│   │   ├── FinanceController.php
│   │   ├── ExportController.php
│   │   ├── RoleController.php
│   │   └── ...
│   ├── Db/                   # Entities & Mapper
│   │   ├── Member.php
│   │   ├── MemberMapper.php
│   │   ├── Fee.php
│   │   ├── FeeMapper.php
│   │   └── ...
│   ├── Middleware/           # Request Middleware
│   │   └── AuthorizationMiddleware.php
│   ├── Service/              # Business Logic
│   │   ├── MemberService.php
│   │   ├── FeeService.php
│   │   ├── Export/
│   │   │   ├── CsvExporter.php
│   │   │   └── PdfExporter.php
│   │   ├── RBAC/
│   │   │   └── RoleService.php
│   │   └── Validation/
│   │       ├── IbanValidator.php
│   │       ├── EmailValidator.php
│   │       └── Sanitizer.php
│   └── Settings/             # Admin Settings
│       ├── AdminSection.php
│       └── AdminSettings.php
├── templates/                # PHP Templates
│   └── main.php
├── tests/
│   ├── Unit/                 # Unit Tests
│   └── Integration/          # Integration Tests
├── vendor/                   # Composer Dependencies
├── composer.json
├── package.json
├── phpunit.xml
└── vite.config.js
```

---

## 🏗️ Architektur

### Schichtenmodell

```
┌─────────────────────────────────────────────────┐
│                   Frontend                       │
│              (Vue 3 + Vite)                     │
├─────────────────────────────────────────────────┤
│                  Controller                      │
│         (OCP\AppFramework\Controller)           │
├─────────────────────────────────────────────────┤
│                   Service                        │
│            (Business Logic)                      │
├─────────────────────────────────────────────────┤
│                  Mapper/DB                       │
│         (OCP\AppFramework\Db)                   │
├─────────────────────────────────────────────────┤
│                 Nextcloud Core                   │
│           (OCP Interfaces)                       │
└─────────────────────────────────────────────────┘
```

### Datenfluß

```
Browser Request
      │
      ▼
┌─────────────┐
│   routes.php │ → Route-Matching
└──────┬──────┘
       │
       ▼
┌─────────────────────┐
│ AuthorizationMiddleware │ → RBAC Check
└──────────┬──────────┘
           │
           ▼
┌─────────────────┐
│    Controller    │ → Request Handling
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│     Service      │ → Business Logic
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│     Mapper       │ → Database Access
└────────┬────────┘
         │
         ▼
    Database (SQLite/MySQL/PostgreSQL)
```

---

## 🐘 Backend (PHP)

### Controller erstellen

Controller erben von `OCP\AppFramework\Controller`:

```php
<?php
namespace OCA\Verein\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

class ExampleController extends Controller {
    private ExampleService $service;

    public function __construct(
        string $appName,
        IRequest $request,
        ExampleService $service
    ) {
        parent::__construct($appName, $request);
        $this->service = $service;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function index(): JSONResponse {
        return new JSONResponse($this->service->findAll());
    }
}
```

### Annotations

| Annotation | Bedeutung |
|------------|-----------|
| `@NoAdminRequired` | Nicht-Admins dürfen zugreifen |
| `@NoCSRFRequired` | Kein CSRF-Token nötig (für APIs) |
| `@PublicPage` | Öffentlich zugänglich |

### Entity erstellen

```php
<?php
namespace OCA\Verein\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method int getId()
 * @method string getName()
 * @method void setName(string $name)
 */
class Example extends Entity {
    protected string $name = '';
    protected ?string $description = null;

    public function __construct() {
        $this->addType('id', 'integer');
    }
}
```

### Mapper erstellen

```php
<?php
namespace OCA\Verein\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class ExampleMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'verein_examples', Example::class);
    }

    public function findAll(): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')->from($this->tableName);
        return $this->findEntities($qb);
    }
}
```

### RBAC-Berechtigungen

Verwende das `@RequirePermission` Attribut:

```php
use OCA\Verein\Attributes\RequirePermission;

class MemberController extends Controller {
    
    #[RequirePermission('members.create')]
    public function create(): JSONResponse {
        // Nur mit members.create Berechtigung erreichbar
    }
}
```

### Validierung

Nutze die Validator-Klassen:

```php
use OCA\Verein\Service\Validation\IbanValidator;
use OCA\Verein\Service\Validation\EmailValidator;

$ibanValidator = new IbanValidator();
$result = $ibanValidator->validate('DE89370400440532013000');

if (!$result->isValid()) {
    throw new ValidationException($result->getErrors());
}
```

---

## ⚡ Frontend (Vue 3)

### Komponenten-Struktur

```vue
<template>
  <div class="component">
    <NcButton @click="handleClick">
      {{ t('verein', 'Klick mich') }}
    </NcButton>
  </div>
</template>

<script>
import { NcButton } from '@nextcloud/vue'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

export default {
  name: 'ExampleComponent',
  components: { NcButton },
  
  data() {
    return {
      items: [],
      loading: false,
    }
  },
  
  async mounted() {
    await this.fetchData()
  },
  
  methods: {
    async fetchData() {
      this.loading = true
      try {
        const url = generateUrl('/apps/verein/api/items')
        const { data } = await axios.get(url)
        this.items = data
      } catch (error) {
        console.error('Fehler:', error)
      } finally {
        this.loading = false
      }
    },
    
    handleClick() {
      // Handler
    }
  }
}
</script>

<style scoped lang="scss">
.component {
  padding: 20px;
}
</style>
```

### Nextcloud Vue Components

Nutze die offiziellen Nextcloud Vue Komponenten:

```javascript
import {
  NcButton,
  NcModal,
  NcTextField,
  NcSelect,
  NcActions,
  NcActionButton,
  NcEmptyContent,
  NcLoadingIcon,
} from '@nextcloud/vue'
```

Dokumentation: https://nextcloud-vue-components.netlify.app/

### API Aufrufe

```javascript
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

// GET Request
const url = generateUrl('/apps/verein/members')
const { data } = await axios.get(url)

// POST Request
await axios.post(generateUrl('/apps/verein/members'), {
  name: 'Max Mustermann',
  email: 'max@example.com'
})

// PUT Request
await axios.put(generateUrl('/apps/verein/members/{id}', { id: 5 }), {
  name: 'Neuer Name'
})

// DELETE Request
await axios.delete(generateUrl('/apps/verein/members/{id}', { id: 5 }))
```

### Notifications

Verwende die zentrale `notify.js` Utility:

```javascript
import { success, error, info } from '../notify.js'

// Erfolg
success('Mitglied erfolgreich erstellt')

// Fehler
error('Fehler beim Speichern')

// Info
info('Daten werden geladen...')
```

---

## 🧪 Testing

### PHPUnit Tests ausführen

```bash
# Alle Tests
composer test

# Nur Unit Tests
./vendor/bin/phpunit --testsuite Unit

# Nur schnelle Tests (Export, Validation)
./vendor/bin/phpunit --testsuite Fast

# Einzelne Testdatei
./vendor/bin/phpunit tests/Unit/Service/MemberServiceTest.php

# Mit Coverage
./vendor/bin/phpunit --coverage-html coverage/
```

### Test-Struktur

```php
<?php
namespace OCA\Verein\Tests\Unit\Service;

use OCA\Verein\Service\MemberService;
use PHPUnit\Framework\TestCase;

class MemberServiceTest extends TestCase {
    private MemberService $service;

    protected function setUp(): void {
        parent::setUp();
        $this->service = new MemberService(/* mocked dependencies */);
    }

    public function testFindAllReturnsArray(): void {
        $result = $this->service->findAll();
        $this->assertIsArray($result);
    }
    
    /**
     * @dataProvider validEmailProvider
     */
    public function testEmailValidation(string $email, bool $expected): void {
        $result = $this->service->isValidEmail($email);
        $this->assertSame($expected, $result);
    }
    
    public static function validEmailProvider(): array {
        return [
            ['test@example.com', true],
            ['invalid-email', false],
        ];
    }
}
```

### Testsuites

| Suite | Beschreibung | Dauer |
|-------|--------------|-------|
| `Unit` | Alle Unit Tests | ~2s |
| `Fast` | Export + Validation | ~0.1s |
| `Integration` | Mit Datenbank | ~5s |

---

## 🚢 Deployment

### Entwicklung → Test-Server

```bash
# Deploy Script verwenden
./scripts/deploy-to-nextcloud.sh

# Oder manuell
rsync -av --exclude='node_modules' --exclude='.git' \
  ./ /var/www/html/nextcloud/apps/verein/
```

### Production Release

1. **Version erhöhen** in `appinfo/info.xml`
2. **Frontend bauen**: `npm run build`
3. **Tests ausführen**: `composer test`
4. **Release erstellen**:

```bash
# Tag erstellen
git tag -a v0.2.1 -m "Release v0.2.1"
git push origin v0.2.1

# Release-Archiv erstellen
./scripts/create-release.sh
```

### Deploy Script

Das Projekt enthält ein Deploy-Script unter `scripts/deploy-to-nextcloud.sh`:

```bash
#!/bin/bash
DEST="/var/www/html/nextcloud/apps/verein"

# Sync relevante Ordner
rsync -av js/dist/ "$DEST/js/dist/"
rsync -av lib/ "$DEST/lib/"
rsync -av templates/ "$DEST/templates/"
rsync -av appinfo/ "$DEST/appinfo/"
rsync -av vendor/ "$DEST/vendor/"

# Cache leeren
sudo -u www-data php /var/www/html/nextcloud/occ maintenance:repair
```

---

## 🤝 Contributing

### Workflow

1. **Fork** das Repository
2. **Branch** erstellen: `git checkout -b feature/mein-feature`
3. **Änderungen** committen: `git commit -m "feat: Beschreibung"`
4. **Push**: `git push origin feature/mein-feature`
5. **Pull Request** erstellen

### Branch-Namenskonvention

| Prefix | Verwendung |
|--------|------------|
| `feature/` | Neue Features |
| `fix/` | Bugfixes |
| `docs/` | Dokumentation |
| `refactor/` | Code-Refactoring |
| `test/` | Tests hinzufügen |

### Commit-Nachrichten

Wir verwenden [Conventional Commits](https://www.conventionalcommits.org/):

```
<type>(<scope>): <description>

[optional body]
```

**Types:**
- `feat`: Neues Feature
- `fix`: Bugfix
- `docs`: Dokumentation
- `style`: Formatierung
- `refactor`: Code-Refactoring
- `test`: Tests
- `chore`: Maintenance

**Beispiele:**
```
feat(members): Add bulk import from CSV
fix(export): Correct PDF column widths
docs(api): Add OpenAPI specification
test(validation): Add IBAN edge cases
```

---

## 📏 Code Style

### PHP

Wir folgen PSR-12 mit PHP CS Fixer:

```bash
# Code formatieren
./vendor/bin/php-cs-fixer fix

# Nur prüfen
./vendor/bin/php-cs-fixer fix --dry-run
```

Konfiguration in `.php-cs-fixer.php`:

```php
<?php
return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'no_unused_imports' => true,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__ . '/lib')
            ->in(__DIR__ . '/tests')
    );
```

### JavaScript/Vue

ESLint mit Nextcloud-Config:

```bash
# Linting
npm run lint

# Auto-Fix
npm run lint:fix
```

### Wichtige Regeln

- **Keine `var`** – nutze `const` oder `let`
- **Typisierung** in PHP (Type Hints, Return Types)
- **Dokumentation** für öffentliche Methoden
- **Tests** für neue Features

---

## 📚 Weiterführende Ressourcen

- [Nextcloud Developer Documentation](https://docs.nextcloud.com/server/latest/developer_manual/)
- [Nextcloud App Tutorial](https://docs.nextcloud.com/server/latest/developer_manual/app_development/tutorial.html)
- [Nextcloud Vue Components](https://nextcloud-vue-components.netlify.app/)
- [Vue 3 Documentation](https://vuejs.org/guide/introduction.html)
- [OpenAPI Specification](https://swagger.io/specification/)

---

## ❓ Hilfe & Support

- **Issues**: [GitHub Issues](https://github.com/Wacken2012/nextcloud-verein/issues)
- **Discussions**: [GitHub Discussions](https://github.com/Wacken2012/nextcloud-verein/discussions)

---

*Letzte Aktualisierung: 30. November 2025*
