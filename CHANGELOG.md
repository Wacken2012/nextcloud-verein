# Changelog - Nextcloud Verein App

Alle wichtigen Änderungen dieser App werden in dieser Datei dokumentiert.

Das Format basiert auf [Keep a Changelog](https://keepachangelog.com/de/) und folgt [Semantic Versioning](https://semver.org/lang/de/).

---

## [0.2.2-pre-release] - 2025-12-04

### 🐛 Bug Fixes

#### API 500 Error Fixes
- **ILogger Deprecation**: Ersetzt veralteten `ILogger` durch `Psr\Log\LoggerInterface` (Nextcloud 32 Kompatibilität)
- **Request Body Parsing**: Verwendet `file_get_contents('php://input')` statt nicht existierendem `getBody()`
- **PrivacyService DI**: Ersetzt nicht existierenden `SettingService` durch `IConfig`
- **Syntax Error**: Korrigiert `this->` zu `$this->` in ReminderService
- **Export Endpoint**: Akzeptiert jetzt `string|int` für memberId (unterstützt Nextcloud User-IDs)
- **ReminderLog.vue**: Behandelt sowohl Array- als auch Objekt-API-Antworten

#### Betroffene Endpoints (jetzt funktional)
- `POST /api/v1/reminders/config` - Mahnung Konfiguration speichern
- `GET /api/v1/reminders/log` - Mahnung Protokoll abrufen
- `GET /api/v1/privacy/export/{id}` - DSGVO Datenexport
- `GET /api/v1/privacy/consent/{id}` - Einwilligungsstatus abrufen
- `GET /api/v1/privacy/policy` - Datenschutzerklärung abrufen

### 🔧 Technical Changes
- PrivacyService Registration mit korrekten Dependencies in Application.php
- Union Types für flexible Parameter-Handling
- Verbesserte Error-Response-Typen

---

## [0.2.0-beta] - 2025-12-01

### ✨ Features

#### Dashboard Statistics (Neu)
- **Mitgliederstatistiken**: Dashboard-Kachel mit aktueller Mitgliederzahl, Rollenverteilung, Neumitglieder im Monat
- **Gebührenstatistiken**: Kacheln für offene Gebühren, bezahlte Gebühren, überfällige & fällige Gebühren
- **API-Endpoints**: `/statistics/members` und `/statistics/fees` für Live-Datenabfrage
- **Vue.js 3 Frontend**: Reaktive Komponente mit automatischem Daten-Refresh

#### Role-Based Access Control (Neu)
- **Rollen-System**: Admin, Kassierer, Mitglied mit granularen Berechtigungen
- **31 geschützte API-Endpoints**: @RequirePermission Decorators auf alle kritischen Methoden
- **Admin-Panel Integration**: Native Rollen- und Permission-Verwaltung in Nextcloud Settings
- **AuthorizationMiddleware**: Automatische Permission-Überprüfung vor Action-Ausführung
- **Audit-Logging**: Permission-Violations werden protokolliert

#### CSV/PDF Export
- **CSV Export**: UTF-8 BOM, Semikolon-Trennzeichen, Excel-kompatibel
- **PDF Export**: Professionelle Layouts mit TCPDF
- **4 Endpunkte**: `/export/members/csv`, `/export/members/pdf`, `/export/fees/csv`, `/export/fees/pdf`
- **Sonderzeichen-Handling**: Korrekte Behandlung von Umlauten, Anführungszeichen, Sonderzeichen
- **Error Handling**: Aussagekräftige Fehlermeldungen bei Problemen

#### Datenvalidierung
- **IBAN/BIC Validierung**: ISO 13616 & SWIFT ISO 9362 Compliance
- **E-Mail Validierung**: RFC 5322 Format + optionaler MX-Record Check
- **SEPA XML Validierung**: pain.001 Schema Compliance
- **Eingabe-Sanitization**: NFKC Unicode-Normalisierung

### 🐛 Bug Fixes
- CSV Export mit korrekten Member-Namen statt firstname/lastname Split
- Fee Export ohne fehlende getMemberName() Methode
- Statistics Service mit korrekter DateTime-Behandlung für fällige Gebühren
- ExportController mit verbessertem Error-Handling und Logging

### 🔒 Sicherheit
- @RequirePermission Decorators auf allen kritischen Endpoints
- Input-Sanitization für alle POST/PUT Requests
- Permission-basierte API-Zugriffskontrolle
- HTTP 403 Forbidden bei fehlenden Berechtigungen

### 📊 Testing
- 20+ Unit Tests für RBAC-System
- 69+ Unit Tests für Datenvalidierung
- 41+ Unit Tests für Export-Services
- Manuelle Tests mit realistischen Daten (15 Mitglieder, 23 Gebühren)
- Edge-Cases: Sonderzeichen, lange Namen, leere DB

### 📝 Documentation
- README.md mit vollständiger Feature-Dokumentation
- ROADMAP.md mit Versionsplanung
- DEVELOPMENT_STATUS_v0.2.0-beta.md mit technischem Status
- API-Dokumentation für neue Endpoints
- Installationsanleitung aktualisiert

### 🔄 Breaking Changes
- Keine Breaking Changes aus v0.1.0 (Vollständig rückwärts-kompatibel)

### 🚀 Performance
- Optimierte Datenbank-Queries in Statistics Service
- Caching-Headers für statische Assets
- Gzip-Kompression für Frontend Assets (196 KB → 4.5 KB)

### 📦 Dependencies
- Weiterhin: Nextcloud AppFramework, Vue.js 3, Chart.js 4
- Optional: TCPDF für PDF-Export (geplant für v0.2.1)
- DevDependencies: Vite 4, Sass, TypeScript

### 🙏 Known Issues
- PDF-Export (TCPDF) noch nicht vollständig integriert → geplant für v0.2.1
- Admin-Panel Rollen-Verwaltung UI noch einfach → wird in v0.2.1 erweitert
- Keine Mehrsprachigkeit für Dashboard-Labels → geplant für v0.3.0

### 📋 Upgrade-Hinweise
- Keine Datenbank-Migrations nötig
- Bestehende Rollen werden automatisch importiert
- CSV/PDF Export funktioniert auch ohne Rollen-Setup (Fallback zu Member-Rolle)

---

## [0.1.0-alpha] - 2025-11-15

### ✨ Features
- **Mitgliederverwaltung**: CRUD für Mitglieder mit Name, Email, IBAN, BIC, Rolle
- **Gebührenverwaltung**: CRUD für Gebühren mit Amount, Status, Due Date
- **SEPA Export**: Generiert pain.001 XML für Bankübertragungen
- **Dashboard**: Überblick über Mitglieder- und Gebührenstatistiken
- **API Endpoints**: 15+ REST-Endpoints für Mobile/External Clients
- **Nextcloud Integration**: Native App im App-Menü, Settings-Integration

### 🔒 Sicherheit
- Benutzerauthentifizierung via Nextcloud
- Session-Management
- Basic Input Validation

### 📝 Documentation
- README.md mit Basis-Features
- Installation Guide
- API Reference (Swagger-Style)

### ⚠️ Known Issues
- Keine Rollen-basierte Zugriffskontrolle (implementiert in v0.2.0)
- Validierung noch nicht vollständig
- Kein Admin-Panel (kommt in v0.2.0)
- Keine CSV/PDF Exports (kommt in v0.2.0)

---

## Geplante Releases

### v0.2.1 (Q1 2026)
- [ ] PDF-Export vollständig integrieren
- [ ] Admin-Panel UI erweitern
- [ ] Performance-Optimierungen
- [ ] Zusätzliche Unit Tests
- [ ] Bug-Fixes basierend auf Community-Feedback

### v0.3.0 (Q2 2026)
- [ ] Mehrsprachigkeit (Deutsch, Englisch, Französisch)
- [ ] Automatische Gebühren-Generierung
- [ ] Email-Benachrichtigungen
- [ ] SEPA Direct Debit (pain.008)
- [ ] Erweiterte Reporting & Analytics

### v1.0.0 (Q4 2026)
- [ ] Production-Ready (100% Stabilitäts-Tests)
- [ ] 100% Test-Coverage
- [ ] Vollständige API-Dokumentation
- [ ] Certified für Nextcloud Appstore
- [ ] Multi-Language Support
- [ ] Mobile App (optionale Integration)

---

## Versionierung

Diese App folgt [Semantic Versioning](https://semver.org/lang/de/):

- **MAJOR** (z.B. 1.0.0): Inkompatible API-Änderungen
- **MINOR** (z.B. 0.2.0): Neue Features mit Rückwärts-Kompatibilität
- **PATCH** (z.B. 0.2.1): Bug-Fixes
- **PRE-RELEASE** (z.B. 0.2.0-beta): Vorläufige Versionen

---

## Mitwirkende

Das Projekt wird entwickelt und gepflegt von der Community. Vielen Dank an alle, die Feedback, Bug-Reports und Pull Requests beitragen!

- **Lead Developer**: Stefan (Wacken2012)
- **Contributors**: (Hier könnten zukünftige Mitwirkende aufgeführt werden)

---

## Feedback & Support

- **Issues**: https://github.com/Wacken2012/nextcloud-verein/issues
- **Diskussionen**: https://github.com/Wacken2012/nextcloud-verein/discussions
- **Email**: Kontakt über Projekt-Website
