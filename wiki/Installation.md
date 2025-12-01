# Installation & Setup Guide

> 🇩🇪 [Deutsch](#deutsch) | 🇬🇧 [English](#english)

---

# 🇩🇪 Deutsch

## Setup-Anleitung

Detaillierte Anleitung zur Installation der Nextcloud Vereins-App für Production und Development.

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
Nextcloud:         28.0 oder neuer
PHP:               8.0 oder neuer
SQLite/MySQL/PostgreSQL
Git:               2.25+
Node.js:           16.0+ (nur für Development)
npm:               7.0+ (nur für Development)
```

### Notwendige Rechte

```
✅ Nextcloud Admin-Zugang
✅ SSH/Terminal-Zugang zum Server
✅ Schreibrechte in /var/www/nextcloud/apps/
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

### Schritt 3: Production Build erstellen

```bash
npm run build
```

**Output sollte so aussehen:**
```
✓ 106 modules transformed.
js/dist/nextcloud-verein.mjs       508 kB │ gzip: 148 kB
js/dist/style.css                   24 kB │ gzip:   4 kB
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
sudo -u www-data php /var/www/nextcloud/occ cache:clear-all
```

### Schritt 6: Berechtigungen setzen

```bash
# Richtige Berechtigungen für die App
sudo chown -R www-data:www-data /var/www/nextcloud/apps/verein/
sudo chmod -R 755 /var/www/nextcloud/apps/verein/
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

```bash
# Mit Watch-Modus (Vite)
npm run dev

# Output:
# ✓ 106 modules transformed
# VITE v4.5.14 watching for file changes...
```

Jetzt werden alle Änderungen automatisch rebuildert! 🔄

### Schritt 5: Im Browser öffnen

```
http://localhost/nextcloud/index.php/apps/verein-dev/
```

---

## ⚙️ Konfiguration

### App-Info Datei

Die App wird durch `appinfo/info.xml` konfiguriert:

```xml
<?xml version="1.0"?>
<info>
    <id>verein</id>
    <name>Vereins-App</name>
    <summary>Verwaltung für Vereine, Clubs und Organisationen</summary>
    <version>0.2.1</version>
    <licence>AGPL</licence>
    <author>Stefan Schulz</author>
    <namespace>OCA\Verein</namespace>
    <documentation>https://github.com/Wacken2012/nextcloud-verein</documentation>
</info>
```

---

## 🐛 Troubleshooting

### Problem: "App not found"

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

**Lösung:**
```bash
# Berechtigungen korrigieren
chmod -R 755 node_modules/

# Oder: Komplett neu installieren
rm -rf node_modules package-lock.json
npm install
```

### Problem: Build schlägt fehl

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

---

# 🇬🇧 English

## Setup Guide

Detailed instructions for installing the Nextcloud Association App for production and development.

---

## 📋 Table of Contents

1. [Requirements](#requirements)
2. [Production Installation](#production-installation)
3. [Development Setup](#development-setup)
4. [Configuration](#configuration)
5. [Troubleshooting](#troubleshooting-1)

---

## ✅ Requirements

### System Requirements

```
Nextcloud:         28.0 or newer
PHP:               8.0 or newer
SQLite/MySQL/PostgreSQL
Git:               2.25+
Node.js:           16.0+ (development only)
npm:               7.0+ (development only)
```

### Required Access

```
✅ Nextcloud admin access
✅ SSH/terminal access to server
✅ Write permissions in /var/www/nextcloud/apps/
```

---

## 🚀 Production Installation

### Step 1: Clone Repository

```bash
# Change to apps directory
cd /var/www/nextcloud/apps/

# Clone repository
git clone https://github.com/Wacken2012/nextcloud-verein.git verein

# Change to app directory
cd verein
```

### Step 2: Install Dependencies

```bash
npm install
```

### Step 3: Create Production Build

```bash
npm run build
```

**Output should look like:**
```
✓ 106 modules transformed.
js/dist/nextcloud-verein.mjs       508 kB │ gzip: 148 kB
js/dist/style.css                   24 kB │ gzip:   4 kB
✓ built in 1.34s
```

### Step 4: Enable App

```bash
# With www-data user
sudo -u www-data php /var/www/nextcloud/occ app:enable verein

# Output:
# verein enabled
```

### Step 5: Clear Nextcloud Cache

```bash
sudo -u www-data php /var/www/nextcloud/occ cache:clear-all
```

### Step 6: Set Permissions

```bash
# Set correct permissions for app
sudo chown -R www-data:www-data /var/www/nextcloud/apps/verein/
sudo chmod -R 755 /var/www/nextcloud/apps/verein/
```

### ✅ Verification

```bash
# App should now be installed
curl -I -u admin:PASSWORD http://localhost/nextcloud/index.php/apps/verein/

# Should return HTTP 200
```

---

## 🛠️ Development Setup

### Step 1: Clone Repository

```bash
# Change to development directory
cd ~/projects/

# Clone repository
git clone https://github.com/Wacken2012/nextcloud-verein.git
cd nextcloud-verein
```

### Step 2: Install Dependencies

```bash
npm install
```

### Step 3: Create Symlink (optional)

```bash
# Create symlink to Nextcloud installation
ln -s ~/projects/nextcloud-verein /var/www/nextcloud/apps/verein-dev

# Enable app
sudo -u www-data php /var/www/nextcloud/occ app:enable verein-dev
```

### Step 4: Start Development Server

```bash
# With watch mode (Vite)
npm run dev

# Output:
# ✓ 106 modules transformed
# VITE v4.5.14 watching for file changes...
```

All changes will now be automatically rebuilt! 🔄

### Step 5: Open in Browser

```
http://localhost/nextcloud/index.php/apps/verein-dev/
```

---

## ⚙️ Configuration

### App Info File

The app is configured through `appinfo/info.xml`:

```xml
<?xml version="1.0"?>
<info>
    <id>verein</id>
    <name>Association App</name>
    <summary>Management for clubs and organizations</summary>
    <version>0.2.1</version>
    <licence>AGPL</licence>
    <author>Stefan Schulz</author>
    <namespace>OCA\Verein</namespace>
    <documentation>https://github.com/Wacken2012/nextcloud-verein</documentation>
</info>
```

---

## 🐛 Troubleshooting

### Problem: "App not found"

**Cause:** App not enabled or build failed

**Solution:**
```bash
# Check app status
sudo -u www-data php /var/www/nextcloud/occ app:list | grep verein

# Re-enable app
sudo -u www-data php /var/www/nextcloud/occ app:disable verein
sudo -u www-data php /var/www/nextcloud/occ app:enable verein

# Clear cache
sudo -u www-data php /var/www/nextcloud/occ cache:clear-all
```

### Problem: "Permission denied" during npm install

**Solution:**
```bash
# Fix permissions
chmod -R 755 node_modules/

# Or: Reinstall completely
rm -rf node_modules package-lock.json
npm install
```

### Problem: Build fails

**Solution:**
```bash
# Check Node version
node --version    # should be ≥16.0.0
npm --version     # should be ≥7.0.0

# Reinstall dependencies
rm -rf node_modules
npm install

# Build again
npm run build
```

---

**Last Updated:** December 2025  
**App Version:** v0.2.1
