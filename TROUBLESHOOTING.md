# 🔧 Vereins-App Troubleshooting & Fixes

## Problem 1: Template nicht gefunden (template:main)

### Fehler
```
OCP\Template\TemplateNotFoundException: template file not found: template:main
```

### Root Cause
- Templates wurden nicht zum Server synchronisiert
- PageController verwendete hardcodiertes 'verein' statt `$this->appName`
- Nextcloud-Cache hatte alte Template-Konfiguration

### Lösung
1. **Templates synchronisieren:**
   ```bash
   sudo rsync -av templates/ /var/www/html/nextcloud/apps/verein/templates/
   ```

2. **PageController aktualisieren:**
   - Änderung von `new TemplateResponse('verein', 'main')` 
   - Zu `new TemplateResponse($this->appName, 'main')`

3. **Caches löschen:**
   ```bash
   sudo systemctl restart apache2
   sudo -u www-data php /var/www/html/nextcloud/occ app:disable verein
   sudo -u www-data php /var/www/html/nextcloud/occ app:enable verein
   ```

## Problem 2: App-Icon routet zu Dashboard statt zu Verein

### Fehler
Icon klick führt zu: `/apps/dashboard/` statt `/apps/verein/`

### Root Cause
- Wahrscheinlich Nextcloud-Cache mit alten App-Metadaten
- `info.xml` hatte falschen href

### Lösung
1. **info.xml prüfen** (sollte korrekt sein):
   ```xml
   <navigation>
       <name>Verein</name>
       <href>/apps/verein/</href>
       <route>page#index</route>
       <icon>app.svg</icon>
   </navigation>
   ```

2. **App neu laden:**
   ```bash
   sudo -u www-data php /var/www/html/nextcloud/occ app:disable verein
   sudo -u www-data php /var/www/html/nextcloud/occ app:enable verein
   ```

3. **Browser-Cache leeren:**
   - Nextcloud Dashboard neu laden
   - Browser F5/Ctrl+F5 drücken

## Synchronisierungs-Checkliste

✅ **Was wurde synchronisiert:**
- [x] lib/ (PHP Backend)
- [x] appinfo/ (Konfiguration)
- [x] templates/ (HTML Templates)
- [x] img/ (Icons)
- [x] js/ (Frontend JavaScript)
- [x] composer.* (Dependencies)

✅ **Permissions:**
- [x] www-data:www-data Ownership
- [x] 644 für Dateien
- [x] 755 für Verzeichnisse

## Empfohlene Entwickler-Workflow

### Nach Code-Änderungen:
```bash
# 1. Lokal testen
cd /home/stefan/Dokumente/Programmieren\ lernen/Nextcloud-Verein

# 2. Sync zum Server
sudo rsync -av lib/ /var/www/html/nextcloud/apps/verein/lib/
sudo rsync -av templates/ /var/www/html/nextcloud/apps/verein/templates/

# 3. Permissions korrigieren
sudo chown -R www-data:www-data /var/www/html/nextcloud/apps/verein/

# 4. Cache löschen & neustarten
sudo systemctl restart apache2
```

### Alternativ: Vollständige Neu-Deploy
```bash
# App deaktivieren
sudo -u www-data php /var/www/html/nextcloud/occ app:disable verein

# Komplette Rsync
sudo rsync -av \
  /home/stefan/Dokumente/Programmieren\ lernen/Nextcloud-Verein/ \
  /var/www/html/nextcloud/apps/verein/ \
  --exclude='node_modules' \
  --exclude='.git' \
  --exclude='vendor'

# Permissions neu setzen
sudo chown -R www-data:www-data /var/www/html/nextcloud/apps/verein/

# App reaktivieren
sudo -u www-data php /var/www/html/nextcloud/occ app:enable verein

# Apache restart
sudo systemctl restart apache2
```

## Debugging-Tipps

### Logs prüfen
```bash
# Letzte 50 Zeilen
sudo tail -50 /var/www/html/nextcloud/data/nextcloud.log

# Nur Fehler & Warnungen
sudo tail -100 /var/www/html/nextcloud/data/nextcloud.log | grep -i "error\|warning\|exception"

# Spezifisch für verein app
sudo tail -100 /var/www/html/nextcloud/data/nextcloud.log | grep "verein"
```

### PHP Syntax validieren
```bash
# Einzelne Datei
php -l /var/www/html/nextcloud/apps/verein/lib/Controller/PageController.php

# Alle PHP-Dateien
find /var/www/html/nextcloud/apps/verein/lib -name "*.php" -exec php -l {} \;
```

### Template-Dateien prüfen
```bash
# Sollten existieren
ls -la /var/www/html/nextcloud/apps/verein/templates/

# Inhalt prüfen
head -20 /var/www/html/nextcloud/apps/verein/templates/main.php
```

###  Routing testen
```bash
# Routes prüfen
cat /var/www/html/nextcloud/apps/verein/appinfo/routes.php

# App-Info prüfen
cat /var/www/html/nextcloud/apps/verein/appinfo/info.xml | grep -A 5 "navigation"
```

## Status nach Fixes

- ✅ Templates vorhanden
- ✅ PageController korrigiert
- ✅ Permissions korrekt
- ✅ App ist registered und enabled
- ⏳ Warten auf Nextcloud-Cache-Refresh

## Nächste Schritte

1. Browser auffrischen
2. Auf Verein-Icon klicken
3. Main-Template sollte laden (mit Hinweis "Vereins-App wird geladen")
4. Frontend Vue.js Bundle einbinden
5. API-Endpoints testen

---

**Last Update:** 15 Nov 2025 14:52 UTC
