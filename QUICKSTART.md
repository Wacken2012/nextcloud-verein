# 🚀 Quickstart Guide

Diese Anleitung hilft dir, die Nextcloud Verein-App in Betrieb zu nehmen.

## Voraussetzungen

- Nextcloud 27 oder höher installiert
- PHP 8.0 oder höher
- Node.js 16 oder höher
- Composer installiert

## Installation (Development)

### 1. Dependencies installieren

```bash
# PHP-Dependencies
composer install

# JavaScript-Dependencies
npm install
```

### 2. Frontend bauen

```bash
# Einmaliger Build
npm run build

# Oder: Watch-Modus für Entwicklung
npm run watch
```

### 3. App in Nextcloud integrieren

```bash
# App-Verzeichnis in Nextcloud kopieren
cp -r /pfad/zu/Nextcloud-Verein /pfad/zu/nextcloud/apps/verein

# In Nextcloud-Verzeichnis wechseln
cd /pfad/zu/nextcloud

# App aktivieren
php occ app:enable verein
```

### 4. Datenbank-Tabellen erstellen

Die Tabellen werden automatisch beim ersten Aktivieren der App erstellt.
Prüfen kannst du das mit:

```bash
php occ app:list | grep verein
```

## Tests ausführen

```bash
# Alle PHPUnit-Tests
composer test

# Einzelne Testklasse
./vendor/bin/phpunit tests/Unit/MemberServiceTest.php

# Mit Coverage-Report
./vendor/bin/phpunit --coverage-html coverage/
```

## Entwicklung

### Frontend-Entwicklung

```bash
# Development-Server mit Hot-Reload
npm run dev

# Build für Produktion
npm run build

# Watch-Modus
npm run watch
```

### Backend-Entwicklung

Die PHP-Klassen befinden sich in:
- `lib/Controller/` - API-Endpunkte
- `lib/Service/` - Business-Logik  
- `lib/Db/` - Datenbank-Modelle

### Neue API-Route hinzufügen

1. Route in `appinfo/routes.php` definieren
2. Controller-Methode erstellen
3. Service-Logik implementieren
4. Test schreiben

## Nutzung

### 1. Mitglieder verwalten

- Navigiere zu "Verein" in Nextcloud
- Klicke "Neues Mitglied"
- Fülle das Formular aus (IBAN wird automatisch validiert)
- Nutze Suche und Filter für große Mitgliederlisten

### 2. Beiträge erfassen

- Wechsle zum Tab "Beiträge"
- Klicke "Neuer Beitrag"
- Wähle Mitglied, Betrag, Fälligkeitsdatum
- Markiere bezahlte Beiträge mit ✓-Button

### 3. SEPA-Export erstellen

- Wechsle zum Tab "SEPA-Export"
- Fülle Gläubiger-Daten aus
- Klicke "Vorschau" für Übersicht
- Klicke "SEPA-XML herunterladen"
- Importiere XML in deine Banking-Software

## Troubleshooting

### App erscheint nicht in Nextcloud

```bash
# Prüfe App-Status
php occ app:list

# Deaktiviere und aktiviere erneut
php occ app:disable verein
php occ app:enable verein

# Prüfe Logs
tail -f /pfad/zu/nextcloud/data/nextcloud.log
```

### Frontend wird nicht geladen

```bash
# Cache leeren
rm -rf js/dist/*
npm run build

# Nextcloud-Cache leeren
php occ maintenance:mode --on
php occ maintenance:mode --off
```

### Datenbank-Fehler

```bash
# Prüfe ob Tabellen existieren
php occ db:check

# Repariere Datenbank
php occ maintenance:repair
```

## Nächste Schritte

- [ ] Testdaten anlegen (5-10 Mitglieder)
- [ ] Beiträge für Mitglieder erfassen
- [ ] SEPA-Export testen
- [ ] Feedback sammeln
- [ ] Produktiv-Deployment planen

## Support

Bei Fragen oder Problemen:
- GitHub Issues: [Link zum Repository]
- E-Mail: your-email@example.com
