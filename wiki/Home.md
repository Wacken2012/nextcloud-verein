# Nextcloud Vereins-App – Übersicht

Willkommen zur Nextcloud Vereins-App! 👋

Diese Wiki-Seite gibt dir einen Überblick über die App, ihre Features und wie du sie installierst und nutzt.

---

## 📋 Inhaltsverzeichnis

1. [Einführung](#einführung)
2. [Features](#features)
3. [Installation](#installation)
4. [Dokumentation](#dokumentation)
5. [Roadmap](#roadmap)
6. [Community & Feedback](#community--feedback)
7. [Credits](#credits)

---

## 🎯 Einführung

### Was ist die Nextcloud Vereins-App?

Die **Nextcloud Vereins-App** ist eine spezialisierte Anwendung für die Verwaltung von Vereinen, Clubs und Organisationen direkt in Nextcloud. Sie bietet eine integrierte Plattform für:

- 👥 **Mitgliederverwaltung** - Kontakte, Rollen, IBAN/BIC
- 💰 **Gebührenverwaltung** - Abrechnung, Status-Tracking
- 📊 **Dashboard** - Statistiken und Übersichten
- 📅 **Termine & Aufgaben** - Planung und Organisation
- 📁 **Dokumente** - Zentrale Dateiablage

### Zielgruppe

Die App richtet sich an:

- **Eingetragene Vereine (e.V.)**
- **Clubs und Verbände**
- **Gemeinnützige Organisationen**
- **Kleinere Unternehmensgruppen**
- **Kuriergruppen und Communities**

### Lizenz

```
AGPL-3.0 – Open Source
https://github.com/Wacken2012/nextcloud-verein/blob/main/LICENSE
```

---

## ✨ Features

### 👥 Mitgliederverwaltung

```
✅ Komplett-CRUD (Create, Read, Update, Delete)
✅ Rollenverwaltung (Vorstand, Kassierer, Mitglied, etc.)
✅ IBAN/BIC für SEPA-Transfers
✅ Email, Telefon, Adresse
✅ Dateianhänge (Fotos, Dokumente)
✅ Suchfunktion & Filter
✅ Export zu CSV/PDF
```

**Beispiel-Datenstruktur:**
```javascript
{
  name: "Max Mustermann",
  email: "max@example.com",
  phone: "+49 123 456789",
  address: "Musterstr. 1, 12345 Musterstadt",
  role: "Vorstand",
  iban: "DE89370400440532013000",
  bic: "COBADEFFXXX",
  joinDate: "2023-01-15",
  status: "active"
}
```

### 💰 Gebührenverwaltung

```
✅ Gebührenätze definieren
✅ Status-Tracking (offen, bezahlt, überfällig)
✅ Automatische Mahnungen (in v0.3.0)
✅ SEPA-XML Export (in v0.2.0)
✅ Zahlungshistorie
✅ Abrechnung nach Monat/Jahr
✅ Reports und Statistiken
```

**Status-Workflow:**
```
offen → [Zahlung erhalten] → bezahlt
  ↓
[Nach Fälligkeitsdatum] → überfällig
  ↓
[Mahnung] → gemahnt (in v0.3.0)
```

### 📊 Dashboard mit Statistiken

```
✅ Live-Statistiken
   ├─ Mitgliederanzahl
   ├─ Offene Gebühren
   ├─ Monatliche Einnahmen
   └─ Letzten Aktivitäten

✅ Charts (Chart.js)
   ├─ Balkendiagramme
   ├─ Kreisdiagramme
   ├─ Liniendiagramme
   └─ Zeitreihen

✅ Responsive Layout
   ├─ Desktop: 4-Spalten Grid
   ├─ Tablet: 2-Spalten Grid
   └─ Mobile: 1-Spalten Layout
```

### 📱 Responsive Design

```
🖥️  Desktop (≥1024px)
   ├─ 4-Spalten Grid für Widgets
   ├─ Volle Navigation mit Tab-Labels
   ├─ Sticky Tab-Navigation
   └─ Max-Width Container (1200px)

📱 Tablet (768-1023px)
   ├─ 2-Spalten Grid
   ├─ Kompakte Navigation
   ├─ Responsive Padding
   └─ Touch-freundliche Größen

📱 Mobile (<768px)
   ├─ 1-Spalten Layout
   ├─ Icon-only Navigation
   ├─ Großzügiges Spacing
   └─ Optimierte Touch-Targets (48px+)
```

### 🌙 Dark-Mode & Theme-Integration

```
✅ Automatische Dark-Mode-Erkennung
   └─ @media (prefers-color-scheme: dark)

✅ Nextcloud CSS-Variablen
   ├─ --color-primary
   ├─ --color-background
   ├─ --color-text
   ├─ --color-error
   ├─ --color-success
   └─ ... 20+ weitere Variablen

✅ Light & Dark Mode Farben
   ├─ Automatischer Wechsel
   ├─ Smooth Transitions
   └─ WCAG AAA Kontraste
```

### ♿ Accessibility Features

```
✅ Semantic HTML
   ├─ <nav>, <main>, <section>
   ├─ <button>, <input>, <label>
   └─ Richtige Heading-Hierarchie

✅ ARIA-Labels
   ├─ aria-label für Icons
   ├─ aria-current="page"
   ├─ aria-expanded für Menüs
   └─ aria-describedby für Hilftexte

✅ Keyboard Navigation
   ├─ Tab durch alle Controls
   ├─ Enter zum Aktivieren
   ├─ Escape zum Schließen
   └─ Arrow Keys für Listen

✅ Focus Management
   ├─ Sichtbare Focus-Indikatoren
   ├─ Focus-visible Styling
   └─ Focus-Trap in Modalen

✅ Reduced Motion
   ├─ @media (prefers-reduced-motion: reduce)
   ├─ Keine Auto-Animationen
   └─ Instant Transitions
```

---

## 🚀 Installation

### Voraussetzungen

```
✅ Nextcloud 24.0+
✅ PHP 8.0+
✅ SQLite / MySQL / PostgreSQL
✅ Git
✅ Node.js 16+ (für Development)
✅ npm 7+
```

### Installation (Production)

#### Schritt 1: Clone Repository

```bash
cd /var/www/nextcloud/apps/
git clone https://github.com/Wacken2012/nextcloud-verein.git verein
cd verein
```

#### Schritt 2: Dependencies installieren

```bash
npm install
```

#### Schritt 3: Production Build

```bash
npm run build
```

#### Schritt 4: App aktivieren

```bash
sudo -u www-data php occ app:enable verein
```

#### Schritt 5: Nextcloud Cache leeren

```bash
sudo -u www-data php occ maintenance:mode --off
```

### Verifizierung

```bash
# App sollte unter Admin → Apps → Installed Apps sichtbar sein
# Und in der App-Navigation verfügbar sein
curl -u admin:PASSWORD http://localhost/nextcloud/index.php/apps/verein/
```

### Installation (Development)

```bash
# Repository klonen
git clone https://github.com/Wacken2012/nextcloud-verein.git verein
cd verein

# Dependencies installieren
npm install

# Development Server starten (mit Hot-Reload)
npm run dev

# Oder einmalig bauen
npm run build

# App mit Nextcloud verlinken (symlink)
ln -s /home/developer/nextcloud-verein /var/www/nextcloud/apps/verein
```

---

## 📚 Dokumentation

Die Dokumentation ist im Repository unter verschiedenen Markdown-Dateien verfügbar:

### 📖 Hauptdokumentation

| Datei | Inhalt |
|-------|--------|
| **README.md** | Features, Quickstart, Links |
| **INSTALLATION.md** | Detaillierte Setup-Anleitung & Troubleshooting |
| **ROADMAP.md** | Geplante Features für zukünftige Versionen |
| **RELEASE_NOTES.md** | Versionshistorie & Changelogs |

### 🎨 Design & Layout

| Datei | Inhalt |
|-------|--------|
| **RESPONSIVE_LAYOUT.md** | Responsive Breakpoints & CSS-Variablen |
| **IMPLEMENTATION_STATUS.md** | Status, Metriken, nächste Schritte |
| **QUICK_START.md** | Quick Reference Guide |

### 🛠️ Entwicklung

| Datei | Inhalt |
|-------|--------|
| **DEVELOPMENT.md** | Entwicklungsrichtlinien & Best Practices |
| **FEATURES_SUMMARY.md** | Übersicht aller Features |
| **PROGRESS.md** | Aktueller Entwicklungs-Status |

### 🧪 Testing & Quality

| Datei | Inhalt |
|-------|--------|
| **TROUBLESHOOTING.md** | Häufige Probleme & Lösungen |
| **TESTING.md** | Test-Strategie & -Verfahren |

---

## 🗺️ Roadmap

### ✅ Abgeschlossen (v0.1.0-alpha)

- [x] Vue 3 Frontend Setup
- [x] Mitgliederverwaltung (CRUD)
- [x] Gebührenverwaltung (CRUD)
- [x] Dashboard mit Stats
- [x] Responsive Layout (Desktop/Tablet/Mobile)
- [x] Dark-Mode Support
- [x] Nextcloud Theme-Integration
- [x] Accessibility Features (WCAG AA)

### 🚧 In Arbeit (v0.2.0-beta)

- [ ] Rollen & Berechtigungen (Owner, Admin, Member)
- [ ] Input-Validierungen (Email, IBAN, etc.)
- [ ] Unit-Tests (Vue Components)
- [ ] E2E-Tests (Cypress)
- [ ] SEPA-XML Export
- [ ] CSV Import/Export
- [ ] API Dokumentation
- [ ] Admin-Settings Seite

### 📋 Geplant (v0.3.0)

- [ ] Automatische Mahnungen
- [ ] Kalender-Integration (Nextcloud Calendar)
- [ ] Push-Notifications
- [ ] Neue Komponenten (Datei-Upload, etc.)
- [ ] Mehrsprachigkeit (i18n)
- [ ] Performance-Optimierungen
- [ ] Audit-Logs

### 🎯 Zielversion (v1.0.0)

- [ ] App-Store Release (Nextcloud App Store)
- [ ] 100% Test Coverage
- [ ] Production-Ready Datenbank-Migration
- [ ] Admin-Dashboard
- [ ] Fortgeschrittene Berechtigungen (ACL)
- [ ] Webhooks & API
- [ ] Docker-Image

---

## 💬 Community & Feedback

### 🐛 Bug-Reports & Feature Requests

```
GitHub Issues: https://github.com/Wacken2012/nextcloud-verein/issues

Bitte berichtet:
- App-Version
- Nextcloud-Version
- Browser & OS
- Reproduktionsschritte
- Screenshots wenn möglich
```

### 💡 Diskussionen & Ideen

```
GitHub Discussions: https://github.com/Wacken2012/nextcloud-verein/discussions

Kategorien:
- Q&A: Fragen zur Nutzung
- Ideas: Feature-Ideen
- Announcements: Neue Versionen
- General: Allgemeine Unterhaltung
```

### 🌐 Community-Forum

```
Nextcloud Help Forum:
https://help.nextcloud.com/

Sucht nach "Vereins-App" oder erstellt einen neuen Post!
```

### 🤝 Beitragen

Interessiert an Mitarbeit?

```
1. Repository forken
2. Feature-Branch erstellen (git checkout -b feature/xyz)
3. Commits mit aussagekräftigen Messages
4. Push zum Fork (git push origin feature/xyz)
5. Pull Request erstellen
6. Code-Review abwarten

Richtlinien: siehe DEVELOPMENT.md
```

---

## 🙏 Credits

### 👨‍💻 Autor

**Stefan** – Hauptentwickler & Projektleiter

### 🤖 Unterstützt durch

- **GitHub Copilot** – AI-gestützte Code-Generierung
- **Nextcloud Community** – Feedback & Testing
- **Open Source Community** – Libraries & Inspiration

### 📚 Libraries & Tools

```
Vue.js 3 – Progressive JavaScript Framework
Vite – Lightning fast build tool
Sass – CSS Preprocessor
Chart.js – JavaScript Charts
Axios – HTTP Client
```

### 📄 Lizenz

```
AGPL-3.0 License
Copyright (c) 2024 Stefan

Weitere Infos: LICENSE
```

---

## 🚀 Quick Links

| Link | Zweck |
|------|-------|
| [GitHub Repository](https://github.com/Wacken2012/nextcloud-verein) | Quellcode & Issues |
| [README.md](./README.md) | Features & Quickstart |
| [INSTALLATION.md](./INSTALLATION.md) | Installationsanleitung |
| [ROADMAP.md](./ROADMAP.md) | Zukünftige Features |
| [Issues](https://github.com/Wacken2012/nextcloud-verein/issues) | Bug-Reports |
| [Discussions](https://github.com/Wacken2012/nextcloud-verein/discussions) | Ideen & Fragen |

---

## ❓ Häufig gestellte Fragen (FAQ)

### Kostet die App etwas?

**Nein!** Die App ist kostenlos und Open Source (AGPL-3.0).

### Kann ich die App selbst hosten?

**Ja!** Du benötigst nur eine Nextcloud-Installation (selbst gehostet oder bei einem Provider).

### Gibt es eine Demo?

**Ja!** Kontaktiere den Autor für Zugang zur Demo-Instanz.

### Wie sicher ist die App?

- ✅ Alle Daten bleiben in deiner Nextcloud
- ✅ HTTPS-Verschlüsselung
- ✅ Nächste Rollen- & Berechtigungen (ab v0.2.0)
- ✅ Regelmäßige Security-Audits

### Kann ich die App anpassen?

**Ja!** Der Quellcode ist offen und kann angepasst werden.

---

**Viel Spaß mit der Nextcloud Vereins-App! 🚀**

---

**Letzte Aktualisierung:** November 2024  
**App-Version:** 0.1.0-alpha  
**Status:** Beta / Community Feedback Phase
