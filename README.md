# 🤝 Nextcloud Vereins-App

Eine moderne, benutzerfreundliche **Nextcloud-App zur Verwaltung von Vereinen, Verbänden und Organisationen**. Mit vollständiger Mitglieder- und Finanzverwaltung.

**Status**: v0.2.0-beta (In Entwicklung) | **Lizenz**: AGPL-3.0 | **Nextcloud**: 28+

### 📊 Release Status

| Version | Status | Release | Features |
|---------|--------|---------|----------|
| **v0.1.0** | ✅ Released | Nov 2025 | Basis CRUD, MVP |
| **v0.2.0-beta** | 🔄 In Arbeit (80%) | Q1 2026 | ✅ RBAC, Admin Panel, Permission System |
| **v0.3.0** | 📋 Geplant | Q2 2026 | Automatisierung, Integrationen |

### 🆕 Was ist neu in v0.2.0-beta?

✅ **Role-Based Access Control (RBAC)**
- Admin, Treasurer, Member Rollen
- Granulare Berechtigungen für alle Endpoints
- Audit-Logging für Permission Violations
- 20+ Unit Tests für RBAC

✅ **Admin Settings Integration**
- Native Nextcloud Settings Seite (Settings → Administration → Verein)
- Role-Management im Admin-Panel
- Permission-Verwaltung

✅ **Verbesserte API Sicherheit**
- @RequirePermission Decorators auf 31 Endpoints
- AuthorizationMiddleware mit automatischen Checks
- 403 Forbidden bei fehlenden Permissions

---

## ✨ Features

### 👥 Mitgliederverwaltung
- Mitglieder anlegen, bearbeiten, löschen
- Datenfelder: Name, E-Mail, Adresse, IBAN, BIC, Rolle
- Rollen: Mitglied, Kassierer, Admin
- Responsive Tabelle mit Inline-Editing

### 💰 Finanzverwaltung
- Gebühren und Beitragsverfolgung
- Status-Tracking: offen, bezahlt, überfällig
- Statistiken: Gesamtausstände, bezahlte Beträge
- Schnelle Übersicht aller Transaktionen

### 🎨 User Experience
- Dark Mode Support
- Responsive Design (Desktop, Tablet, Mobile)
- Nextcloud-native Authentifizierung
- Schnelle Vue 3 + Vite Frontend

### 🔄 Weitere Tabs (geplant)
- 📅 **Kalender** (Nextcloud Calendar Integration)
- 📋 **Aufgaben** (Nextcloud Deck Integration)
- 📄 **Dokumente** (Nextcloud Files Integration)

---

## 🚀 Installation

### Anforderungen
- **Nextcloud**: 28.0 oder höher
- **PHP**: 8.1 oder höher
- **Database**: MySQL/MariaDB oder PostgreSQL

### Quick Install

```bash
# 1. Repo klonen
cd /var/www/nextcloud/apps/
git clone https://github.com/yourusername/nextcloud-verein.git verein
cd verein

# 2. Dependencies installieren
npm install
npm run build

# 3. App aktivieren
sudo -u www-data php /var/www/nextcloud/occ app:enable verein

# 4. Fertig! 
# In Nextcloud: Apps → Verein → Erste Mitglieder hinzufügen
```

**Detaillierte Anleitung**: Siehe [INSTALLATION.md](./INSTALLATION.md)

---

## 🎯 Roadmap

### v0.1.0 ✅ (Alpha - Aktuell)
- Basis Mitgliederverwaltung (CRUD)
- Gebührenverwaltung (CRUD)
- Responsive UI
- Dark Mode

### v0.2.0 🔧 (Beta - Nächste Phase)
- Rollen & Berechtigungen
- CSV/PDF Export
- Erweiterte Statistiken & Charts

### v0.3.0 📋 (Geplant)
- Automatische Mahnungen
- Benachrichtigungssystem
- Kalender-Integration

### v1.0.0 🎯 (Production)
- Vollständige Stabilität
- 100% Test-Coverage
- Umfangreiche Dokumentation

---

## 🛠️ Entwicklung

### Lokal entwickeln

```bash
# 1. Repository klonen
git clone <repo-url>
cd nextcloud-verein

# 2. Dependencies
npm install

# 3. Watch Mode (Vite Auto-Rebuild)
npm run dev

# 4. Prodktion Build
npm run build

# 5. Zum Server synchen
rsync -av js/dist/ /var/www/nextcloud/apps/verein/js/dist/
```

### Struktur

```
nextcloud-verein/
├── appinfo/
│   ├── info.xml          # App-Metadaten
│   └── routes.php        # API Routes
├── lib/
│   ├── Controller/       # PHP Controller
│   ├── Service/          # Business Logic
│   └── Db/              # Entity Models
├── js/
│   ├── components/       # Vue Components
│   ├── api.js           # Axios Wrapper
│   ├── main.js          # Entry Point
│   └── style.css        # Global Styles
├── templates/
│   └── main.php         # Main Template
├── tests/               # Unit Tests
└── package.json         # Node Dependencies
```

---

## 🤝 Contributing

Contributions sind willkommen! Bitte:

1. **Fork** das Repository
2. **Branch erstellen**: `git checkout -b feature/your-feature`
3. **Commit**: `git commit -m 'Add your feature'`
4. **Push**: `git push origin feature/your-feature`
5. **Pull Request** öffnen

Siehe [CONTRIBUTING.md](./CONTRIBUTING.md) für detaillierte Guidelines.

---

## 🐛 Known Issues & Limitationen

- Rollen & Berechtigungen noch nicht implementiert (alle Nutzer haben Admin-Zugriff)
- Keine Validierung von IBAN/BIC
- Export (CSV/PDF) noch nicht verfügbar
- Automatische Mahnungen noch nicht implementiert

---

## 📝 Lizenz

**AGPL-3.0** - Siehe [LICENSE](./LICENSE) für Details.

Diese App muss unter der gleichen Lizenz verteilt werden und ist für die Verwendung in Nextcloud-Instanzen konzipiert.

---

## ❓ Support

- **GitHub Issues**: [Bugs & Feature Requests](https://github.com/yourusername/nextcloud-verein/issues)
- **Discussions**: [Q&A & Ideas](https://github.com/yourusername/nextcloud-verein/discussions)

---

## 📚 Tech Stack

- **Frontend**: Vue 3 + Vite
- **Backend**: PHP + Nextcloud AppFramework
- **Database**: MySQL/MariaDB/PostgreSQL
- **Styling**: CSS + Nextcloud Design Variables

---

## 🙏 Danksagungen

Entwickelt für Vereine, die ihre Verwaltung modernisieren wollen.

Inspiriert von Nextcloud und der Community! 

**Powered by**: [Nextcloud](https://nextcloud.com) • [Vue.js](https://vuejs.org) • [Vite](https://vitejs.dev)

---

**Bereit zum Starten?** → [Installation Guide](./INSTALLATION.md)
