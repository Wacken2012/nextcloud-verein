# Developer Guide

> 🇩🇪 [Deutsch](#deutsch) | 🇬🇧 [English](#english)

---

# 🇩🇪 Deutsch

## Entwickler-Anleitung

Richtlinien für Entwickler die zur Nextcloud Vereins-App beitragen möchten.

---

## 📋 Inhaltsverzeichnis

1. [Setup](#setup)
2. [Projekt-Struktur](#projekt-struktur)
3. [Entwicklungs-Workflow](#entwicklungs-workflow)
4. [Code-Standards](#code-standards)
5. [Testing](#testing)
6. [Git & GitHub](#git--github)

---

## ⚙️ Setup

### Schritt 1: Repository forken & klonen

```bash
# 1. Auf GitHub forken: https://github.com/Wacken2012/nextcloud-verein/fork

# 2. Dein Fork klonen
git clone https://github.com/DEIN_USERNAME/nextcloud-verein.git
cd nextcloud-verein

# 3. Upstream hinzufügen
git remote add upstream https://github.com/Wacken2012/nextcloud-verein.git
```

### Schritt 2: Lokale Umgebung vorbereiten

```bash
# Dependencies installieren
npm install

# Entwicklungs-Server starten
npm run dev
```

---

## 📁 Projekt-Struktur

```
nextcloud-verein/
├── appinfo/
│   ├── info.xml              # App-Metadaten
│   └── routes.php            # API-Routes
│
├── js/
│   ├── main.js              # Vue.js App-Entry
│   ├── theme.scss           # CSS-Variablen & Design
│   ├── components/          # Vue-Komponenten
│   ├── api.js              # API-Adapter
│   └── dist/               # Gebuildete Dateien
│
├── lib/
│   ├── AppInfo/            # Application.php
│   ├── Controller/         # API-Controller
│   ├── Db/                # Datenbank-Models
│   ├── Service/           # Business Logic
│   └── Attributes/        # Decorators & Middleware
│
├── tests/
│   ├── Unit/              # Unit Tests
│   └── Integration/       # Integration Tests
│
├── docs/
│   ├── api/               # OpenAPI Dokumentation
│   └── ...
│
├── vite.config.js         # Build-Konfiguration
├── package.json           # Dependencies & Scripts
└── README.md             # Projekt-Übersicht
```

---

## 🔄 Entwicklungs-Workflow

### 1. Feature-Branch erstellen

```bash
# Upstream aktualisieren
git fetch upstream main

# Branch von upstream erstellen
git checkout -b feat/neue-feature upstream/main
```

### 2. Code schreiben & testen

```bash
# Entwicklungs-Server sollte noch laufen (npm run dev)
# Bei Änderungen wird automatisch neu gebuildert

# Tests ausführen
npm run test

# Linting prüfen
npm run lint
```

### 3. Commits erstellen

```bash
# Änderungen stagen
git add js/components/MyComponent.vue

# Mit aussagekräftiger Nachricht committen
git commit -m "feat: Add new member validation

- Add email format validation
- Add IBAN format check
- Display error messages to user
- Add unit tests"
```

### 4. Zu eigenem Fork pushen

```bash
git push origin feat/neue-feature
```

### 5. Pull Request erstellen

```
GitHub → Pull Requests → New Pull Request

Title: feat: Add new member validation

Description:
- Was wurde hinzugefügt?
- Warum?
- Screenshots/Videos (wenn UI-Änderung)

Checklist:
✅ Tests geschrieben
✅ Code formatiert
✅ npm run lint bestand
✅ Doku aktualisiert
```

---

## 📝 Code-Standards

### JavaScript/Vue.js

**ESLint läuft automatisch:**
```bash
npm run lint          # Prüfen
npm run lint:fix      # Automatisch beheben
```

**Formatierung:**
```javascript
// ✅ Gut: Clear, descriptive names
function calculateMemberTotal(members) {
  return members.reduce((sum, member) => sum + member.fee, 0);
}

// ❌ Schlecht: Kryptisch
function calc(m) {
  return m.reduce((s, item) => s + item.f, 0);
}
```

### Vue-Komponenten

**Struktur:**
```vue
<template>
  <div class="member-item">
    <h3>{{ member.name }}</h3>
    <button @click="edit">Edit</button>
  </div>
</template>

<script>
export default {
  name: 'MemberItem',
  props: {
    member: {
      type: Object,
      required: true
    }
  },
  data() {
    return {};
  },
  methods: {
    edit() {
      // ...
    }
  }
};
</script>

<style scoped>
.member-item {
  padding: 1rem;
  border-radius: var(--border-radius);
}
</style>
```

### SCSS/CSS

**Variablen nutzen:**
```scss
// ✅ Gut
.button {
  background: var(--primary-color);
  padding: var(--spacing-md);
  border-radius: var(--border-radius);
}

// ❌ Schlecht
.button {
  background: #0066cc;
  padding: 1rem;
  border-radius: 4px;
}
```

---

## 🧪 Testing

### Unit Tests schreiben

```bash
# Test-Datei erstellen
touch tests/unit/MyComponent.spec.js
```

**Test-Struktur:**
```javascript
import { mount } from '@vue/test-utils';
import MyComponent from '@/components/MyComponent.vue';

describe('MyComponent', () => {
  it('renders correctly', () => {
    const wrapper = mount(MyComponent, {
      props: {
        title: 'Test'
      }
    });
    expect(wrapper.find('h1').text()).toBe('Test');
  });
});
```

### Tests ausführen

```bash
# Alle Tests
npm run test

# Mit Coverage
npm run test:coverage
```

---

## 🔀 Git & GitHub

### Commits pushen

```bash
# Lokale Commits pushen
git push origin feat/neue-feature
```

### Upstream aktualisieren

```bash
# Falls main-Branch Updates bekam:
git fetch upstream main
git rebase upstream/main

# Falls Konflikte:
# 1. Dateien bearbeiten
# 2. git add .
# 3. git rebase --continue
```

### PR Merge

```bash
# Nach PR-Approval:
# 1. Im GitHub UI auf "Merge" klicken
# 2. Oder im Terminal:

git checkout main
git pull upstream main
git merge feat/neue-feature
git push origin main
```

---

# 🇬🇧 English

## Developer Guide

Guidelines for developers who want to contribute to the Nextcloud Association App.

---

## 📋 Table of Contents

1. [Setup](#setup-1)
2. [Project Structure](#project-structure)
3. [Development Workflow](#development-workflow-1)
4. [Code Standards](#code-standards-1)
5. [Testing](#testing-1)
6. [Git & GitHub](#git--github-1)

---

## ⚙️ Setup

### Step 1: Fork & Clone Repository

```bash
# 1. Fork on GitHub: https://github.com/Wacken2012/nextcloud-verein/fork

# 2. Clone your fork
git clone https://github.com/YOUR_USERNAME/nextcloud-verein.git
cd nextcloud-verein

# 3. Add upstream
git remote add upstream https://github.com/Wacken2012/nextcloud-verein.git
```

### Step 2: Prepare Local Environment

```bash
# Install dependencies
npm install

# Start development server
npm run dev
```

---

## 📁 Project Structure

```
nextcloud-verein/
├── appinfo/
│   ├── info.xml              # App metadata
│   └── routes.php            # API routes
│
├── js/
│   ├── main.js              # Vue.js app entry
│   ├── theme.scss           # CSS variables & design
│   ├── components/          # Vue components
│   ├── api.js              # API adapter
│   └── dist/               # Built files
│
├── lib/
│   ├── AppInfo/            # Application.php
│   ├── Controller/         # API controllers
│   ├── Db/                # Database models
│   ├── Service/           # Business logic
│   └── Attributes/        # Decorators & middleware
│
├── tests/
│   ├── Unit/              # Unit tests
│   └── Integration/       # Integration tests
│
├── docs/
│   ├── api/               # OpenAPI documentation
│   └── ...
│
├── vite.config.js         # Build configuration
├── package.json           # Dependencies & scripts
└── README.md             # Project overview
```

---

## 🔄 Development Workflow

### 1. Create Feature Branch

```bash
# Update upstream
git fetch upstream main

# Create branch from upstream
git checkout -b feat/new-feature upstream/main
```

### 2. Write Code & Test

```bash
# Dev server should still be running (npm run dev)
# Auto-rebuilds on changes

# Run tests
npm run test

# Check linting
npm run lint
```

### 3. Create Commits

```bash
# Stage changes
git add js/components/MyComponent.vue

# Commit with descriptive message
git commit -m "feat: Add new member validation

- Add email format validation
- Add IBAN format check
- Display error messages to user
- Add unit tests"
```

### 4. Push to Your Fork

```bash
git push origin feat/new-feature
```

### 5. Create Pull Request

```
GitHub → Pull Requests → New Pull Request

Title: feat: Add new member validation

Description:
- What was added?
- Why?
- Screenshots/videos (if UI change)

Checklist:
✅ Tests written
✅ Code formatted
✅ npm run lint passed
✅ Docs updated
```

---

## 📝 Code Standards

### JavaScript/Vue.js

**ESLint runs automatically:**
```bash
npm run lint          # Check
npm run lint:fix      # Auto fix
```

**Formatting:**
```javascript
// ✅ Good: Clear, descriptive names
function calculateMemberTotal(members) {
  return members.reduce((sum, member) => sum + member.fee, 0);
}

// ❌ Bad: Cryptic
function calc(m) {
  return m.reduce((s, item) => s + item.f, 0);
}
```

### Vue Components

**Structure:**
```vue
<template>
  <div class="member-item">
    <h3>{{ member.name }}</h3>
    <button @click="edit">Edit</button>
  </div>
</template>

<script>
export default {
  name: 'MemberItem',
  props: {
    member: {
      type: Object,
      required: true
    }
  },
  data() {
    return {};
  },
  methods: {
    edit() {
      // ...
    }
  }
};
</script>

<style scoped>
.member-item {
  padding: 1rem;
  border-radius: var(--border-radius);
}
</style>
```

### SCSS/CSS

**Use variables:**
```scss
// ✅ Good
.button {
  background: var(--primary-color);
  padding: var(--spacing-md);
  border-radius: var(--border-radius);
}

// ❌ Bad
.button {
  background: #0066cc;
  padding: 1rem;
  border-radius: 4px;
}
```

---

## 🧪 Testing

### Write Unit Tests

```bash
# Create test file
touch tests/unit/MyComponent.spec.js
```

**Test structure:**
```javascript
import { mount } from '@vue/test-utils';
import MyComponent from '@/components/MyComponent.vue';

describe('MyComponent', () => {
  it('renders correctly', () => {
    const wrapper = mount(MyComponent, {
      props: {
        title: 'Test'
      }
    });
    expect(wrapper.find('h1').text()).toBe('Test');
  });
});
```

### Run Tests

```bash
# All tests
npm run test

# With coverage
npm run test:coverage
```

---

## 🔀 Git & GitHub

### Push Commits

```bash
# Push local commits
git push origin feat/new-feature
```

### Update from Upstream

```bash
# If main branch has updates:
git fetch upstream main
git rebase upstream/main

# If conflicts:
# 1. Edit conflicting files
# 2. git add .
# 3. git rebase --continue
```

### Merge PR

```bash
# After PR approval:
# 1. Click "Merge" in GitHub UI
# 2. Or in terminal:

git checkout main
git pull upstream main
git merge feat/new-feature
git push origin main
```

---

**Last Updated:** December 2025  
**App Version:** v0.2.1
