# 🗺️ Roadmap – Nextcloud Vereins-App

Öffentliche Roadmap für die Entwicklung der Nextcloud Vereins-App. Status und geplante Features für die nächsten Versionen.

---

## Deutsch

### 📊 Version Overview

| Version | Status | Release | Fokus |
|---------|--------|---------|-------|
| **v0.1.0-alpha** | ✅ Stabil | Nov 2025 | Basis CRUD, MVP |
| **v0.2.0-beta** | ✅ Released | 30. Nov 2025 | RBAC, Admin-Panel, CSV/PDF Export |
| **v0.2.1** | ✅ Fertig | 30. Nov 2025 | API Docs, Developer Guide, PDF Fix |
| **v0.3.0** | 📋 Geplant | Q2 2026 | Automatisierung, Integrationen (Kalender, Talk, Files, Deck) |
| **v0.4.0** | 📋 Geplant | Q3 2026 | Materialverwaltung (Zeugwart) |
| **v1.0.0** | 🎯 Ziel | Q4 2026 | Production-Ready |

---

## ✅ v0.1.0-alpha (Stabil)

**Release**: November 2025

### Features
- [x] Mitgliederverwaltung (CRUD)
- [x] Gebührenverwaltung (CRUD)
- [x] Responsive Vue 3 UI
- [x] Dark Mode
- [x] Basis API

### Known Issues (alle in v0.2.0 behoben ✅)
- ✅ Rollen & Berechtigungen → v0.2.0 behoben
- ✅ Datenvalidierung → v0.2.0 behoben
- ✅ Export-Funktionalität → v0.2.0 behoben
- 🔄 Benachrichtigungen → v0.3.0 geplant

---

## ✅ v0.2.0-beta (RELEASED — 30. November 2025)

### 🎯 Fokus: Berechtigungen & Datenqualität & Export

**Abgeschlossene Features:**
- [x] **Rollen & Berechtigungen (RBAC)**
  - ✅ Admin: Volle Kontrolle über alle Funktionen
  - ✅ Kassierer: Gebühren verwalten, Export
  - ✅ Mitglied: Nur eigene Daten einsehen
  - ✅ 20+ Unit Tests (100% Pass-Rate)
  - ✅ Audit-Logging für Permission-Violations

- [x] **Input-Validierung & Datensicherheit**
  - ✅ IBAN/BIC Validierung (ISO 13616 + SWIFT ISO 9362)
  - ✅ E-Mail Format & MX-Check (RFC 5322)
  - ✅ SEPA XML Schema Validierung (pain.001)
  - ✅ Sanitizer für alle Eingabefelder (NFKC Unicode)
  - ✅ 69+ Unit Tests (100% Pass-Rate)
  - ✅ Duplikat-Prüfung (IBAN, Email)

- [x] **CSV/PDF Export**
  - ✅ CSV Export mit UTF-8 BOM (Excel-kompatibel)
  - ✅ PDF Export mit TCPDF für professionelle Layouts
  - ✅ 4 Endpunkte (Members CSV/PDF, Fees CSV/PDF)
  - ✅ RBAC-geschützt mit @RequirePermission
  - ✅ 41 Tests (28 Unit + 13 Integration) — 100% Pass-Rate
  - ✅ CSV Endpoints live getestet: HTTP 200 OK ✅
  - ✅ Fehlerbehandlung für leere Datenbanken

- [x] **Admin-Panel & Settings**
  - ✅ Native Nextcloud Settings Integration
  - ✅ Rollen-Management für Benutzer
  - ✅ IAppContainer Dependency Injection

- [x] **Tests & QA**
  - ✅ 130+ Unit Tests (300+ Assertions)
  - ✅ RBAC Tests: 20+
  - ✅ Validation Tests: 69+
  - ✅ Export Tests: 41+
  - ✅ 100% Pass-Rate für alle Tests

