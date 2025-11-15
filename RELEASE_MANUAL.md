# 📚 Anleitung: GitHub Release manuell erstellen

## Schritt 1: Zur Release-Seite navigieren

1. Gehe zu: https://github.com/Wacken2012/nextcloud-verein/releases
2. Klicke auf den Button **"Draft a new release"** (oben rechts)

---

## Schritt 2: Release-Tag auswählen

1. Klicke auf **"Choose a tag"**
2. Wähle **`v0.1.0-alpha`** aus der Liste
3. Der Tag wird als **"Existing tag"** bestätigt

---

## Schritt 3: Release-Informationen eintragen

### Release-Titel:
```
Nextcloud Vereins-App v0.1.0-alpha
```

### Release-Beschreibung:
Kopiere den kompletten Text aus **Schritt 4** unten

---

## Schritt 4: Release-Text (Deutsch)

Kopiere alles zwischen den Doppelstrichen und füge es ins Beschreibungsfeld ein:

```
# 🎉 Nextcloud Vereins-App

## 🌟 Was ist neu

Diese **Alpha-Version** bietet die Kernfunktionalität für die Verwaltung von Vereinen und Organisationen.

### ✨ Hauptmerkmale

#### 👥 Mitgliederverwaltung
- ✅ Mitglieder anlegen, bearbeiten, löschen
- ✅ Mitgliederdaten: Name, E-Mail, Adresse, IBAN, BIC
- ✅ Rollen-Management: Mitglied, Kassierer, Admin
- ✅ Responsive Tabelle mit Inline-Editing
- ✅ Suchfunktion

#### 💰 Finanzverwaltung
- ✅ Gebühren und Beitragsverfolgung
- ✅ Status-Tracking: offen, bezahlt, überfällig
- ✅ Statistiken: Gesamtausstände, eingezahlte Beträge
- ✅ Gebührentabelle mit Bearbeitungs- und Löschfunktion
- ✅ Nach Mitgliedern filtern

#### 🎨 Benutzer-Erlebnis
- ✅ Dark Mode Support
- ✅ Responsive Design (Desktop, Tablet, Mobile)
- ✅ Nextcloud-native Authentifizierung
- ✅ Moderne Vue 3 + Vite Frontend
- ✅ Keine Fehler in der Browserkonsole

---

## 📋 Anforderungen

- **Nextcloud**: 28.0 oder höher
- **PHP**: 8.1 oder höher
- **Datenbank**: MySQL/MariaDB oder PostgreSQL
- **Browser**: Modern (Chrome, Firefox, Safari, Edge)

---

## 🔧 Installation

### Methode 1: Aus dem Release (Einfach)

1. Herunterladbare Datei herunterladen: `nextcloud-verein-v0.1.0-alpha-release.tar.gz`
2. In `/var/www/nextcloud/apps/` extrahieren:
   ```bash
   cd /var/www/nextcloud/apps/
   tar -xzf nextcloud-verein-v0.1.0-alpha-release.tar.gz
   mv nextcloud-verein verein
   ```
3. App aktivieren:
   ```bash
   sudo -u www-data php /var/www/nextcloud/occ app:enable verein
   ```
4. Zugreifen unter: `https://your-nextcloud/apps/verein/`

### Methode 2: Aus dem Quellcode (Entwicklung)

1. Repository klonen:
   ```bash
   cd /var/www/nextcloud/apps/
   git clone https://github.com/Wacken2012/nextcloud-verein.git verein
   cd verein
   ```
2. Abhängigkeiten installieren:
   ```bash
   npm install
   npm run build
   ```
3. App aktivieren:
   ```bash
   sudo -u www-data php /var/www/nextcloud/occ app:enable verein
   ```

---

## ✅ Getestete & funktionierende Funktionen

- ✅ Mitglieder-CRUD (Create, Read, Update, Delete)
- ✅ Gebühren-CRUD
- ✅ Statistiken-Dashboard
- ✅ Dark Mode
- ✅ Responsive Design
- ✅ API-Integration
- ✅ Nextcloud-Authentifizierung

---

## ⚠️ Bekannte Einschränkungen

- ⚠️ **Keine Berechtigungsverwaltung**: Alle angemeldeten Benutzer können alle Daten sehen/ändern
- ⚠️ **Keine Datenexporte**: CSV/PDF-Export nicht implementiert
- ⚠️ **Keine Tests**: Unit/Integration Tests ausstehend
- ⚠️ **Keine Dokumentation im Code**: Inline-Kommentare minimal
- ⚠️ **Alpha-Status**: Möglicherweise Bugs, API kann sich ändern

