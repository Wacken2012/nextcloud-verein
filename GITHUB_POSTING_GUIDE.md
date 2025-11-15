# 🚀 GitHub Issue & Discussion Posting Guide

Schritt-für-Schritt Anleitung zum Posten der vorbereiten Templates auf GitHub.

---

## 📋 OPTION 1: GitHub Issue posten

### Schritt 1: Zum Repository navigieren

```
https://github.com/Wacken2012/nextcloud-verein
```

### Schritt 2: Issues Tab öffnen

```
Klick auf "Issues" Tab (neben "Code")
```

### Schritt 3: Neue Issue erstellen

```
Grüner Button "New Issue"
```

### Schritt 4: Issue-Inhalt kopieren

**Datei öffnen:**
```bash
cat ".github/ISSUE_RESPONSIVE_LAYOUT.md"
```

**Oder im Editor anschauen:**
```
.github/ISSUE_RESPONSIVE_LAYOUT.md
```

### Schritt 5: Inhalt einfügen

```
Title:       [Feedback] Responsive Layout & Theme Integration – Bitte testen!

Description: (Gesamten Inhalt aus ISSUE_RESPONSIVE_LAYOUT.md kopieren)
```

### Schritt 6: Labels hinzufügen

```
Klick "Labels" (rechts)
Auswählen:
✅ enhancement
✅ feedback
✅ testing

(Oder erstelle neue Labels)
```

### Schritt 7: Assignees hinzufügen (optional)

```
Klick "Assignees" (rechts)
Dich selbst auswählen als Verantwortlicher
```

### Schritt 8: Submit

```
Grüner Button "Submit new issue"
```

**Fertig! ✅ Issue ist jetzt live.**

---

## 💬 OPTION 2: GitHub Discussion posten

### Schritt 1: Zum Repository navigieren

```
https://github.com/Wacken2012/nextcloud-verein
```

### Schritt 2: Discussions Tab öffnen

```
Klick auf "Discussions" Tab
```

### Schritt 3: Neue Discussion erstellen

```
Grüner Button "New discussion"
```

### Schritt 4: Discussion-Inhalt kopieren

**Datei öffnen:**
```bash
cat ".github/DISCUSSION_RESPONSIVE_LAYOUT.md"
```

**Oder im Editor anschauen:**
```
.github/DISCUSSION_RESPONSIVE_LAYOUT.md
```

### Schritt 5: Inhalte einfügen

```
Title:       Responsive Layout & Dark-Mode – Was haltet ihr davon?

Category:    Q&A (Aus Dropdown auswählen)

Description: (Gesamten Inhalt aus DISCUSSION_RESPONSIVE_LAYOUT.md kopieren)
```

### Schritt 6: Submit

```
Grüner Button "Start discussion"
```

**Fertig! ✅ Discussion ist jetzt live.**

---

## 📄 VERKNÜPFUNG: Issue ↔ Discussion

### In der Issue auf die Discussion verweisen

**In der Issue kommentieren:**
```markdown
👉 Siehe auch die passende [Discussion](https://github.com/Wacken2012/nextcloud-verein/discussions/X) 
für allgemeine Fragen und Feedback!
```

### In der Discussion auf die Issue verweisen

**In der Discussion Beschreibung unten hinzufügen:**
```markdown
🔗 Verwandt: [Feedback Issue](https://github.com/Wacken2012/nextcloud-verein/issues/X) 
für Fehlerberichte und Tests
```

---

## 📊 Erwartete Struktur nach Posting

```
GitHub Repo
├─ Issues
│  └─ #123: [Feedback] Responsive Layout & Theme Integration
│           (mit Testing Checklist & Bug Template)
│
└─ Discussions
   └─ #45: Responsive Layout & Dark-Mode – Was haltet ihr davon?
           (mit Q&A Format & Community Questions)
```

---

## 🔔 Nach dem Posting

### 1. Freigeben in Community

