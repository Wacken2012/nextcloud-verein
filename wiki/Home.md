# 🤝 Nextcloud Vereins-App – Wiki

> 🇩🇪 [Deutsch](#deutsch) | 🇬🇧 [English](#english)

---

# 🇩🇪 Deutsch

## Willkommen! 👋

Dies ist das offizielle Wiki der Nextcloud Vereins-App – einer modernen, benutzerfreundlichen Anwendung zur Verwaltung von Vereinen, Clubs und Organisationen direkt in Nextcloud.

---

## 📋 Inhaltsverzeichnis

1. [Über die App](#über-die-app)
2. [Aktueller Status](#aktueller-status)
3. [Features](#features)
4. [Wiki-Navigation](#wiki-navigation)
5. [Community & Support](#community--support)

---

## 🎯 Über die App

### Was ist die Nextcloud Vereins-App?

Die **Nextcloud Vereins-App** ist eine spezialisierte Anwendung für die Verwaltung von Vereinen, Clubs und Organisationen. Sie bietet eine integrierte Plattform für:

- 👥 **Mitgliederverwaltung** — Kontakte, Rollen, IBAN/BIC, Validierung
- 💰 **Gebührenverwaltung** — Beiträge, Status-Tracking, SEPA-Export
- 📊 **Dashboard** — Live-Statistiken mit Charts
- 🔐 **RBAC** — Rollenbasierte Zugriffskontrolle (Admin, Kassenwart, Mitglied)
- 📄 **Export** — CSV und PDF Export für Mitglieder und Gebühren
- 🌙 **Dark-Mode** — Automatische Nextcloud Theme-Integration

### Zielgruppe

- **Eingetragene Vereine (e.V.)**
- **Clubs und Verbände**
- **Gemeinnützige Organisationen**
- **Musikvereine und Orchester**
- **Sportvereine**

### Lizenz

```
AGPL-3.0 – Open Source
https://github.com/Wacken2012/nextcloud-verein/blob/main/LICENSE
```

---

## 📊 Aktueller Status

| Version | Status | Release | Fokus |
|---------|--------|---------|-------|
| **v0.1.0-alpha** | ✅ Stabil | Nov 2025 | Basis CRUD, MVP |
| **v0.2.0-beta** | ✅ Released | 30. Nov 2025 | RBAC, Admin-Panel, CSV/PDF Export, Statistics |
| **v0.2.1** | ✅ **Aktuell** | 1. Dez 2025 | API Docs, Developer Guide, Bundle-Optimierung |
| **v0.3.0** | 📋 Geplant | Q2 2026 | Kalender, Talk, Files, Deck Integration |
| **v1.0.0** | 🎯 Ziel | Q4 2026 | Production-Ready, App Store Release |

### Was ist neu in v0.2.1?

- ✅ **API-Dokumentation** — Vollständige OpenAPI 3.0 Spezifikation
- ✅ **Entwicklerhandbuch** — Architektur-Übersicht und Best Practices
- ✅ **Bundle-Optimierung** — 854KB → 508KB (40% Reduktion)
- ✅ **Zweisprachige Dokumentation** — Alle Dokumente DE/EN
- ✅ **PDF-Export** — Voll funktionsfähig mit TCPDF

### Was kam in v0.2.0-beta?

- ✅ **Role-Based Access Control (RBAC)** — Admin, Kassenwart, Mitglied Rollen
- ✅ **Admin-Panel** — Native Nextcloud Settings Integration
- ✅ **Datenvalidierung** — IBAN/BIC, Email, SEPA XML Validierung
- ✅ **CSV/PDF Export** — Mitglieder und Gebühren exportieren
- ✅ **Dashboard-Statistiken** — 4 Kacheln mit Live-Daten und Charts
- ✅ **69 Unit Tests** — 100% Pass-Rate

---

## ✨ Features

### 👥 Mitgliederverwaltung

```
✅ Komplett-CRUD (Create, Read, Update, Delete)
✅ Rollenverwaltung (Admin, Kassenwart, Mitglied)
✅ IBAN/BIC Validierung (ISO 13616 + SWIFT)
✅ Email Validierung (RFC 5322 + MX-Check)
✅ Export: CSV (UTF-8 BOM) und PDF
✅ Responsive Tabelle mit Suche & Filter
```

### 💰 Gebührenverwaltung

```
✅ Gebührensätze definieren
✅ Status-Tracking (offen, bezahlt, überfällig)
✅ SEPA-XML Export (pain.001)
✅ CSV/PDF Export
✅ Dashboard-Integration mit Charts
```

### 📊 Dashboard & Statistiken

```
✅ 4 Dashboard-Kacheln mit Live-Daten
✅ Chart.js Integration (Balken, Kreis, Linien)
✅ Mitglieder- und Gebührenstatistiken
✅ Responsive Layout (Desktop/Tablet/Mobile)
```

### 🔐 Sicherheit & RBAC

```
✅ 3 Rollen: Admin, Kassenwart, Mitglied
✅ @RequirePermission Decorators (31 Methoden)
✅ HTTP 403 bei fehlenden Berechtigungen
✅ Audit-Logging für Permission-Violations
✅ Input-Sanitization auf allen Endpoints
```

---

## 📚 Wiki-Navigation

| Seite | Inhalt |
|-------|--------|
| **[Home](Home)** | Übersicht (diese Seite) |
| **[Installation](Installation)** | Setup-Anleitung für Production & Development |
| **[Development](Development)** | Entwickler-Guide, Code-Standards, Testing |
| **[FAQ](FAQ)** | Häufig gestellte Fragen |

### Weitere Dokumentation

| Datei | Inhalt |
|-------|--------|
| [README.md](https://github.com/Wacken2012/nextcloud-verein/blob/main/README.md) | Projekt-Übersicht |
| [ROADMAP.md](https://github.com/Wacken2012/nextcloud-verein/blob/main/ROADMAP.md) | Zukünftige Features & Anforderungen |
| [DEVELOPER_GUIDE.md](https://github.com/Wacken2012/nextcloud-verein/blob/main/DEVELOPER_GUIDE.md) | Architektur & API-Dokumentation |
| [CONTRIBUTING.md](https://github.com/Wacken2012/nextcloud-verein/blob/main/CONTRIBUTING.md) | Contributor-Guidelines |

---

## 💬 Community & Support

### 🐛 Bug-Reports

```
GitHub Issues: https://github.com/Wacken2012/nextcloud-verein/issues

Bitte angeben:
- App-Version (z.B. v0.2.1)
- Nextcloud-Version
- Browser & Betriebssystem
- Reproduktionsschritte
- Screenshots (wenn möglich)
```

### 💡 Feature-Wünsche & Diskussionen

```
GitHub Discussions: https://github.com/Wacken2012/nextcloud-verein/discussions

Kategorien:
- Q&A: Fragen zur Nutzung
- Ideas: Feature-Ideen
- Announcements: Neue Versionen
- General: Allgemeines
```

### 🤝 Beitragen

Interessiert an Mitarbeit? Siehe [CONTRIBUTING.md](https://github.com/Wacken2012/nextcloud-verein/blob/main/CONTRIBUTING.md)!

---

## 🙏 Credits

**Entwickelt von:** Stefan Schulz  
**Unterstützt durch:** GitHub Copilot, Nextcloud Community  
**Lizenz:** AGPL-3.0

---

# 🇬🇧 English

## Welcome! 👋

This is the official Wiki for the Nextcloud Association App – a modern, user-friendly application for managing clubs, associations, and organizations directly in Nextcloud.

---

## 📋 Table of Contents

1. [About the App](#about-the-app)
2. [Current Status](#current-status)
3. [Features](#features-1)
4. [Wiki Navigation](#wiki-navigation-1)
5. [Community & Support](#community--support-1)

---

## 🎯 About the App

### What is the Nextcloud Association App?

The **Nextcloud Association App** is a specialized application for managing clubs, associations, and organizations. It provides an integrated platform for:

- 👥 **Member Management** — Contacts, roles, IBAN/BIC, validation
- 💰 **Fee Management** — Dues, status tracking, SEPA export
- 📊 **Dashboard** — Live statistics with charts
- 🔐 **RBAC** — Role-based access control (Admin, Treasurer, Member)
- 📄 **Export** — CSV and PDF export for members and fees
- 🌙 **Dark Mode** — Automatic Nextcloud theme integration

### Target Audience

- **Registered associations**
- **Clubs and federations**
- **Non-profit organizations**
- **Music associations and orchestras**
- **Sports clubs**

### License

```
AGPL-3.0 – Open Source
https://github.com/Wacken2012/nextcloud-verein/blob/main/LICENSE
```

---

## 📊 Current Status

| Version | Status | Release | Focus |
|---------|--------|---------|-------|
| **v0.1.0-alpha** | ✅ Stable | Nov 2025 | Basic CRUD, MVP |
| **v0.2.0-beta** | ✅ Released | Nov 30, 2025 | RBAC, Admin Panel, CSV/PDF Export, Statistics |
| **v0.2.1** | ✅ **Current** | Dec 1, 2025 | API Docs, Developer Guide, Bundle Optimization |
| **v0.3.0** | 📋 Planned | Q2 2026 | Calendar, Talk, Files, Deck Integration |
| **v1.0.0** | 🎯 Goal | Q4 2026 | Production-Ready, App Store Release |

### What's New in v0.2.1?

- ✅ **API Documentation** — Complete OpenAPI 3.0 specification
- ✅ **Developer Guide** — Architecture overview and best practices
- ✅ **Bundle Optimization** — 854KB → 508KB (40% reduction)
- ✅ **Bilingual Documentation** — All docs in DE/EN
- ✅ **PDF Export** — Fully functional with TCPDF

### What came in v0.2.0-beta?

- ✅ **Role-Based Access Control (RBAC)** — Admin, Treasurer, Member roles
- ✅ **Admin Panel** — Native Nextcloud Settings integration
- ✅ **Data Validation** — IBAN/BIC, Email, SEPA XML validation
- ✅ **CSV/PDF Export** — Export members and fees
- ✅ **Dashboard Statistics** — 4 tiles with live data and charts
- ✅ **69 Unit Tests** — 100% pass rate

---

## ✨ Features

### 👥 Member Management

```
✅ Complete CRUD (Create, Read, Update, Delete)
✅ Role management (Admin, Treasurer, Member)
✅ IBAN/BIC validation (ISO 13616 + SWIFT)
✅ Email validation (RFC 5322 + MX check)
✅ Export: CSV (UTF-8 BOM) and PDF
✅ Responsive table with search & filter
```

### 💰 Fee Management

```
✅ Define fee rates
✅ Status tracking (open, paid, overdue)
✅ SEPA XML export (pain.001)
✅ CSV/PDF export
✅ Dashboard integration with charts
```

### 📊 Dashboard & Statistics

```
✅ 4 dashboard tiles with live data
✅ Chart.js integration (bar, pie, line)
✅ Member and fee statistics
✅ Responsive layout (Desktop/Tablet/Mobile)
```

### 🔐 Security & RBAC

```
✅ 3 roles: Admin, Treasurer, Member
✅ @RequirePermission decorators (31 methods)
✅ HTTP 403 for missing permissions
✅ Audit logging for permission violations
✅ Input sanitization on all endpoints
```

---

## 📚 Wiki Navigation

| Page | Content |
|------|---------|
| **[Home](Home)** | Overview (this page) |
| **[Installation](Installation)** | Setup guide for production & development |
| **[Development](Development)** | Developer guide, code standards, testing |
| **[FAQ](FAQ)** | Frequently asked questions |

### Additional Documentation

| File | Content |
|------|---------|
| [README.md](https://github.com/Wacken2012/nextcloud-verein/blob/main/README.md) | Project overview |
| [ROADMAP.md](https://github.com/Wacken2012/nextcloud-verein/blob/main/ROADMAP.md) | Future features & requirements |
| [DEVELOPER_GUIDE.md](https://github.com/Wacken2012/nextcloud-verein/blob/main/DEVELOPER_GUIDE.md) | Architecture & API documentation |
| [CONTRIBUTING.md](https://github.com/Wacken2012/nextcloud-verein/blob/main/CONTRIBUTING.md) | Contributor guidelines |

---

## 💬 Community & Support

### 🐛 Bug Reports

```
GitHub Issues: https://github.com/Wacken2012/nextcloud-verein/issues

Please include:
- App version (e.g., v0.2.1)
- Nextcloud version
- Browser & operating system
- Steps to reproduce
- Screenshots (if possible)
```

### 💡 Feature Requests & Discussions

```
GitHub Discussions: https://github.com/Wacken2012/nextcloud-verein/discussions

Categories:
- Q&A: Usage questions
- Ideas: Feature ideas
- Announcements: New versions
- General: General discussion
```

### 🤝 Contributing

Interested in contributing? See [CONTRIBUTING.md](https://github.com/Wacken2012/nextcloud-verein/blob/main/CONTRIBUTING.md)!

---

## 🙏 Credits

**Developed by:** Stefan Schulz  
**Supported by:** GitHub Copilot, Nextcloud Community  
**License:** AGPL-3.0

---

**Last Updated:** December 2025  
**App Version:** v0.2.1  
**Status:** Beta / Community Feedback Phase