### 🔧 Tech Improvements (Abgeschlossen)
- [x] Unit Tests (PHP + Vue) — 130+
- [x] Error Handling
- [x] Security Audit (RBAC, Input Validation)
- [x] Performance Optimierungen

### 📚 Documentation (Abgeschlossen ✅)
- [x] README mit Feature-Übersicht
- [x] ROADMAP mit aktualisiertem Status
- [x] API Documentation (OpenAPI 3.0) ✅ v0.2.1
- [x] Developer Guide (Bilingual DE/EN) ✅ v0.2.1
- [x] CONTRIBUTING.md (Bilingual DE/EN) ✅ v0.2.1

### ✅ Behobene Punkte (v0.2.1)
- ✅ PDF Export: Vollständig funktional (Members + Fees)
- ✅ Documentation: API Docs, Developer Guide erstellt
- ✅ Alle Dokumentation zweisprachig (DE/EN)

---

## 📋 v0.3.0 (Q2 2026 — Automatisierung & Integrationen)

### 🎯 Fokus: Automatisierung & Integrationen

**Geplante Features:**
- [ ] Automatische Mahnungen
  - Cronjob für Beiträge
  - E-Mail Benachrichtigungen
  - Mahnstufen (1., 2., Mahnung)
- [ ] Kalender Integration (nach Vorbild "Konzertmeister")
  - Gebühren-Fristen als Events
  - Terminverwaltung (Versammlungen, Proben, Events, Konzerte)
  - Teilnehmer-Abfrage (RSVP: Zu-/Absage)
  - Anwesenheitsverwaltung & Statistik
  - Wiederkehrende Termine (z.B. wöchentliche Proben)
  - Programm-/Setlist-Planung pro Termin
- [ ] Deck Integration
  - Aufgaben-Management
  - Beitragsabrechnung
- [ ] Direktnachrichten (Talk) (nach Vorbild "Konzertmeister")
  - Benachrichtigungen via Chat
  - Admin-Alerts
  - Termin-Erinnerungen an Mitglieder
  - Automatische Einladungen zu Veranstaltungen
  - Abfrage-Benachrichtigungen (Zu-/Absage anfordern)
  - Gruppen-Kommunikation nach Register/Stimme
- [ ] Files Integration (Notenverwaltung)
  - Noten-Archiv nach Vorbild "SoftNote"
  - Kategorisierung nach Instrument/Stimme
  - Such- und Filterfunktion
  - Verknüpfung mit Mitgliedern (Stimme/Register)
  - PDF-Vorschau und Download

### 🔐 Security & Permissions
- [ ] Erweiterte Rollen mit granularen Berechtigungen:

| Rolle | Dashboard | Mitglieder | Finanzen | Kalender | Talk | Noten (Files) | Einstellungen | Rollenverwaltung |
|-------|-----------|------------|----------|----------|------|---------------|---------------|------------------|
| **Admin** | ✅ Voll | ✅ Voll | ✅ Voll | ✅ Voll | ✅ Voll | ✅ Voll | ✅ Voll | ✅ Voll |
| **Vorstand** | ✅ Voll | ✅ Lesen | ✅ Lesen | ✅ Voll | ✅ Voll | ✅ Lesen | ❌ | ✅ Vergeben* |
| **Kassenwart** | 📊 Finanzen | ❌ | ✅ Voll | ❌ | 📨 Zugewiesen | ❌ | ❌ | ❌ |
| **Notenwart** | 📊 Basis | ❌ | ❌ | ❌ | 📨 Zugewiesen | ✅ Admin | ❌ | ❌ |
| **Mitglied** | 📊 Eigene | ❌ **KEIN ZUGRIFF** | ❌ **KEIN ZUGRIFF** | 📅 Eigene | 📨 Zugewiesen | 📁 **NUR Freigegeben** | ❌ | ❌ |

*Vorstand kann Rollen vergeben, aber keine neuen Rollen erstellen oder Admin-Rechte vergeben

