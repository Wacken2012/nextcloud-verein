# Development Guide

Anleitung für Entwickler die zur Nextcloud Vereins-App beitragen möchten.

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

# 3. Upstream hinzufügen (um Updates zu bekommen)
git remote add upstream https://github.com/Wacken2012/nextcloud-verein.git
```

### Schritt 2: Lokale Umgebung vorbereiten

```bash
# Dependencies installieren
npm install

# Entwicklungs-Server starten
npm run dev

# In anderem Terminal: Nextcloud starten
cd /var/www/nextcloud
sudo -u www-data php occ app:enable verein
sudo -u www-data php occ cache:clear-all

# Browser öffnen
open http://localhost/nextcloud/index.php/apps/verein/
```

### Schritt 3: Symlink erstellen (optional)

```bash
# Symlink zur Entwicklungsversion erstellen
ln -s ~/projects/nextcloud-verein /var/www/nextcloud/apps/verein-dev

# App aktivieren
sudo -u www-data php /var/www/nextcloud/occ app:enable verein-dev

# Browser: http://localhost/nextcloud/index.php/apps/verein-dev/
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
│   │   ├── App.vue          # Main Container
│   │   ├── Statistics.vue   # Dashboard/Stats
│   │   ├── Members.vue      # Mitgliederliste
│   │   ├── Finance.vue      # Gebührenverwaltung
│   │   ├── Alert.vue        # Alert-Komponente
│   │   └── Modal.vue        # Modal-Dialog
│   │
│   ├── api/
│   │   ├── members.js       # Members-API
│   │   └── finance.js       # Finance-API
│   │
│   └── dist/                # Gebuildete Dateien (generated)
│       ├── nextcloud-verein.mjs
│       └── style.css
│
├── src/                     # PHP Backend (zukünftig)
│   └── ...
│
├── tests/                   # Unit Tests
│   ├── unit/
│   │   └── App.spec.js
│   └── e2e/
│       └── app.e2e.js
│
├── docs/
│   ├── DEVELOPMENT.md       # Diese Datei
│   ├── ARCHITECTURE.md      # Architektur-Übersicht
│   └── API.md              # API-Dokumentation
│
├── vite.config.js           # Build-Konfiguration
├── package.json             # Dependencies & Scripts
├── .eslintrc.js            # Linting-Regeln
└── README.md               # Projekt-Übersicht
```

---

## 🔄 Entwicklungs-Workflow

### 1. Feature-Branch erstellen

```bash
# Upstream aktualisieren
git fetch upstream main

# Branch von upstream erstellen
git checkout -b feature/meine-feature upstream/main

# z.B. für neue Komponente:
git checkout -b feat/validation-rules upstream/main
```

### 2. Code schreiben & testen

```bash
# Entwicklungs-Server sollte noch laufen (npm run dev)
# Bei Änderungen wird automatisch neu gebuildert

# Code bearbeiten:
vim js/components/MyComponent.vue

# Tests schreiben:
vim tests/unit/MyComponent.spec.js

# Tests ausführen:
npm run test
```

### 3. Commits erstellen

```bash
# Änderungen stagen
git add js/components/MyComponent.vue

# Mit aussagekräftiger Nachricht committen
git commit -m "feat: Add validation for member input

- Add email validation
- Add phone number validation
- Display error messages to user
- Add unit tests for validators"
```

**Commit Message Format:**
```
<type>(<scope>): <subject>

<body>

<footer>
```

**Types:**
```
feat:     Neue Funktionalität
fix:      Bug-Behebung
refactor: Code-Umstrukturierung
perf:     Performance-Verbesserung
docs:     Dokumentation
test:     Tests
style:    Formatierung/Styling
```

### 4. Zu eigenem Fork pushen

```bash
git push origin feature/meine-feature
```

### 5. Pull Request erstellen

```
GitHub → Pull Requests → New Pull Request

Title: feat: Add input validation

Description:
- Was wurde hinzugefügt?
- Warum?
- Screenshots/Videos (wenn UI-Änderung)
- Checklist:
  ✅ Tests geschrieben
  ✅ Code formatiert
  ✅ Doku aktualisiert
```

---

## 📝 Code-Standards

### JavaScript/Vue.js

**ESLint** läuft automatisch:
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

**Structure:**
```vue
<template>
  <!-- HTML hier -->
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
    return {
      // reactive data
    };
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

// ❌ Schlecht: Hardcoded
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

  it('handles click', async () => {
    const wrapper = mount(MyComponent);
    await wrapper.find('button').trigger('click');
    expect(wrapper.emitted('click')).toBeTruthy();
  });
});
```

### Tests ausführen

```bash
# Alle Tests
npm run test

# Bestimmte Datei
npm run test tests/unit/MyComponent.spec.js

# Mit Coverage
npm run test:coverage
```

---

## 🔀 Git & GitHub

### Commits pushen

```bash
# Lokale Commits pushen
git push origin feature/meine-feature

# Wenn Branch nicht existiert, Push wird erstellt:
# remote: Create a new pull request for 'feature/meine-feature':
```

### Upstream aktualisieren

```bash
# Wenn main-Branch Updates bekam:
git fetch upstream main
git rebase upstream/main

# Falls Konflikte:
# 1. Dateien bearbeiten und Konflikte beheben
# 2. git add .
# 3. git rebase --continue
```

### PR Review-Feedback beheben

```bash
# Feedback erhält man in der PR

# Lokal Änderung machen
vim js/components/MyComponent.vue

# Commit (kein new commit nötig!)
git add .
git commit --amend --no-edit

# Kraftvoll pushen
git push origin feature/meine-feature --force
```

### Merge in main

```bash
# Nach PR-Approval:
# 1. Im GitHub UI auf "Merge" klicken
# ODER Terminal:

git checkout main
git pull upstream main
git merge feature/meine-feature
git push origin main
```

---

## 📚 Weitere Ressourcen

### Dokumentation
- [Architecture.md](./Architecture.md) - System-Design
- [API.md](./API.md) - REST-API Dokumentation
- [FAQ.md](./FAQ.md) - Häufige Fragen

### Links
- [Vue.js Guide](https://vuejs.org/guide/)
- [Nextcloud API](https://docs.nextcloud.com/server/latest/developer_manual/)
- [Git Book](https://git-scm.com/book/de/)

---

## ✅ Pre-Commit Checklist

Vor Push → PR:

```
✅ npm run lint      (0 Fehler)
✅ npm run test      (Alle Tests green)
✅ npm run build     (Build erfolgreich)
✅ Code-Review selber (Sinn ergibt es?)
✅ Commit-Message aussagekräftig
✅ Changes dokumentiert
✅ Screenshots beigefügt (wenn UI)
```

---

**Danke dass du beiträgst! 🙏**

Bei Fragen → [GitHub Issues](https://github.com/Wacken2012/nextcloud-verein/issues)
