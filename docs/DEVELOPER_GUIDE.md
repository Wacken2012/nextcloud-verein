# 🛠️ Developer Guide – Nextcloud Vereins-App

[🇩🇪 Deutsch](#deutsch) | [🇬🇧 English](#english)

---

# Deutsch

Dieses Dokument richtet sich an Entwickler, die zur Vereins-App beitragen oder sie erweitern möchten.

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

- **VS Code** mit Extensions: PHP Intelephense, Volar (Vue 3), ESLint
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

### 2. Dependencies installieren

```bash
composer install    # PHP Dependencies
npm install         # Frontend Dependencies
```

### 3. Frontend bauen

```bash
npm run watch       # Watch-Modus (Entwicklung)
npm run build       # Production Build
```

### 4. App aktivieren

```bash
sudo -u www-data php /var/www/html/nextcloud/occ app:enable verein
```

---

## 📁 Projektstruktur

```
verein/
├── appinfo/
│   ├── database.xml      # Datenbankschema
│   ├── info.xml          # App-Metadaten
│   └── routes.php        # API-Routen
├── docs/                 # Dokumentation
├── js/
│   ├── components/       # Vue-Komponenten
│   ├── dist/             # Kompilierte Assets
│   ├── App.vue           # Haupt-Vue-App
│   ├── main.js           # Entry Point
│   └── api.js            # API-Client
├── lib/
│   ├── Controller/       # HTTP Controller
│   ├── Db/               # Entities & Mapper
│   ├── Middleware/       # Request Middleware
│   ├── Service/          # Business Logic
│   └── Settings/         # Admin Settings
├── templates/            # PHP Templates
├── tests/                # Unit & Integration Tests
└── vendor/               # Composer Dependencies
```

---

## 🏗️ Architektur

### Schichtenmodell

```
┌─────────────────────────────────────┐
│           Frontend (Vue 3)          │
├─────────────────────────────────────┤
│           Controller (PHP)          │
├─────────────────────────────────────┤
│         Service (Business Logic)    │
├─────────────────────────────────────┤
│           Mapper/DB (ORM)           │
├─────────────────────────────────────┤
│          Nextcloud Core (OCP)       │
└─────────────────────────────────────┘
```

---

## 🐘 Backend (PHP)

### Controller erstellen

```php
<?php
namespace OCA\Verein\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;

class ExampleController extends Controller {
    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function index(): JSONResponse {
        return new JSONResponse(['status' => 'ok']);
    }
}
```

### Annotations

| Annotation | Bedeutung |
|------------|-----------|
| `@NoAdminRequired` | Nicht-Admins dürfen zugreifen |
| `@NoCSRFRequired` | Kein CSRF-Token nötig |
| `@PublicPage` | Öffentlich zugänglich |

### RBAC-Berechtigungen

```php
use OCA\Verein\Attributes\RequirePermission;

#[RequirePermission('members.create')]
public function create(): JSONResponse {
    // Nur mit members.create Berechtigung
}
```

---

## ⚡ Frontend (Vue 3)

### Komponenten-Struktur

```vue
<template>
  <NcButton @click="handleClick">
    {{ t('verein', 'Klick mich') }}
  </NcButton>
</template>

<script>
import { NcButton } from '@nextcloud/vue'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

export default {
  name: 'ExampleComponent',
  methods: {
    async handleClick() {
      const { data } = await axios.get(generateUrl('/apps/verein/api'))
    }
  }
}
</script>
```

### Notifications

```javascript
import { success, error } from '../notify.js'

success('Erfolgreich gespeichert')
error('Fehler beim Speichern')
```

---

## 🧪 Testing

```bash
# Alle Tests
composer test

# Nur Unit Tests
./vendor/bin/phpunit --testsuite Unit

# Schnelle Tests (Export, Validation)
./vendor/bin/phpunit --testsuite Fast

# Mit Coverage
./vendor/bin/phpunit --coverage-html coverage/
```

---

## 🚢 Deployment

```bash
# Deploy Script
./scripts/deploy-to-nextcloud.sh

# Manuell
rsync -av --exclude='node_modules' --exclude='.git' \
  ./ /var/www/html/nextcloud/apps/verein/
```

---

## 🤝 Contributing

1. Fork & Branch erstellen: `git checkout -b feature/mein-feature`
2. Änderungen committen (Conventional Commits)
3. Pull Request gegen `develop` Branch

### Commit-Format

```
<type>(<scope>): <description>

feat(members): Add bulk import
fix(export): Correct PDF widths
docs(api): Add OpenAPI spec
```

---

## 📏 Code Style

- **PHP**: PSR-12 mit `php-cs-fixer`
- **JavaScript**: ESLint mit Nextcloud-Config
- Typisierung in PHP (Type Hints, Return Types)
- Tests für neue Features

---

# English

This document is intended for developers who want to contribute to or extend the Vereins-App.

## 📋 Table of Contents

1. [Prerequisites](#prerequisites)
2. [Development Environment Setup](#development-environment-setup)
3. [Project Structure](#project-structure-1)
4. [Architecture](#architecture)
5. [Backend (PHP)](#backend-php-1)
6. [Frontend (Vue 3)](#frontend-vue-3-1)
7. [Testing](#testing-1)
8. [Deployment](#deployment-1)
9. [Contributing](#contributing-1)
10. [Code Style](#code-style-1)

---

## 🔧 Prerequisites

### System Requirements

| Component | Version | Purpose |
|-----------|---------|---------|
| **PHP** | ≥ 8.0 | Backend |
| **Node.js** | ≥ 18.x | Frontend Build |
| **npm** | ≥ 9.x | Package Manager |
| **Composer** | ≥ 2.x | PHP Dependencies |
| **Nextcloud** | ≥ 28.0 | App Platform |
| **SQLite/MySQL/PostgreSQL** | - | Database |

### Recommended Tools

- **VS Code** with Extensions: PHP Intelephense, Volar (Vue 3), ESLint
- **Git** for version control
- **Postman** or **curl** for API testing

---

## 🚀 Development Environment Setup

### 1. Clone Repository

```bash
cd /path/to/nextcloud/apps
git clone https://github.com/Wacken2012/nextcloud-verein.git verein
cd verein
```

### 2. Install Dependencies

```bash
composer install    # PHP Dependencies
npm install         # Frontend Dependencies
```

### 3. Build Frontend

```bash
npm run watch       # Watch mode (development)
npm run build       # Production build
```

### 4. Enable App

```bash
sudo -u www-data php /var/www/html/nextcloud/occ app:enable verein
```

---

## 📁 Project Structure

```
verein/
├── appinfo/
│   ├── database.xml      # Database schema
│   ├── info.xml          # App metadata
│   └── routes.php        # API routes
├── docs/                 # Documentation
├── js/
│   ├── components/       # Vue components
│   ├── dist/             # Compiled assets
│   ├── App.vue           # Main Vue app
│   ├── main.js           # Entry point
│   └── api.js            # API client
├── lib/
│   ├── Controller/       # HTTP controllers
│   ├── Db/               # Entities & mappers
│   ├── Middleware/       # Request middleware
│   ├── Service/          # Business logic
│   └── Settings/         # Admin settings
├── templates/            # PHP templates
├── tests/                # Unit & integration tests
└── vendor/               # Composer dependencies
```

---

## 🏗️ Architecture

### Layer Model

```
┌─────────────────────────────────────┐
│           Frontend (Vue 3)          │
├─────────────────────────────────────┤
│           Controller (PHP)          │
├─────────────────────────────────────┤
│         Service (Business Logic)    │
├─────────────────────────────────────┤
│           Mapper/DB (ORM)           │
├─────────────────────────────────────┤
│          Nextcloud Core (OCP)       │
└─────────────────────────────────────┘
```

---

## 🐘 Backend (PHP)

### Creating Controllers

```php
<?php
namespace OCA\Verein\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;

class ExampleController extends Controller {
    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function index(): JSONResponse {
        return new JSONResponse(['status' => 'ok']);
    }
}
```

### Annotations

| Annotation | Meaning |
|------------|---------|
| `@NoAdminRequired` | Non-admins can access |
| `@NoCSRFRequired` | No CSRF token required |
| `@PublicPage` | Publicly accessible |

### RBAC Permissions

```php
use OCA\Verein\Attributes\RequirePermission;

#[RequirePermission('members.create')]
public function create(): JSONResponse {
    // Only with members.create permission
}
```

---

## ⚡ Frontend (Vue 3)

### Component Structure

```vue
<template>
  <NcButton @click="handleClick">
    {{ t('verein', 'Click me') }}
  </NcButton>
</template>

<script>
import { NcButton } from '@nextcloud/vue'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

export default {
  name: 'ExampleComponent',
  methods: {
    async handleClick() {
      const { data } = await axios.get(generateUrl('/apps/verein/api'))
    }
  }
}
</script>
```

### Notifications

```javascript
import { success, error } from '../notify.js'

success('Successfully saved')
error('Error saving')
```

---

## 🧪 Testing

```bash
# All tests
composer test

# Unit tests only
./vendor/bin/phpunit --testsuite Unit

# Fast tests (Export, Validation)
./vendor/bin/phpunit --testsuite Fast

# With coverage
./vendor/bin/phpunit --coverage-html coverage/
```

---

## 🚢 Deployment

```bash
# Deploy script
./scripts/deploy-to-nextcloud.sh

# Manual
rsync -av --exclude='node_modules' --exclude='.git' \
  ./ /var/www/html/nextcloud/apps/verein/
```

---

## 🤝 Contributing

1. Fork & create branch: `git checkout -b feature/my-feature`
2. Commit changes (Conventional Commits)
3. Pull Request against `develop` branch

### Commit Format

```
<type>(<scope>): <description>

feat(members): Add bulk import
fix(export): Correct PDF widths
docs(api): Add OpenAPI spec
```

---

## 📏 Code Style

- **PHP**: PSR-12 with `php-cs-fixer`
- **JavaScript**: ESLint with Nextcloud config
- Type hints in PHP (Type Hints, Return Types)
- Tests for new features

---

## 📚 Further Resources / Weiterführende Ressourcen

- [Nextcloud Developer Documentation](https://docs.nextcloud.com/server/latest/developer_manual/)
- [Nextcloud Vue Components](https://nextcloud-vue-components.netlify.app/)
- [Vue 3 Documentation](https://vuejs.org/)
- [OpenAPI Specification](https://swagger.io/specification/)

---

*Letzte Aktualisierung / Last updated: 30. November 2025*