**⚠️ DSGVO-Anforderungen (Pflicht für v1.0):**
- Mitglieder haben **keinen** Zugriff auf Mitgliederliste (Datenschutz)
- Mitglieder haben **keinen** Zugriff auf Finanzen
- Mitglieder sehen **nur** explizit freigegebene Noten
- Alle personenbezogenen Daten nur für berechtigte Rollen sichtbar

- [ ] Rollenverwaltungs-UI für Admin & Vorstand
  - Rollen an Mitglieder zuweisen/entziehen
  - Neue Rollen erstellen (nur Admin)
  - Berechtigungen pro Rolle konfigurieren (nur Admin)
  - Übersicht aller Rollen-Zuweisungen
- [ ] Custom Permissions
- [ ] **DSGVO-Compliance (Pflicht für v1.0)**
  - Einwilligungserklärung für Datenspeicherung
  - Recht auf Auskunft (Datenexport für Mitglied)
  - Recht auf Löschung (Datenlöschung auf Anfrage)
  - Datenminimierung (nur notwendige Daten)
  - Zugriffsprotokollierung (wer hat wann was gesehen)
- [ ] Audit Logs exportierbar

### 💾 Data Export Erwiterungen
- [ ] SEPA XML Export (für Bankentransfers)
- [ ] Excel-Export mit Formeln
- [ ] Jahresabschluss-PDF

### 📊 Statistiken & Reporting
- [ ] Charts & Diagramme
  - Anwesenheitsstatistik im Dashboard (Diagramm)
  - Anwesenheitsquote pro Mitglied
  - Teilnahme-Trends über Zeit
- [ ] Historische Daten
- [ ] Trends & Prognosen

---

## 📋 v0.4.0 (Q3 2026 — Materialverwaltung)

### 🎯 Fokus: Zeugwart & Inventar

**Geplante Features:**
- [ ] Materialverwaltung (Zeugwart-Modul)
  - Inventarliste aller Materialien/Instrumente
  - Status-Tracking (verfügbar, ausgegeben, Reparatur)
  - Ausgabe-Protokoll (wer hat was wann erhalten)
  - Rückgabe-Verwaltung
  - Reparatur-Tracking mit Status & Kosten
  - Wartungs-Erinnerungen
- [ ] Zeugwart-Rolle
  - Zugriff auf Materialverwaltungs-Tab
  - Ausgabe/Rücknahme-Berechtigung
  - Reparatur-Aufträge erstellen

| Rolle | Materialverwaltung |
|-------|-------------------|
| **Admin** | ✅ Voll |
| **Vorstand** | ✅ Lesen |
| **Zeugwart** | ✅ Admin |
| **Mitglied** | 👤 Eigene Ausleihen |

---

## 🎯 v1.0.0 (Q4 2026 — Production Release)

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

### v0.2.1 (alle gelöst ✅)
| Issue | Severity | Status | Fix |
|-------|----------|--------|-----|
| PDF Export (TCPDF Dependency) | 🟡 Medium | ✅ BEHOBEN | v0.2.1 |
| SEPA XML Export | 🟡 Medium | 📋 Geplant | v0.3.0 |

### v0.1.0-alpha (alle in v0.2.0 behoben ✅)
| Issue | Severity | Status | Fix |
|-------|----------|--------|-----|
| Keine Berechtigungen | 🔴 Kritisch | ✅ BEHOBEN | v0.2.0 |
| IBAN nicht validiert | 🟡 Medium | ✅ BEHOBEN | v0.2.0 |
| Kein Export | 🟡 Medium | ✅ BEHOBEN | v0.2.0 |

### Performance
- Bundle-Größe: ~~854 KB~~ → **508 KB** (v0.2.1, 40% Reduktion) ✅
  - gzip: ~~197 KB~~ → **148 KB** (25% Reduktion)
  - Ziel für v1.0: < 200 KB
- Optimierungen v0.2.1:
  - [x] Aggressive Terser-Minification
  - [x] Console.log/debug entfernt in Production
  - [x] Tree-shaking verbessert
