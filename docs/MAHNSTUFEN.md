# Mahnstufen — Design & Implementierungsplan

Dieses Dokument beschreibt die geplante Umsetzung der Mahnstufen (1., 2., Mahnung) für die Nextcloud Vereins-App. Ziel ist eine konfigurierbare, getestete und DSGVO-kompatible Mahnlogik, die sich in den vorhandenen `ReminderJob` und das Settings-UI integriert.

## Ziele
- Konfigurierbare Mahnstufen (Anzahl, Intervalle, Aktionen)
- Reproduzierbare, testbare Eskalations-Logik
- Nachvollziehbare Logs und Audit-Einträge
- Admin-UI zur Konfiguration (Stufen, Intervalle, Templates)

## Anforderungen (high level)
- Administratoren können beliebig viele Mahnstufen definieren (Default: 2)
- Für jede Stufe konfigurierbar: Verzögerung (Tage), Mahngebühr (optional), Vorlage für E-Mail
- Aktionen pro Stufe: Senden einer E-Mail, Änderung des Mitglieds-/Kontostatus, optionale Sperrung
- Alle Aktionen müssen idempotent sein (bei wiederholtem Lauf keine Duplikate)
- Historie: jede ausgelöste Mahnung wird geloggt (Mitglied, Betrag, Stufe, Zeit, Aktion)

## Datenmodell (Vorschlag)
- Tabelle: `oc_verein_reminder_levels`
  - `id` INT PK
  - `app_id` VARCHAR (Nextcloud app instance optional)
  - `level` INT (1,2,3...)
  - `delay_days` INT
  - `fee_cents` INT NULL
  - `template_id` VARCHAR NULL
  - `active` BOOLEAN
  - `created_at` DATETIME
  - `updated_at` DATETIME

- Tabelle: `oc_verein_reminder_logs`
  - `id` INT PK
  - `member_id` INT
  - `fee_id` INT NULL
  - `reminder_level_id` INT
  - `sent_at` DATETIME
  - `action` VARCHAR
  - `payload` JSON (snapshotted data)

## Integrationspunkte
- `ReminderJob`: erweitert um Evaluation aller offenen Beiträge gegen definierte Mahnstufen
- `ReminderService`: neue Methoden `applyReminderLevels()` und `recordReminderLog()`
- Settings-UI (Admin): neue Sektion `Mahnwesen` mit Auflistung / Editieren der Stufen
- E-Mail-Templates: neue Template-Keys `reminder.level.%d` (z. B. `reminder.level.1`)

## Verhalten / Ablauf
1. `ReminderJob` läuft periodisch (z. B. täglich)
2. Für jeden offenen Beitrag/Mahnbare Position wird das Alter berechnet
3. Für passende Stufe wird `ReminderService` angewiesen, Aktion auszuführen
   - Senden der E-Mail via vorhandenen Mail-Service mit Template und Fallback
   - Optionale Gebührenbuchung (als Gebühr-Transaktion) — optional, konfigurierbar
   - Statusaktualisierung am Mitglied (z. B. `blocked_until` oder `in_collection`) — optional
4. Log-Eintrag wird in `oc_verein_reminder_logs` geschrieben
5. Idempotenz: durch Prüfung des Logs wird verhindert, dass gleiche Stufe doppelt ausgelöst wird

## UI/Settings
- Admin-Route: `settings/mahnwesen`
- CRUD für Mahnstufen: Reihenfolge (level), Tage, Gebühr, Template, Aktiv/Deaktiv
- Preview: Beispiel-E-Mail generieren (mit Test-Daten)

## Migration & Backwards Compatibility
- Neue Migration erstellt (`MigrationAddReminderLevels`) mit Default-Stufen (z. B. 14 Tage, 28 Tage)
- Fallback: wenn keine Stufen konfiguriert sind, verhält sich ReminderJob wie zuvor (nur einfache Reminder)

## Tests
- Unit-Tests für `ReminderService::determineLevelForAge()`
- Integration-Test für `ReminderJob` Lauf (simulate jobs, assert logs and emails sent)
- Edge-Cases: multiple reminders for same member, overlapping intervals, timezone handling

## Security & Datenschutz
- Keine sensiblen Daten in `payload` speichern (nur Referenzen und anonymisierte Snapshots)
- Logs nur für berechtigte Admins sichtbar
- Löschfristen für Logs konfigurieren (DSGVO)

## Nächste Schritte (Implementierung)
1. Branch erstellen: `feature/mahnstufen` (von `develop`)
2. Migration + Entities anlegen
3. `ReminderService` erweitern (core logic)
4. `ReminderJob` anpassen und lokal testen
5. Settings-UI (Vue) mit CRUD für Stufen
6. E-Mail-Template-Keys anlegen & Übersetzungen
7. Tests schreiben & CI ausführen

---

## Short English Summary
This document specifies the design for configurable reminder levels (1st/2nd reminder). It covers data model, integration points (`ReminderJob` + `ReminderService`), admin UI, migration plan and tests. Next step: create `feature/mahnstufen` branch and add migrations + service logic.