---

## 🗓️ Roadmap

### v0.2.0 (Dezember 2025)
- Berechtigungsverwaltung (Ansicht, Bearbeitung, Admin)
- CSV-Export für Mitglieder und Gebühren
- Gebührenerinnerungen (E-Mail-Benachrichtigungen)

### v0.3.0 (Januar 2026)
- Kalender-Integration (Nextcloud Calendar)
- Gebührenplan und automatische Rechnungen
- Mitgliedsantragsformular

### v1.0.0 (März 2026)
- Vollständige Dokumentation
- Unit- und Integration-Tests
- Performance-Optimierungen
- Nextcloud AppStore-Veröffentlichung

---

## 📚 Dokumentation

- [README.md](https://github.com/Wacken2012/nextcloud-verein/blob/main/README.md) - Hauptdokumentation
- [INSTALLATION.md](https://github.com/Wacken2012/nextcloud-verein/blob/main/INSTALLATION.md) - Installationsanleitung
- [ROADMAP.md](https://github.com/Wacken2012/nextcloud-verein/blob/main/ROADMAP.md) - Entwicklungsplan
- [LIZENZ: AGPL-3.0](https://github.com/Wacken2012/nextcloud-verein/blob/main/LICENSE)

---

## 🧪 Zum Testen

1. App auf Nextcloud-Instanz installieren
2. Zum Tab **"Mitglieder"** navigieren und ein Mitglied hinzufügen
3. Zum Tab **"Finanzen"** gehen und eine Gebühr erstellen
4. Dark Mode testen: Nextcloud-Einstellungen > Erscheinungsbild
5. Auf mobilen Geräten testen
6. Browser-Konsole auf Fehler prüfen (F12)

---

## 💬 Feedback & Fehlermeldungen

Bitte erstellen Sie ein [Issue auf GitHub](https://github.com/Wacken2012/nextcloud-verein/issues) für:
- 🐛 Fehlerberichte
- 💡 Feature-Anfragen
- 🎨 Design-Verbesserungen
- 📝 Dokumentations-Verbesserungen

---

## 📄 Lizenz

Diese App ist lizenziert unter der **AGPL-3.0 Lizenz** - siehe [LICENSE](LICENSE) für Details.

---

## 👏 Credits

Entwickelt mit ❤️ für Nextcloud-Benutzer und Vereinsverwaltungen.

**Danke** an die Nextcloud-Community und alle Mitwirkenden!
```

---

## Schritt 5: Checkboxen konfigurieren

In der Release-Erstellung findest du folgende Optionen:

- ☑️ **"This is a pre-release"** → UNBEDINGT ANKREUZEN (Alpha-Status)
- ☐ "Set as the latest release" → NICHT ankreuzen (noch alpha)

---

## Schritt 6: Artefakte hochladen (Dateien anhängen)

1. Scrolle nach unten zum Bereich **"Attach binaries by dropping them here or selecting them."**

2. Du benötigst diese 2 Dateien:
   - `nextcloud-verein-v0.1.0-alpha.tar.gz`
   - `nextcloud-verein-v0.1.0-alpha-release.tar.gz`

3. Diese befinden sich im Verzeichnis:
   ```
   /home/stefan/Dokumente/Programmieren lernen/Nextcloud-Verein/release/
   ```

4. **Drag & Drop** oder klicke auf den Upload-Bereich und wähle beide Dateien aus

---

## Schritt 7: Release veröffentlichen

1. Klicke auf **"Publish release"** Button (grün, unten rechts)
2. Die Release wird sofort live gehen! ✅

---

## Fertig! 🎉

Deine Release ist jetzt unter dieser URL öffentlich verfügbar:
```
https://github.com/Wacken2012/nextcloud-verein/releases/tag/v0.1.0-alpha
```

Benutzer können die Dateien dort herunterladen und installieren!

---

## Troubleshooting

**Problem: Artefakte werden nicht hochgeladen?**
- Die Dateien sollten aus dem Projektverzeichnis stammen
- Nutze den absoluten Pfad wenn nötig

**Problem: Release-Text wird nicht richtig angezeigt?**
- GitHub erkennt Markdown automatisch
- Überprüfe dass du den kompletten Text aus Schritt 4 kopiert hast

**Problem: Tag `v0.1.0-alpha` wird nicht angeboten?**
- Tag ist noch nicht gepusht
- Führe aus: `git push origin v0.1.0-alpha`
