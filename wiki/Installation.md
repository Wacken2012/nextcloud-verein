# Installation & Setup Guide

Detaillierte Anleitung zur Installation der Nextcloud Vereins-App.

---

## 📋 Inhaltsverzeichnis

1. [Voraussetzungen](#voraussetzungen)
2. [Production-Installation](#production-installation)
3. [Development-Setup](#development-setup)
4. [Konfiguration](#konfiguration)
5. [Troubleshooting](#troubleshooting)

---

## ✅ Voraussetzungen

### System-Anforderungen

```
Nextcloud:         24.0 oder neuer
PHP:               8.0 oder neuer
Node.js:           16.0+ (nur für Development)
npm:               7.0+ (nur für Development)
Git:               2.25+
```

### Notwendige Rechte

```
✅ Nextcloud Admin-Zugang
✅ SSH/Terminal-Zugang zum Server
✅ Schreibrechte in /var/www/nextcloud/apps/
```

### Speicherplatz

```
App-Größe:         ca. 50 MB
Node-Modules:      ca. 500 MB (nur bei Development)
Datenbank:         ca. 10 MB pro 1000 Mitglieder
```

---

## 🚀 Production-Installation

### Schritt 1: Repository klonen

```bash
# Wechsel ins Apps-Verzeichnis
cd /var/www/nextcloud/apps/

# Repository klonen
git clone https://github.com/Wacken2012/nextcloud-verein.git verein

# Ins App-Verzeichnis wechseln
cd verein
```

### Schritt 2: Dependencies installieren

```bash
npm install
```

**Output sollte so aussehen:**
```
added 500 packages in 2m
```

### Schritt 3: Production Build erstellen

```bash
npm run build
```

**Output sollte so aussehen:**
```
✓ 106 modules transformed.
js/dist/style.css              24.72 kB │ gzip:   4.33 kB
js/dist/nextcloud-verein.mjs  822.75 kB │ gzip: 191.29 kB
✓ built in 1.34s
```

### Schritt 4: App aktivieren

```bash
# Mit www-data User
sudo -u www-data php /var/www/nextcloud/occ app:enable verein

# Output:
# verein enabled
```

### Schritt 5: Nextcloud Cache leeren

```bash
# Cache leeren
sudo -u www-data php /var/www/nextcloud/occ maintenance:mode --off

# Optional: Caches resetten
sudo -u www-data php /var/www/nextcloud/occ cache:clear-all
```

### Schritt 6: Berechtigungen setzen

```bash
# Richtige Berechtigungen für die App
sudo chown -R www-data:www-data /var/www/nextcloud/apps/verein/

# Dateirechte
sudo chmod -R 755 /var/www/nextcloud/apps/verein/
sudo chmod -R 750 /var/www/nextcloud/apps/verein/appinfo/
```

### ✅ Verifikation

```bash
# App sollte jetzt installiert sein
curl -I -u admin:PASSWORD http://localhost/nextcloud/index.php/apps/verein/

# HTTP 200 sollte zurückgegeben werden
```

---

## 🛠️ Development-Setup

### Schritt 1: Repository klonen

```bash
# Ins Development-Verzeichnis gehen
cd ~/projects/

# Repository klonen
git clone https://github.com/Wacken2012/nextcloud-verein.git
cd nextcloud-verein
```

### Schritt 2: Dependencies installieren

```bash
npm install
```

### Schritt 3: Symlink erstellen (optional)

```bash
# Symlink zur Nextcloud-Installation erstellen
ln -s ~/projects/nextcloud-verein /var/www/nextcloud/apps/verein-dev

# App aktivieren
sudo -u www-data php /var/www/nextcloud/occ app:enable verein-dev
```

### Schritt 4: Development Server starten

#### Option A: Mit Watch-Modus (Vite)

```bash
npm run dev

# Output:
# > nextcloud-verein@0.1.0 dev
# > vite build --watch
#
# ✓ 106 modules transformed
# VITE v4.5.14 watching for file changes...
```

Jetzt werden alle Änderungen automatisch rebuildert! 🔄

#### Option B: Einmalig bauen

```bash
npm run build

# Output: wie unter Schritt 3 (Production)
```

### Schritt 5: Nextcloud im Browser öffnen

```
http://localhost/nextcloud/index.php/apps/verein-dev/
```

---

## ⚙️ Konfiguration

### Nextcloud App-Info

Die App wird durch diese Dateien konfiguriert:

#### `appinfo/info.xml`

```xml
<?xml version="1.0"?>
<info>
    <id>verein</id>
    <name>Vereins-App</name>
    <summary>Verwaltung für Vereine</summary>
    <description>Mitglieder, Gebühren, Statistiken</description>
    <version>0.1.0</version>
    <licence>AGPL</licence>
    <author>Stefan</author>
    <namespace>OCA\Verein</namespace>
</info>
```

#### `appinfo/routes.php`

```php
return [
    'routes' => [
        ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
        // API Routes
        ['name' => 'api#getMembers', 'url' => '/api/v1/members', 'verb' => 'GET'],
        ['name' => 'api#createMember', 'url' => '/api/v1/members', 'verb' => 'POST'],
        // ...
    ]
];
```

### Umgebungsvariablen

Erstelle eine `.env` Datei für Development:

```bash
# .env.development
VITE_API_BASE=http://localhost/nextcloud/index.php/apps/verein/api/v1
VITE_DEBUG=true
```

---

## 🐛 Troubleshooting

### Problem: "App not found" beim Öffnen

**Ursache:** App nicht aktiviert oder Build fehlgeschlagen

**Lösung:**
```bash
# App-Status prüfen
sudo -u www-data php /var/www/nextcloud/occ app:list | grep verein

# App neu aktivieren
sudo -u www-data php /var/www/nextcloud/occ app:disable verein
sudo -u www-data php /var/www/nextcloud/occ app:enable verein

# Cache leeren
sudo -u www-data php /var/www/nextcloud/occ cache:clear-all
```

### Problem: "Permission denied" bei npm install

**Ursache:** Node-Module Berechtigungen falsch

**Lösung:**
```bash
# Berechtigungen korrigieren
chmod -R 755 node_modules/

# Oder: Komplett neu installieren
rm -rf node_modules package-lock.json
npm install
```

### Problem: Build schlägt fehl

**Ursache:** Abhängigkeiten nicht installiert oder Node-Version zu alt

**Lösung:**
```bash
# Node-Version prüfen
node --version    # sollte ≥16.0.0 sein
npm --version     # sollte ≥7.0.0 sein

# Dependencies neu installieren
rm -rf node_modules
npm install

# Nochmal bauen
npm run build
```

### Problem: Charts werden nicht angezeigt

**Ursache:** Chart.js nicht geladen

**Lösung:**
```bash
# Chart.js installieren
npm install chart.js

# Build erneut laufen lassen
npm run build
```

### Problem: Dark-Mode funktioniert nicht

**Ursache:** CSS-Variablen nicht definiert

**Lösung:**
```bash
# theme.scss prüfen
cat js/theme.scss | grep "color-"

# Theme neuladen
# Browser-Cache leeren (Ctrl+Shift+Delete)
# Nextcloud neu laden (F5)
```

---

## 📝 Nächste Schritte nach Installation

1. **Erste Schritte:**
   - Admin-Login in Nextcloud
   - Zum App "Vereins-App" navigieren
   - Erste Mitglieder hinzufügen

2. **Konfiguration:**
   - Gebührensätze definieren
   - Rollen anpassen
   - Berechtigungen setzen (ab v0.2.0)

3. **Testing:**
   - App auf verschiedenen Geräten testen
   - Responsive Layout prüfen
   - Dark-Mode testen

4. **Backup:**
   - Nextcloud regelmäßig sichern
   - Datenbank-Backups erstellen

---

## 🔗 Weitere Ressourcen

- [Offizielle Nextcloud Docs](https://docs.nextcloud.com/)
- [PHP API Reference](https://docs.nextcloud.com/server/latest/developer_manual/)
- [Vue.js Dokumentation](https://vuejs.org/)

---

**Bei Problemen:** Siehe [Troubleshooting.md](Troubleshooting.md) oder erstelle ein [GitHub Issue](https://github.com/Wacken2012/nextcloud-verein/issues)
