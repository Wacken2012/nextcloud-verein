# 📖 Installation Guide – Nextcloud Vereins-App

Detaillierte Anleitung zur Installation und Konfiguration der Nextcloud Vereins-App.

---

## 📋 Voraussetzungen

### Server-Anforderungen
- **Nextcloud**: 28.0 oder höher
- **PHP**: 8.1 oder höher
- **Database**: MySQL 5.7+, MariaDB 10.2+ oder PostgreSQL 9.0+
- **Web Server**: Apache oder Nginx mit URL Rewriting
- **Disk Space**: ~50 MB (+ Platz für Daten)

### Optionale Anforderungen
- Node.js 18+ (nur wenn du die App selbst bauen möchtest)
- Git (für Repository-Klone)

---

## 🔧 Installation

### Option 1: Einfach (Vorkompiliertes Release)

```bash
# 1. Ins Nextcloud Apps-Verzeichnis gehen
cd /var/www/nextcloud/apps/

# 2. Release herunterladen und entpacken
wget https://github.com/yourusername/nextcloud-verein/releases/download/v0.1.0/verein-v0.1.0.tar.gz
tar -xzf verein-v0.1.0.tar.gz
rm verein-v0.1.0.tar.gz

# 3. Berechtigungen setzen
chown -R www-data:www-data verein
chmod 755 verein

# 4. App aktivieren
sudo -u www-data php /var/www/nextcloud/occ app:enable verein

# 5. Fertig! In Nextcloud einloggen und App starten
```

---

### Option 2: Von Source (für Entwickler)

```bash
# 1. Repository klonen
cd /var/www/nextcloud/apps/
git clone https://github.com/yourusername/nextcloud-verein.git verein
cd verein

# 2. Node Dependencies installieren
npm install

# 3. Production Build erstellen
npm run build

# 4. Berechtigungen setzen
chown -R www-data:www-data /var/www/nextcloud/apps/verein
chmod 755 /var/www/nextcloud/apps/verein

# 5. App aktivieren
sudo -u www-data php /var/www/nextcloud/occ app:enable verein

# 6. Cache leeren
sudo -u www-data php /var/www/nextcloud/occ app:disable verein
sudo -u www-data php /var/www/nextcloud/occ app:enable verein
```

---

## ✅ Nach der Installation

### Erste Schritte

1. **In Nextcloud anmelden**
   - URL: `http://your-nextcloud.com/index.php/apps/verein/`
   - Mit Admin-Konto anmelden

2. **Erste Mitglieder hinzufügen**
   - Tab "Mitglieder" öffnen
   - Button "Neues Mitglied hinzufügen" klicken
   - Daten eintragen und speichern

3. **Gebühren erfassen**
   - Tab "Finanzen" öffnen
   - Button "Neue Gebühr hinzufügen" klicken
   - Mitglied, Betrag und Status auswählen

---

## 🔍 Diagnose & Troubleshooting

### Problem: App wird nicht angezeigt

```bash
# 1. App-Status prüfen
sudo -u www-data php /var/www/nextcloud/occ app:list

# 2. Logs anschauen
tail -f /var/www/nextcloud/data/nextcloud.log

# 3. Cache leeren
sudo -u www-data php /var/www/nextcloud/occ maintenance:mode --on
sudo -u www-data php /var/www/nextcloud/occ maintenance:mode --off
```

### Problem: 500er Fehler beim Laden

```bash
# 1. PHP-Version prüfen
php -v  # Muss 8.1+ sein

# 2. Berechtigungen prüfen
ls -la /var/www/nextcloud/apps/verein/

# 3. Nextcloud-Logs prüfen
grep -i "verein" /var/www/nextcloud/data/nextcloud.log | tail -20
```

### Problem: API-Fehler (404 bei Members/Finanzen)

```bash
# 1. Router neu laden
sudo -u www-data php /var/www/nextcloud/occ app:disable verein
sudo -u www-data php /var/www/nextcloud/occ app:enable verein

# 2. .htaccess prüfen (Apache)
curl -I http://your-nextcloud.com/index.php/apps/verein/members

# 3. URL Rewriting testen
sudo a2enmod rewrite
sudo systemctl restart apache2
```

---

## 🗄️ Datenbankinitialisierung

Die App erstellt automatisch folgende Tabellen beim Start:

- `oc_verein_members` – Mitgliederdaten
- `oc_verein_fees` – Gebührenverwaltung

**Manuell initialisieren** (falls nötig):

```bash
# Occ Command (später verfügbar)
sudo -u www-data php /var/www/nextcloud/occ verein:init
```

---

## 🔐 Sicherheit

### Best Practices

1. **Nur HTTPS verwenden**
   ```bash
   # In config.php
   'overwrite.cli.url' => 'https://your-nextcloud.com/',
   'forcessl' => true,
   ```

2. **Backup vor Update**
   ```bash
   # Vor der Installation
   mysqldump -u nextcloud -p nextcloud > backup_$(date +%Y%m%d).sql
   ```

3. **Berechtigungen setzen**
   ```bash
   # Richtige Owner
   chown -R www-data:www-data /var/www/nextcloud/apps/verein
   chmod 755 /var/www/nextcloud/apps/verein
   chmod 644 /var/www/nextcloud/apps/verein/**/*.{php,html,css,js}
   ```

4. **Regelmäßig updaten**
   - App-Updates installieren
   - Nextcloud updaten
   - PHP/Server-Software aktuell halten

---

## 🚀 Upgrade auf neue Version

```bash
# 1. Backup machen
mysqldump -u nextcloud -p nextcloud > backup_pre_upgrade.sql

# 2. App deaktivieren
sudo -u www-data php /var/www/nextcloud/occ app:disable verein

# 3. Alte App-Version sichern
mv /var/www/nextcloud/apps/verein /var/www/nextcloud/apps/verein.backup

# 4. Neue Version installieren
# (siehe Installation Option 1 oder 2)

# 5. App aktivieren
sudo -u www-data php /var/www/nextcloud/occ app:enable verein

# 6. Alte Backup löschen (wenn alles funktioniert)
rm -rf /var/www/nextcloud/apps/verein.backup
```

---

## 📞 Support

Falls Probleme auftreten:

1. **GitHub Issues**: [Bug Report](https://github.com/yourusername/nextcloud-verein/issues)
2. **Logs prüfen**: `/var/www/nextcloud/data/nextcloud.log`
3. **Nextcloud Forum**: [Nextcloud Community](https://help.nextcloud.com)

---

## ✨ Fertig!

Die App sollte jetzt unter `http://your-nextcloud.com/index.php/apps/verein/` erreichbar sein.

**Nächste Schritte:**
- Erste Mitglieder hinzufügen
- Gebühren erfassen
- Daten exportieren (in zukünftigen Versionen)

---

**Viel Spaß mit der Nextcloud Vereins-App!** 🎉
