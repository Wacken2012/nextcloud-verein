# Frequently Asked Questions (FAQ)

Häufig gestellte Fragen zur Nextcloud Vereins-App.

---

## 🎯 Allgemein

### Was ist die Nextcloud Vereins-App?

Die **Nextcloud Vereins-App** ist eine spezialisierte Anwendung zur Verwaltung von Vereinen, Clubs und Organisationen direkt in Nextcloud. Sie bietet Funktionen für:

- Mitgliederverwaltung
- Gebührenverwaltung
- Statistiken und Dashboard
- Responsive Design
- Dark-Mode Support

### Kostet die App etwas?

**Nein!** Die App ist vollständig kostenlos und Open Source unter der AGPL-3.0 Lizenz.

### Wer entwickelt die App?

Die App wird von **Stefan Schulz** entwickelt und unterstützt durch die Nextcloud Community und GitHub Copilot.

### Welche Lizenz hat die App?

```
AGPL-3.0 License
→ Quellcode muss offen sein
→ Änderungen müssen weitergegeben werden
→ Kommerzielle Nutzung erlaubt
```

Siehe: [LICENSE](https://github.com/Wacken2012/nextcloud-verein/blob/main/LICENSE)

---

## 📦 Installation & Setup

### Welche Voraussetzungen gibt es?

```
Nextcloud:    24.0+
PHP:          8.0+
Node.js:      16.0+ (nur für Development)
RAM:          512 MB Minimum
Speicher:     50 MB für die App
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

### Gibt es Docker-Support?

**Noch nicht!** Geplant für v1.0.0.

Für jetzt: Manuelle Installation im Nextcloud Container.

### Kann ich die App selbst hosten?

**Ja!** Die App ist für selbst-gehostete Nextcloud-Installationen ausgelegt.

### Kann ich die App in der Cloud hosten (z.B. Hetzner)?

**Ja!** Solange Nextcloud läuft, funktioniert die App.

---

## 🎨 Features & Funktionalität

### Welche Features gibt es aktuell (v0.1.0)?

```
✅ Mitgliederverwaltung (CRUD)
✅ Gebührenverwaltung (CRUD)
✅ Dashboard mit Statistiken
✅ Responsive Layout (Desktop/Tablet/Mobile)
✅ Dark-Mode Support
✅ Nextcloud Theme-Integration
✅ Accessibility Features
```

### Wann kommt Feature XY?

Siehe: [Roadmap.md](./Roadmap.md)

Geplante Features:
- **v0.2.0:** Rollen, Validierungen, SEPA-Export
- **v0.3.0:** Mahnungen, Kalender, Notifications
- **v1.0.0:** App-Store Release

### Kann ich die App anpassen?

**Ja!** Der Code ist Open Source und kann angepasst werden.

**Option 1:** Selber ändern (Fork)
```bash
git clone https://github.com/DEIN_USERNAME/nextcloud-verein.git
# Änderungen machen
# Pull Request erstellen
```

**Option 2:** Feature-Request erstellen
```
GitHub Issues: https://github.com/Wacken2012/nextcloud-verein/issues
```

### Gibt es eine Demo?

**Nein, aber:** Kontaktiere den Entwickler auf GitHub für Test-Zugang.

---

## 🔒 Sicherheit & Datenschutz

### Wie sicher ist die App?

```
✅ Alle Daten bleiben in deiner Nextcloud
✅ HTTPS-Verschlüsselung (wenn Nextcloud HTTPS nutzt)
✅ Berechtigungssystem geplant (v0.2.0+)
✅ Regelmäßige Security-Reviews
```

### Kann jeder alle Daten sehen?

**Aktuell (v0.1.0):** Berechtigungen sind noch nicht implementiert.

**Ab v0.2.0:** Granulare Berechtigungen für:
- Daten-Sicht (öffentlich/privat)
- Bearbeitung (Admin/Member)
- Berichtszugriff

### Wo werden die Daten gespeichert?

Die Daten werden in der **Nextcloud-Datenbank** gespeichert:
- SQLite (default)
- MySQL / MariaDB
- PostgreSQL

Konfiguriert in `config/config.php`.

### Kann ich Daten exportieren?

**Ja!** Geplant für v0.2.0:
```bash
# CSV-Export
# PDF-Export
# SEPA-XML (für Gebühren)
```

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
```

### Fehler beim Laden der Daten

**Ursache:** API nicht erreichbar

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
# Browser-Cache leeren
# Oder: Strg+Shift+Delete

# Nextcloud neuladen
# F5 drücken
```

### Responsive Layout kaputt auf Mobile

**Ursache:** Viewport-Meta-Tag fehlt

**Lösung:**
```bash
# Sollte automatisch gesetzt sein
# Sonst: Browser-Cache leeren

# Developer Tools öffnen (F12)
# Device emulieren
```

### Weitere Hilfe?

→ Siehe: [Troubleshooting.md](./Troubleshooting.md)

---

## 👥 Community & Support

### Wie kann ich Bugs berichten?

```
GitHub Issues: https://github.com/Wacken2012/nextcloud-verein/issues

Bitte berichtet:
- App-Version (Admin → Apps)
- Nextcloud-Version
- Browser & Betriebssystem
- Was habt ihr getan?
- Was ist schief gelaufen?
- Screenshot/Video (wenn möglich)
```

### Wie kann ich Features vorschlagen?

```
GitHub Discussions: https://github.com/Wacken2012/nextcloud-verein/discussions

Oder:
GitHub Issues: Mit Label "enhancement"
```

### Wie kann ich beitragen?

```
1. Repository forken
2. Feature-Branch erstellen
3. Commits mit aussagekräftigen Messages
4. Push und Pull Request erstellen
5. Code-Review abwarten

Richtlinien: siehe DEVELOPMENT.md
```

### Wo kann ich mit anderen Nutzern reden?

```
GitHub Discussions (Q&A):
https://github.com/Wacken2012/nextcloud-verein/discussions

Nextcloud Forum:
https://help.nextcloud.com/
```

---

## 📱 Mobile & Responsiveness

### Funktioniert die App auf dem Handy?

**Ja!** Die App ist vollständig responsive:

```
📱 Mobile (<768px):     1-Spalten Layout, Icon-only Nav
📱 Tablet (768-1023px): 2-Spalten Layout
🖥️  Desktop (≥1024px):   4-Spalten Layout
```

### Kann ich die App auf iOS verwenden?

**Ja!** Im Safari-Browser (iPhone/iPad).

**Desktop-App:** Geplant für zukünftige Versionen.

### Wie sieht das Layout auf dem Handy aus?

```
Responsive Darstellung:
✅ Touch-freundliche Buttons (48px+)
✅ Lesbare Schriftgrößen
✅ Volles Funktionsumfang
✅ Keine horizontalen Scrolls
✅ Sticky Navigation (oben)
```

---

## 🌙 Dark-Mode

### Wie aktiviere ich Dark-Mode?

```
Nextcloud → Einstellungen (Avatar oben rechts)
           → Darstellung
           → Design: "Helligkeit"
           → Auswahl ändern
```

Die App passt sich automatisch an! 🌙

### Funktioniert Dark-Mode auf allen Geräten?

**Ja!** Unterstützt von:
- Chrome 76+
- Firefox 67+
- Safari 12.1+
- Edge 79+

---

## 📊 Statistiken & Reports

### Wie generiere ich einen Report?

**Aktuell:** Dashboard mit Live-Statistiken

**Geplant (v0.2.0+):**
```
PDF-Export
CSV-Export
Benutzerdefinierte Reports
```

### Welche Statistiken gibt es?

```
✅ Mitgliederzahl
✅ Offene Gebühren
✅ Monatliche Einnahmen
✅ Zahlungshistorie
✅ Charts (Balken, Kuchen, Linien)
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

### Gibt es Breaking Changes zwischen Versionen?

**Nein!** Alle Versionen sind abwärtskompatibel.

Datenbank-Migration erfolgt automatisch.

### Wie lange werden alte Versionen unterstützt?

```
v0.1.x:  Aktuelle Entwicklungsversion
v0.2.x:  Nach Release unterstützt bis v1.0.0
v1.0.x+: Mindestens 2 Jahre Unterstützung
```

---

## 📞 Kontakt & Weitere Hilfe

### Wo finde ich weitere Hilfe?

| Ressource | Zweck |
|-----------|-------|
| [Installation.md](./Installation.md) | Setup-Anleitung |
| [Troubleshooting.md](./Troubleshooting.md) | Problembehebung |
| [Roadmap.md](./Roadmap.md) | Zukünftige Features |
| [GitHub Issues](https://github.com/Wacken2012/nextcloud-verein/issues) | Bug-Reports |
| [GitHub Discussions](https://github.com/Wacken2012/nextcloud-verein/discussions) | Fragen & Ideen |

---

**Hast du deine Frage nicht beantwortet bekommen?**

→ Erstelle eine Diskussion: https://github.com/Wacken2012/nextcloud-verein/discussions

Ich helfe gerne! 🙏

*Entwickelt mit ❤️ von Stefan Schulz*