- Datenbankqueries: optimiert durch Nextcloud ORM
- Caching-Strategie: Nextcloud-native

### Security (geplant für v1.0)
- [x] Rate Limiting (in Arbeit)
- [x] CSRF Protection (✅ durch Nextcloud)
- [x] Input Sanitization (✅ v0.2.0)
- [x] Output Escaping (✅ Vue 3 automatic)
- [ ] Security Headers (v1.0)

---

## 📈 Metrics & Goals

### Adoption Goals
- **v0.2.0**: 50+ Installationen
- **v0.3.0**: 200+ Installationen
- **v1.0.0**: 500+ Installationen (Ziel)

### Quality Goals
| Metrik | v0.1 | v0.2 | v0.3 | v1.0 |
|--------|------|------|------|------|
| Test Coverage | 0% | 85% | 95% | 100% |
| Bug Response | - | <7 days | <3 days | <1 day |
| Performance | - | < 2s | < 1s | < 500ms |

---

## 🎁 Community Features (Backlog)

Geplant, aber zeitlich nicht gebunden:

- [ ] Mobile App (iOS/Android)
- [ ] Multi-Language Support (i18n)
- [ ] Member Portal (Self-Service)
- [ ] SMS Notifications
- [ ] Payment Gateway Integration (Stripe, PayPal)
- [ ] Advanced Reporting & BI
- [ ] AI-powered Insights

---

---

## English

### 📊 Version Overview

| Version | Status | Release | Focus |
|---------|--------|---------|-------|
| **v0.1.0-alpha** | ✅ Stable | Nov 2025 | Basic CRUD, MVP |
| **v0.2.0-beta** | 🔄 95% complete | Dec 1, 2025 | RBAC, Admin Panel, CSV/PDF Export ✅ |
| **v0.3.0** | 📋 Planned | Q2 2026 | Automation, Integrations |
| **v1.0.0** | 🎯 Goal | Q4 2026 | Production-Ready |

---

## ✅ v0.1.0-alpha (CURRENT — Stable)

**Release**: November 2025

### Features
- [x] Member management (CRUD)
- [x] Fee management (CRUD)
- [x] Responsive Vue 3 UI
- [x] Dark mode
- [x] Basic API

### Known Issues (all fixed in v0.2.0 ✅)
- ✅ Roles & Permissions → v0.2.0 fixed
- ✅ Data Validation → v0.2.0 fixed
- ✅ Export Functionality → v0.2.0 fixed
- 🔄 Notifications → v0.3.0 planned

---

## 🔄 v0.2.0-beta (95% COMPLETE — Release December 1, 2025)

### 🎯 Focus: Permissions & Data Quality & Export

**Completed Features:**
- [x] **Roles & Permissions (RBAC)**
  - ✅ Admin: Full control over all functions
  - ✅ Treasurer: Fee management, export
  - ✅ Member: View own data only
  - ✅ 20+ Unit Tests (100% pass rate)
  - ✅ Audit logging for permission violations

- [x] **Input Validation & Data Security**
  - ✅ IBAN/BIC Validation (ISO 13616 + SWIFT ISO 9362)
  - ✅ Email Format & MX-check (RFC 5322)
  - ✅ SEPA XML Schema Validation (pain.001)
  - ✅ Input Sanitizer (NFKC Unicode)
  - ✅ 69+ Unit Tests (100% pass rate)
  - ✅ Duplicate checking (IBAN, Email)

- [x] **CSV/PDF Export**
  - ✅ CSV Export with UTF-8 BOM (Excel-compatible)
  - ✅ PDF Export with TCPDF for professional layouts
  - ✅ 4 Endpoints (Members CSV/PDF, Fees CSV/PDF)
  - ✅ RBAC-protected with @RequirePermission
  - ✅ 41 Tests (28 unit + 13 integration) — 100% pass rate
  - ✅ CSV endpoints live tested: HTTP 200 OK ✅
  - ✅ Error handling for empty databases

