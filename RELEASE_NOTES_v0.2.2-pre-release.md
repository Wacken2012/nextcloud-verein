# Release Notes v0.2.2-pre-release

**Datum**: 4. Dezember 2025  
**Branch**: develop  
**Kompatibilität**: Nextcloud 27-32, PHP 7.4-8.4

---

## 🇩🇪 Deutsch

### Übersicht

Diese Pre-Release-Version behebt kritische API-Fehler (HTTP 500), die in v0.2.0-beta mit Nextcloud 32 aufgetreten sind. Alle Mahnung- und DSGVO-Endpunkte sind jetzt funktional.

### 🐛 Behobene Fehler

#### API 500 Error Fixes
| Problem | Ursache | Lösung |
|---------|---------|--------|
| Reminder Config 500 | `ILogger` veraltet in NC32 | `LoggerInterface` verwenden |
| Request Body leer | `getBody()` existiert nicht | `file_get_contents('php://input')` |
| Privacy Service Crash | `SettingService` nicht vorhanden | `IConfig` verwenden |
| ReminderService Fehler | Syntax `this->` statt `$this->` | PHP-Syntax korrigiert |
| Export 403 Error | User-ID vs Member-ID | Union Type `string\|int` |
| ReminderLog leer | Array vs Object Response | Beide Formate unterstützen |

#### Jetzt funktionierende Endpunkte
- ✅ `POST /api/v1/reminders/config` - Mahnung aktivieren/deaktivieren
- ✅ `GET /api/v1/reminders/log` - Mahnung-Protokoll anzeigen
- ✅ `GET /api/v1/privacy/export/{id}` - DSGVO Datenexport
- ✅ `GET /api/v1/privacy/consent/{id}` - Einwilligungsstatus
- ✅ `GET /api/v1/privacy/policy` - Datenschutzerklärung

### 📦 Installation

1. ZIP-Datei herunterladen
2. Entpacken nach `/path/to/nextcloud/apps/verein/`
3. App in Nextcloud aktivieren
4. Browser-Cache leeren (Strg+F5)

### ⚠️ Hinweise

- Dies ist eine **Pre-Release** Version für Testzwecke
- Backup vor Installation empfohlen
- Feedback willkommen unter Issues

---

## 🇬🇧 English

### Overview

This pre-release version fixes critical API errors (HTTP 500) that occurred in v0.2.0-beta with Nextcloud 32. All reminder and GDPR endpoints are now functional.

### 🐛 Bug Fixes

#### API 500 Error Fixes
| Issue | Cause | Solution |
|-------|-------|----------|
| Reminder Config 500 | `ILogger` deprecated in NC32 | Use `LoggerInterface` |
| Request Body empty | `getBody()` doesn't exist | Use `file_get_contents('php://input')` |
| Privacy Service Crash | `SettingService` not found | Use `IConfig` |
| ReminderService Error | Syntax `this->` instead of `$this->` | Fixed PHP syntax |
| Export 403 Error | User-ID vs Member-ID | Union type `string\|int` |
| ReminderLog empty | Array vs Object Response | Support both formats |

#### Now Working Endpoints
- ✅ `POST /api/v1/reminders/config` - Enable/disable reminders
- ✅ `GET /api/v1/reminders/log` - View reminder log
- ✅ `GET /api/v1/privacy/export/{id}` - GDPR data export
- ✅ `GET /api/v1/privacy/consent/{id}` - Consent status
- ✅ `GET /api/v1/privacy/policy` - Privacy policy

### 📦 Installation

1. Download ZIP file
2. Extract to `/path/to/nextcloud/apps/verein/`
3. Enable app in Nextcloud
4. Clear browser cache (Ctrl+F5)

### ⚠️ Notes

- This is a **Pre-Release** version for testing purposes
- Backup recommended before installation
- Feedback welcome via Issues

---

## 📋 Changed Files

```
lib/AppInfo/Application.php        - PrivacyService DI registration
lib/Controller/PrivacyApiController.php - Export endpoint fixes
lib/Controller/ReminderApiController.php - Request body parsing
lib/Service/PrivacyService.php     - IConfig instead of SettingService
lib/Service/ReminderService.php    - PHP syntax fix
js/components/ReminderLog.vue      - Response format handling
```

## 🔗 Links

- [Changelog](CHANGELOG.md)
- [Installation Guide](INSTALLATION.md)
- [Issues](https://github.com/Wacken2012/nextcloud-verein/issues)
