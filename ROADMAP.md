# 🗺️ Roadmap – Nextcloud Vereins-App

Öffentliche Roadmap für die Entwicklung der Nextcloud Vereins-App. Status und geplante Features für die nächsten Versionen.

---

## 📊 Version Overview

| Version | Status | Release | Fokus |
|---------|--------|---------|-------|
| **v0.1.0** | ✅ Aktuell | Nov 2025 | Basis CRUD, MVP |
| **v0.2.0** | 🔄 In Planung | Q1 2026 | Berechtigungen, Export |
| **v0.3.0** | �� Geplant | Q2 2026 | Automatisierung, Integrationen |
| **v1.0.0** | 🎯 Ziel | Q4 2026 | Production-Ready |

---

## ✅ v0.1.0-alpha (AKTUELL)

**Release**: November 2025

### Features
- [x] Mitgliederverwaltung (CRUD)
- [x] Gebührenverwaltung (CRUD)
- [x] Responsive Vue 3 UI
- [x] Dark Mode
- [x] Basis API

### Known Issues
- [ ] Rollen & Berechtigungen (alle Nutzer = Admin)
- [ ] Keine Datenvalidierung (IBAN, E-Mail)
- [ ] Keine Export-Funktionalität
- [ ] Keine Benachrichtigungen

### Tech Schulden
- Unit Tests fehlen (0% Coverage)
- E2E Tests fehlen
- Dokumentation unvollständig
- Performance nicht optimiert

---

## 🔄 v0.2.0-beta (Q1 2026)

### 🎯 Fokus: Berechtigungen & Datenqualität

**Geplante Features:**
- [ ] Rollen & Berechtigungen
  - Admin: Volle Kontrolle
  - Kassierer: Gebühren verwalten
  - Mitglied: Nur eigene Daten
- [ ] Input-Validierung
  - IBAN/BIC Validierung
  - E-Mail Format
  - Pflichtfelder
  - Duplikat-Prüfung
- [ ] CSV Export
  - Mitgliederliste
  - Gebührenübersicht
  - Offene Beiträge
- [ ] Erweiterte Statistiken
  - Charts & Diagramme
  - Historische Daten
  - Trends

### 🔧 Tech Improvements
- [ ] Unit Tests (PHP + Vue)
- [ ] E2E Tests (Cypress)
- [ ] Error Handling
- [ ] Performance Optimierungen
- [ ] Security Audit

### 📚 Documentation
- [ ] API Documentation (Swagger/OpenAPI)
- [ ] Developer Guide
- [ ] Architecture Docs

---

## 📋 v0.3.0 (Q2 2026)

### 🎯 Fokus: Automatisierung & Integrationen

**Geplante Features:**
- [ ] Automatische Mahnungen
  - Cronjob für Beiträge
  - E-Mail Benachrichtigungen
  - Mahnstufen (1., 2., Mahnung)
- [ ] Kalender Integration
  - Mitgliederverwaltung im Kalender
  - Gebühren-Fristen als Events
- [ ] Deck Integration
  - Aufgaben-Management
  - Beitragsabrechnung
- [ ] Direktnachrichten (Talk)
  - Benachrichtigungen via Chat
  - Admin-Alerts

### �� Security & Permissions
- [ ] Erweiterte Rollen
  - Custom Permissions
  - Datenschutz (GDPR)
  - Audit Logs

### 💾 Data Export
- [ ] PDF Export
  - Professionelle Layouts
  - Gebührenlisten
  - Jahresabschlüsse
- [ ] SEPA XML (für Bankentransfers)

---

## 🎯 v1.0.0 (Q4 2026)

### 🎯 Fokus: Production Release

**Stabilität & Polish:**
- [ ] 100% Unit Test Coverage
- [ ] Security Audit (Third-Party)
- [ ] Performance: < 1s Ladezeit
- [ ] i18n (Internationalisierung)
  - English
  - Deutsch
  - Weitere Sprachen