- [x] **Admin Panel & Settings**
  - ✅ Native Nextcloud settings integration
  - ✅ Role management for users
  - ✅ IAppContainer dependency injection

- [x] **Tests & QA**
  - ✅ 130+ Unit Tests (300+ Assertions)
  - ✅ RBAC Tests: 20+
  - ✅ Validation Tests: 69+
  - ✅ Export Tests: 41+
  - ✅ 100% pass rate for all tests

### 🔧 Tech Improvements (Completed)
- [x] Unit Tests (PHP + Vue) — 130+
- [x] Error Handling
- [x] Security Audit (RBAC, Input Validation)
- [x] Performance Optimizations

### 📚 Documentation (In Progress)
- [x] README with feature overview
- [x] ROADMAP with current status
- [ ] API Documentation (Swagger/OpenAPI) — v0.2.1
- [ ] Developer Guide — v0.2.1

### Open Items for Release (minimal)
- 🟡 PDF Export: TCPDF Dependency issue (acceptable for beta)
  - CSV Export works completely ✅
  - Workaround: Use CSV or direct PHP call
  - Target version for fix: v0.2.1
- 🟢 Final QA & Testing (in progress)
- 🟢 Documentation Polish (in progress)

---

## 📋 v0.3.0 (Q2 2026 — Automation & Integrations)

### 🎯 Focus: Automation & Integrations

**Planned Features:**
- [ ] Automated Reminders
  - Cronjob for fees
  - Email notifications
  - Reminder levels (1st, 2nd reminder)
- [ ] Calendar Integration
  - Fee deadlines as events
  - Member management in calendar
- [ ] Deck Integration
  - Task management
  - Contribution accounting
- [ ] Direct Messages (Talk)
  - Notifications via chat
  - Admin alerts

### 🔐 Security & Permissions
- [ ] Advanced Roles
  - Custom permissions
  - Data privacy (GDPR compliance)
  - Exportable audit logs

### 💾 Data Export Extensions
- [ ] SEPA XML Export (for bank transfers)
- [ ] Excel Export with formulas
- [ ] Year-end PDF report

### 📊 Statistics & Reporting
- [ ] Charts & diagrams
- [ ] Historical data
- [ ] Trends & forecasts

---

## 🎯 v1.0.0 (Q4 2026 — Production Release)

### 🎯 Focus: Production Release

**Stability & Polish:**
- [ ] 100% Unit Test Coverage
- [ ] Security Audit (Third-Party)
- [ ] Performance: < 1s load time
- [ ] i18n (Internationalization)
  - English
  - Deutsch
  - Additional languages

### 🌟 New Features
- [ ] Membership Fee Automations
  - Automatic collections (SEPA)
  - Payment plans
  - Cancellations
- [ ] Report Generator
  - Annual reports
  - Treasurer audits
  - Statistics
- [ ] Web Shop (optional)
  - Merchandise sales
  - Ticketing

### 📦 Deployment
- [ ] Nextcloud App Store Release
- [ ] Docker Image
- [ ] Installation script

---

## 🐛 Known Issues & Limitations

### v0.2.0-beta
| Issue | Severity | Workaround | ETA |
|-------|----------|-----------|-----|
| PDF Export (TCPDF Dependency) | 🟡 Medium | Use CSV export | v0.2.1 |
| SEPA XML Export | 🟡 Medium | Manual export | v0.3.0 |

### v0.1.0-alpha (all fixed in v0.2.0 ✅)
| Issue | Severity | Status | Fix |
|-------|----------|--------|-----|
| No Permissions | 🔴 Critical | ✅ FIXED | v0.2.0 |
| IBAN not validated | 🟡 Medium | ✅ FIXED | v0.2.0 |
| No Export | 🟡 Medium | ✅ FIXED | v0.2.0 |

