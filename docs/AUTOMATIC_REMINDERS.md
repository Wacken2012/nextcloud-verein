# Automatische Mahnungen - Implementierung

## Übersicht

Die automatische Mahnungsfunktion wurde als Teil von v0.3.0 implementiert. Diese Funktion ermöglicht es Vereinen, Zahlungserinnerungen automatisch an Mitglieder zu versenden.

## Implementierte Komponenten

### 1. BackgroundJob (`lib/BackgroundJob/ReminderJob.php`)

Ein Nextcloud TimedJob, der täglich (alle 24 Stunden) ausgeführt wird:

```php
// Wird automatisch vom Nextcloud-Cron aufgerufen
protected function run($argument): void {
    $this->reminderService->processDueReminders();
}
```

**Features:**
- ✅ Tägliche Ausführung (86400 Sekunden)
- ✅ Fehler-Logging bei Exceptions
- ✅ Start/Ende-Protokollierung

### 2. ReminderService Erweiterungen (`lib/Service/ReminderService.php`)

**Neue Methoden:**
- `processDueReminders()` - Hauptlogik für Cronjob
- `getIntervalForLevel()` - Intervall für Mahnstufe ermitteln
- `getMaxReminderLevel()` / `setMaxReminderLevel()` - Maximale Mahnstufe konfigurieren
- `getClubName()` / `setClubName()` - Vereinsname konfigurieren
- `getClubEmail()` / `setClubEmail()` - Vereins-E-Mail konfigurieren
- `getBankDetails()` / `setBankDetails()` - Bankverbindung konfigurieren

**Mahnstufen-Logik:**
1. **Stufe 1**: Freundliche Zahlungserinnerung
2. **Stufe 2**: Zweite Zahlungserinnerung
3. **Stufe 3**: Letzte Zahlungsaufforderung (Status: "escalated")

### 3. E-Mail Templates (`templates/email/`)

- `reminder.php` - HTML E-Mail Template
- `reminder_plain.php` - Klartext E-Mail Template

**Features:**
- Mahnstufen-spezifische Betreffzeilen
- Personalisierte Anrede
- Zahlungsdetails
- Bankverbindung
- Abschlusstext je nach Mahnstufe
- Responsive Design

### 4. Registration (`lib/AppInfo/Application.php`)

```php
// Registrierung des BackgroundJobs
$context->registerBackgroundJob(ReminderJob::class);
```

## Konfigurationsoptionen

| Option | Standardwert | Beschreibung |
|--------|-------------|--------------|
| `reminder_enabled` | `false` | Mahnungen aktiviert/deaktiviert |
| `reminder_interval_level_1` | `7` | Tage für Stufe 1 |
| `reminder_interval_level_2` | `14` | Tage für Stufe 2 |
| `reminder_interval_level_3` | `21` | Tage für Stufe 3 |
| `reminder_days_between_reminders` | `3` | Tage zwischen Wiederholungen |
| `reminder_max_level` | `3` | Maximale Mahnstufe |
| `reminder_club_name` | `Verein` | Vereinsname in E-Mails |
| `reminder_club_email` | `` | Absender-E-Mail |
| `reminder_bank_details` | `` | Bankverbindung in E-Mails |

## API-Endpunkte

Bestehende Endpunkte wurden nicht verändert. Die Konfiguration erfolgt über:
- `PUT /api/reminders/toggle` - Mahnungen aktivieren/deaktivieren
- `POST /api/reminders/config` - Konfiguration aktualisieren

## Tests

Neue Tests in `tests/Unit/BackgroundJob/ReminderJobTest.php`:
- ✅ `testRunCallsProcessDueReminders` 
- ✅ `testRunLogsErrorOnException`
- ✅ `testJobIntervalIs24Hours`

## Deployment

Der BackgroundJob wird automatisch beim ersten Cron-Lauf nach dem Update registriert.

### Manuelle Aktivierung:
```bash
# App neu laden
php occ app:disable verein
php occ app:enable verein

# Cron manuell starten
php cron.php
```

### Job Status prüfen:
```bash
php occ background-job:list | grep -i verein
```

## Nächste Schritte

- [ ] Admin-UI für Mahnung-Konfiguration
- [ ] Mahnung-Vorschau im Frontend
- [ ] Manuelles Auslösen einzelner Mahnungen
- [ ] E-Mail-Test-Funktion