### 🌟 New Features
- [ ] Mitgliedsbeitrag-Automationen
  - Automatische Einzüge (SEPA)
  - Zahlungsplan
  - Kündigungen
- [ ] Berichts-Generator
  - Jahresberichte
  - Kassenprüfung
  - Statistiken
- [ ] Web-Shop (optional)
  - Merchandise-Verkauf
  - Ticketing

### 📦 Deployment
- [ ] Nextcloud App Store Release
- [ ] Docker Image
- [ ] Installationsscript

---

## 🐛 Known Issues & Limitations

### v0.1.0-alpha
| Issue | Severity | Workaround | ETA |
|-------|----------|-----------|-----|
| Keine Berechtigungen | 🔴 Kritisch | Nur mit Admin nutzen | v0.2.0 |
| IBAN nicht validiert | 🟡 Medium | Manuell prüfen | v0.2.0 |
| Kein Export | 🟡 Medium | Manual Export vom DB | v0.2.0 |
| Keine Benachrichtigungen | 🟢 Low | E-Mail selbst senden | v0.3.0 |

### Performance
- Bundle-Größe: 387 KB (sollte < 200 KB sein)
- Datenbankqueries: nicht optimiert
- Keine Caching-Strategie

### Security (TODO vor v1.0.0)
- [ ] Rate Limiting
- [ ] CSRF Protection
- [ ] Input Sanitization (alle Felder)
- [ ] Output Escaping
- [ ] Security Headers

---

## 📈 Metrics & Goals

### Adoption Goals
- **v0.2.0**: 50+ Installationen
- **v0.3.0**: 200+ Installationen
- **v1.0.0**: 500+ Installationen (Ziel)

### Quality Goals
| Metrik | v0.1 | v0.2 | v0.3 | v1.0 |
|--------|------|------|------|------|
| Test Coverage | 0% | 50% | 80% | 100% |
| Bug Response | - | <7 days | <3 days | <1 day |
| Performance | - | < 2s | < 1s | < 500ms |

---

## 🎁 Community Features (Backlog)

Geplant, aber zeitlich nicht gebunden:

- [ ] Mobile App (iOS/Android)
- [ ] Multi-Language Support
- [ ] Member Portal (Self-Service)
- [ ] SMS Notifications
- [ ] Payment Gateway Integration (Stripe, PayPal)
- [ ] Advanced Reporting
- [ ] AI-powered Insights

---

## 🤝 How to Contribute

Du möchtest an dieser Roadmap mitarbeiten?

1. **Feature vorschlagen**: [GitHub Discussions](https://github.com/yourusername/nextcloud-verein/discussions)
2. **Bug melden**: [GitHub Issues](https://github.com/yourusername/nextcloud-verein/issues)
3. **Code beitragen**: [Pull Requests](https://github.com/yourusername/nextcloud-verein/pulls)
4. **Testen**: Download & Feedback geben

---

## 📅 Timeline

```
2025
├── Nov: v0.1.0-alpha (AKTUELL)
└── Dez: Bug Fixes & Feedback

2026
├── Q1: v0.2.0-beta (Perms, Export)
├── Q2: v0.3.0 (Automation, Integrations)
└── Q4: v1.0.0 (Production)
```

---

## 💡 Vision

**Langfristig**: Nextcloud Vereins-App soll die **Standard-Lösung** für Vereinsverwaltung in Nextcloud sein – mit modernem UI, stabiler API und aktiver Community.

**Mittelfristig**: Features, die große Vereine brauchen (Automatisierung, Reporting, Integrations).

**Kurzfristig**: Stabilität, Berechtigungen, gute Dokumentation.

---

## 📞 Feedback

Meinung zu dieser Roadmap?

- 💬 [GitHub Discussions](https://github.com/yourusername/nextcloud-verein/discussions)
- 📧 Email: (Deine Kontaktadresse)
- 🐦 Twitter/X: @yourusername

---

**Danke für dein Interesse an der Nextcloud Vereins-App!** 🎉

Zusammen machen wir die beste Vereinsverwaltungs-App! 🚀