### Performance
- Bundle size: 387 KB (Goal: < 200 KB for v1.0)
- Database queries: optimized via Nextcloud ORM
- Caching strategy: Nextcloud-native

### Security (planned for v1.0)
- [x] Rate Limiting (in progress)
- [x] CSRF Protection (✅ via Nextcloud)
- [x] Input Sanitization (✅ v0.2.0)
- [x] Output Escaping (✅ Vue 3 automatic)
- [ ] Security Headers (v1.0)

---

## 📈 Metrics & Goals

### Adoption Goals
- **v0.2.0**: 50+ installations
- **v0.3.0**: 200+ installations
- **v1.0.0**: 500+ installations (goal)

### Quality Goals
| Metric | v0.1 | v0.2 | v0.3 | v1.0 |
|--------|------|------|------|------|
| Test Coverage | 0% | 85% | 95% | 100% |
| Bug Response | - | <7 days | <3 days | <1 day |
| Performance | - | < 2s | < 1s | < 500ms |

---

## 🎁 Community Features (Backlog)

Planned but not time-bound:

- [ ] Mobile App (iOS/Android)
- [ ] Multi-Language Support (i18n)
- [ ] Member Portal (Self-Service)
- [ ] SMS Notifications
- [ ] Payment Gateway Integration (Stripe, PayPal)
- [ ] Advanced Reporting & BI
- [ ] AI-powered Insights

---

## 🤝 How to Contribute — Deutsch

Du möchtest an dieser Roadmap mitarbeiten?

1. **Feature vorschlagen**: [GitHub Discussions](https://github.com/yourusername/nextcloud-verein/discussions)
2. **Bug melden**: [GitHub Issues](https://github.com/yourusername/nextcloud-verein/issues)
3. **Code beitragen**: [Pull Requests](https://github.com/yourusername/nextcloud-verein/pulls)
4. **Testen**: Download & Feedback geben

---

## 🤝 How to Contribute — English

Want to contribute to this roadmap?

1. **Suggest feature**: [GitHub Discussions](https://github.com/yourusername/nextcloud-verein/discussions)
2. **Report bug**: [GitHub Issues](https://github.com/yourusername/nextcloud-verein/issues)
3. **Contribute code**: [Pull Requests](https://github.com/yourusername/nextcloud-verein/pulls)
4. **Test**: Download & provide feedback

---

## 📅 Timeline

```
2025
├── Nov: v0.1.0-alpha (released, stable)
└── Dec 1: v0.2.0-beta (release scheduled)

2026
├── Q1: v0.2.0-beta finalization
├── Q2: v0.3.0 (Automation, Integrations)
└── Q4: v1.0.0 (Production Release)
```

---

## 💡 Vision — Deutsch

**Langfristig**: Nextcloud Vereins-App soll die **Standard-Lösung** für Vereinsverwaltung in Nextcloud sein – mit modernem UI, stabiler API und aktiver Community.

**Mittelfristig**: Features, die große Vereine brauchen (Automatisierung, Reporting, Integrationen).

**Kurzfristig**: Stabilität, Berechtigungen, gute Dokumentation.

---

## 💡 Vision — English

**Long-term**: Nextcloud Vereins-App should be the **standard solution** for club management in Nextcloud – with modern UI, stable API, and active community.

**Mid-term**: Features that large clubs need (automation, reporting, integrations).

**Short-term**: Stability, permissions, good documentation.

---

## 📞 Feedback

Meinung zu dieser Roadmap? / Opinion about this roadmap?

- 💬 [GitHub Discussions](https://github.com/yourusername/nextcloud-verein/discussions)
- 📧 Email: (your-email@example.com)
- 🐦 Twitter/X: @yourusername

---

**Danke für dein Interesse an der Nextcloud Vereins-App!** 🎉

**Thank you for your interest in the Nextcloud Vereins-App!** 🎉

Zusammen machen wir die beste Vereinsverwaltungs-App! 🚀

Together we're building the best club management app! 🚀
