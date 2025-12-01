# Frequently Asked Questions (FAQ)

> 🇩🇪 [Deutsch](#deutsch) | 🇬🇧 [English](#english)

---

# 🇩🇪 Deutsch

## Häufig gestellte Fragen

---

## 🎯 Allgemein

### Was ist die Nextcloud Vereins-App?

Die **Nextcloud Vereins-App** ist eine spezialisierte Anwendung zur Verwaltung von Vereinen, Clubs und Organisationen direkt in Nextcloud. Sie bietet Funktionen für Mitgliederverwaltung, Gebührenverwaltung und Statistiken.

### Kostet die App etwas?

**Nein!** Die App ist vollständig kostenlos und Open Source unter der AGPL-3.0 Lizenz.

### Welche Lizenz hat die App?

```
AGPL-3.0 License
→ Quellcode muss offen sein
→ Änderungen müssen weitergegeben werden
→ Kommerzielle Nutzung erlaubt
```

---

## 📦 Installation & Setup

### Welche Voraussetzungen gibt es?

```
Nextcloud:    28.0+
PHP:          8.0+
SQLite/MySQL/PostgreSQL
Git:          2.25+
Node.js:      16.0+ (nur für Development)
npm:          7.0+
```

### Wie installiere ich die App?

Siehe: [Installation.md](./Installation.md)

Kurz-Version:
```bash
cd /var/www/nextcloud/apps/
git clone https://github.com/Wacken2012/nextcloud-verein.git verein
cd verein
npm install && npm run build
sudo -u www-data php occ app:enable verein
```

### Kann ich die App selbst hosten?

**Ja!** Die App ist für selbst-gehostete Nextcloud-Installationen ausgelegt.

### Kann ich die App in der Cloud hosten?

**Ja!** Solange Nextcloud läuft, funktioniert die App.

---

## ✨ Features & Funktionalität

### Welche Features gibt es aktuell (v0.2.1)?

```
✅ Mitgliederverwaltung (CRUD)
✅ Gebührenverwaltung (CRUD)
✅ Dashboard mit Statistiken
✅ Rollenbasierte Zugriffskontrolle (RBAC)
✅ CSV/PDF Export
✅ SEPA-XML Export
✅ Responsive Layout (Desktop/Tablet/Mobile)
✅ Dark-Mode Support
✅ Nextcloud Theme-Integration
✅ API-Dokumentation (OpenAPI 3.0)
```

### Wann kommt Feature XY?

Siehe: [ROADMAP.md](https://github.com/Wacken2012/nextcloud-verein/blob/main/ROADMAP.md)

Geplante Features:
- **v0.3.0 (Q2 2026):** Kalender, Talk, Files, Deck Integration
- **v0.4.0 (Q3 2026):** Zeugwart/Materialverwaltung
- **v1.0.0 (Q4 2026):** Production-Ready, App Store Release

### Kann ich die App anpassen?

**Ja!** Der Code ist Open Source und kann angepasst werden.

---

## 🔒 Sicherheit & Datenschutz

### Wie sicher ist die App?

```
✅ Alle Daten bleiben in deiner Nextcloud
✅ HTTPS-Verschlüsselung (wenn Nextcloud HTTPS nutzt)
✅ Rollenbasierte Zugriffskontrolle (ab v0.2.0)
✅ Datenvalidierung (IBAN/BIC, Email, SEPA XML)
✅ Audit-Logging für Permission-Violations
✅ Input-Sanitization auf allen Endpoints
```

### Kann jeder alle Daten sehen?

**Nein!** Die App hat ein Rollensystem:
- **Admin** — Vollständiger Zugriff
- **Kassenwart** — Finanz-Daten
- **Mitglied** — Begrenzte Sicht (nur eigene Daten)

### Wo werden die Daten gespeichert?

Die Daten werden in der **Nextcloud-Datenbank** gespeichert:
- SQLite (default)
- MySQL / MariaDB
- PostgreSQL

Konfiguriert in `config/config.php`.

### Kann ich Daten exportieren?

**Ja!** Aktuelle Export-Optionen:
- CSV (Mitglieder, Gebühren)
- PDF (Mitglieder, Gebühren)
- SEPA-XML (Gebühren)

---

## 🐛 Probleme & Fehler

### Die App lädt nicht

**Ursache:** App nicht aktiviert oder Build-Fehler

**Lösung:**
```bash
# App-Status prüfen
sudo -u www-data php /var/www/nextcloud/occ app:list | grep verein

# App neu aktivieren
sudo -u www-data php /var/www/nextcloud/occ app:enable verein

# Cache leeren
sudo -u www-data php /var/www/nextcloud/occ cache:clear-all
```

### Fehler beim Laden der Daten

**Ursache:** API nicht erreichbar oder Berechtigungen

**Lösung:**
```bash
# Nextcloud Logs prüfen
sudo tail -f /var/www/nextcloud/data/nextcloud.log

# Cache leeren
sudo -u www-data php /var/www/nextcloud/occ cache:clear-all
```

### Dark-Mode funktioniert nicht

**Ursache:** CSS-Variablen nicht geladen

**Lösung:**
```bash
# Browser-Cache leeren (Strg+Shift+Delete)
# Nextcloud neuladen (F5)
```

---

## 💬 Community & Support

### Wie kann ich Bugs berichten?

```
GitHub Issues: https://github.com/Wacken2012/nextcloud-verein/issues

Bitte berichtet:
- App-Version (Admin → Apps)
- Nextcloud-Version
- Browser & Betriebssystem
- Reproduktionsschritte
- Screenshots (wenn möglich)
```

### Wie kann ich Features vorschlagen?

```
GitHub Discussions: https://github.com/Wacken2012/nextcloud-verein/discussions

Oder: GitHub Issues mit Label "enhancement"
```

### Wie kann ich beitragen?

```
1. Repository forken
2. Feature-Branch erstellen
3. Commits mit aussagekräftigen Messages
4. Push und Pull Request erstellen
5. Code-Review abwarten

Richtlinien: siehe CONTRIBUTING.md
```

### Wo kann ich mit anderen Nutzern reden?

```
GitHub Discussions (Q&A):
https://github.com/Wacken2012/nextcloud-verein/discussions

Nextcloud Forum:
https://help.nextcloud.com/
```

---

## 🔄 Updates & Versionen

### Wie aktualisiere ich die App?

```bash
cd /var/www/nextcloud/apps/verein

# Neue Version pullen
git pull origin main

# Dependencies aktualisieren
npm install

# Build erstellen
npm run build

# Nextcloud Cache leeren
sudo -u www-data php /var/www/nextcloud/occ cache:clear-all
```

### Gibt es Breaking Changes?

**Nein!** Alle Versionen sind abwärtskompatibel.

---

# 🇬🇧 English

## Frequently Asked Questions

---

## 🎯 General

### What is the Nextcloud Association App?

The **Nextcloud Association App** is a specialized application for managing clubs, associations, and organizations directly in Nextcloud. It provides functionality for member management, fee management, and statistics.

### Is the app free?

**Yes!** The app is completely free and open source under the AGPL-3.0 license.

### What license does the app have?

```
AGPL-3.0 License
→ Source code must be open
→ Changes must be shared
→ Commercial use allowed
```

---

## 📦 Installation & Setup

### What are the requirements?

```
Nextcloud:    28.0+
PHP:          8.0+
SQLite/MySQL/PostgreSQL
Git:          2.25+
Node.js:      16.0+ (development only)
npm:          7.0+
```

### How do I install the app?

See: [Installation.md](./Installation.md)

Quick version:
```bash
cd /var/www/nextcloud/apps/
git clone https://github.com/Wacken2012/nextcloud-verein.git verein
cd verein
npm install && npm run build
sudo -u www-data php occ app:enable verein
```

### Can I self-host the app?

**Yes!** The app is designed for self-hosted Nextcloud installations.

### Can I host the app in the cloud?

**Yes!** As long as Nextcloud runs, the app works.

---

## ✨ Features & Functionality

### What features are available in v0.2.1?

```
✅ Member management (CRUD)
✅ Fee management (CRUD)
✅ Dashboard with statistics
✅ Role-based access control (RBAC)
✅ CSV/PDF export
✅ SEPA XML export
✅ Responsive layout (Desktop/Tablet/Mobile)
✅ Dark mode support
✅ Nextcloud theme integration
✅ API documentation (OpenAPI 3.0)
```

### When will feature XY be available?

See: [ROADMAP.md](https://github.com/Wacken2012/nextcloud-verein/blob/main/ROADMAP.md)

Planned features:
- **v0.3.0 (Q2 2026):** Calendar, Talk, Files, Deck integration
- **v0.4.0 (Q3 2026):** Zeugwart/Material management
- **v1.0.0 (Q4 2026):** Production-ready, App Store release

### Can I customize the app?

**Yes!** The code is open source and can be modified.

---

## 🔒 Security & Privacy

### How secure is the app?

```
✅ All data stays in your Nextcloud
✅ HTTPS encryption (if Nextcloud uses HTTPS)
✅ Role-based access control (from v0.2.0)
✅ Data validation (IBAN/BIC, Email, SEPA XML)
✅ Audit logging for permission violations
✅ Input sanitization on all endpoints
```

### Can everyone see all data?

**No!** The app has a role system:
- **Admin** — Full access
- **Treasurer** — Financial data
- **Member** — Limited view (own data only)

### Where is data stored?

Data is stored in the **Nextcloud database**:
- SQLite (default)
- MySQL / MariaDB
- PostgreSQL

Configured in `config/config.php`.

### Can I export data?

**Yes!** Current export options:
- CSV (members, fees)
- PDF (members, fees)
- SEPA XML (fees)

---

## 🐛 Issues & Errors

### App won't load

**Cause:** App not enabled or build failed

**Solution:**
```bash
# Check app status
sudo -u www-data php /var/www/nextcloud/occ app:list | grep verein

# Re-enable app
sudo -u www-data php /var/www/nextcloud/occ app:enable verein

# Clear cache
sudo -u www-data php /var/www/nextcloud/occ cache:clear-all
```

### Data loading errors

**Cause:** API unreachable or permissions issues

**Solution:**
```bash
# Check Nextcloud logs
sudo tail -f /var/www/nextcloud/data/nextcloud.log

# Clear cache
sudo -u www-data php /var/www/nextcloud/occ cache:clear-all
```

### Dark mode not working

**Cause:** CSS variables not loaded

**Solution:**
```bash
# Clear browser cache (Ctrl+Shift+Delete)
# Reload Nextcloud (F5)
```

---

## 💬 Community & Support

### How do I report bugs?

```
GitHub Issues: https://github.com/Wacken2012/nextcloud-verein/issues

Please include:
- App version (Admin → Apps)
- Nextcloud version
- Browser & operating system
- Steps to reproduce
- Screenshots (if possible)
```

### How do I request features?

```
GitHub Discussions: https://github.com/Wacken2012/nextcloud-verein/discussions

Or: GitHub Issues with label "enhancement"
```

### How can I contribute?

```
1. Fork repository
2. Create feature branch
3. Commits with descriptive messages
4. Push and create pull request
5. Wait for code review

Guidelines: see CONTRIBUTING.md
```

### Where can I talk with other users?

```
GitHub Discussions (Q&A):
https://github.com/Wacken2012/nextcloud-verein/discussions

Nextcloud Forum:
https://help.nextcloud.com/
```

---

## 🔄 Updates & Versions

### How do I update the app?

```bash
cd /var/www/nextcloud/apps/verein

# Pull new version
git pull origin main

# Update dependencies
npm install

# Create build
npm run build

# Clear Nextcloud cache
sudo -u www-data php /var/www/nextcloud/occ cache:clear-all
```

### Are there breaking changes?

**No!** All versions are backward compatible.

---

**Last Updated:** December 2025  
**App Version:** v0.2.1