**Optional: Auf Nextcloud Forum posten**
```
https://help.nextcloud.com/c/apps/verein/

Titel: "Neue Vereins-App v0.1.0: Responsive Layout & Dark-Mode"
Link zur GitHub Issue/Discussion
```

### 2. Monitoring

**GitHub Benachrichtigungen aktivieren:**
```
Settings (oben rechts) → 
Notifications → 
Watch "[nextcloud-verein]"
```

**Dadurch bekommst du:**
- ✅ Neue Kommentare auf Issues
- ✅ Neue Replies auf Discussions
- ✅ Mentions (@Stefan)

### 3. Regelmäßig checken

**Routine:**
```
Montag:    Neue Issues/Discussions prüfen
Mittwoch:  Kommentare beantworten
Freitag:   Wochenrückblick + Planung
```

---

## 💡 Tipps für gutes Community-Engagement

### Schnell antworten
```
Ziel: < 24 Stunden auf Kommentare
(zeigt dass du alive bist)
```

### Konstruktive Kritik nehmen
```
❌ "Das ist falsch"
✅ "Danke für dein Feedback! Wie könnte man das verbessern?"
```

### Dankbar sein
```
Jede Person die testet ist wertvoll!
→ Bedank dich mit: "Danke für die Rückmeldung! 🙏"
```

### Roadmap transparent halten
```
Wenn zu viele Feature-Requests:
"Gute Idee! Das gehört auf die Roadmap für v0.2.0"
```

---

## 🐛 Häufige Fragen beantworten

**"Wann v1.0.0?"**
```
→ Siehe Roadmap in Home.md
→ Aktuell: v0.1.0-alpha
```

**"Kann ich die App forken?"**
```
→ Ja! AGPL-3.0 Lizenz
→ Siehe Development.md für Guide
```

**"Funktioniert auf Android?"**
```
→ Im Mobile Browser ja
→ Native App noch nicht geplant
```

**"Wie melde ich einen Bug?"**
```
→ Neue Issue mit Screenshots
→ Schritt-für-Schritt Anleitung
```

---

## 📈 Success Metrics (Nach 1 Woche)

```
Erwartete Community-Activity:

Issues:
  - 3-5 neue Bug-Reports
  - 2-3 Feature-Requests
  - 1-2 Fragen

Discussions:
  - 10-15 Kommentare
  - 2-3 neue Discussions
  - 1-2 neue Contributors interessiert
```

---

## 🎯 Nächste Schritte nach Feedback

### Priorisierung

```
Priorität 1: Kritische Bugs
  → Sofort fixen
  
Priorität 2: Häufige Fehler
  → Diese Woche
  
Priorität 3: Kleinere Requests
  → Auf Roadmap für v0.2.0
```

### Kommunikation

```
In Issue/Discussion schreiben:
"Danke für das Feedback! Das planen wir für v0.2.0"

Dadurch:
✅ Benutzer fühlt sich gehört
✅ Klare Erwartungshaltung
✅ Mehr Community-Vertrauen
```

---

## 📞 Ressourcen

- [GitHub Help: Creating an Issue](https://docs.github.com/en/issues/tracking-your-work-with-issues/creating-an-issue)
- [GitHub Help: Discussions](https://docs.github.com/en/discussions)
- [Community Guidelines](https://docs.github.com/en/site-policy/github-terms/github-community-guidelines)

---

## ✅ Checklist vor Posting

- [ ] Alle 4 Wiki-Seiten sind published
- [ ] README.md auf GitHub aktualisiert
- [ ] ISSUE_RESPONSIVE_LAYOUT.md bereit zum Kopieren
- [ ] DISCUSSION_RESPONSIVE_LAYOUT.md bereit zum Kopieren
- [ ] App ist live in Nextcloud
- [ ] Alle Commits sind gepusht
- [ ] GitHub Notifications aktiviert

---

**Viel Spaß mit der Community! 🚀**

Nach Fragen → [GitHub Discussions](https://github.com/Wacken2012/nextcloud-verein/discussions)
