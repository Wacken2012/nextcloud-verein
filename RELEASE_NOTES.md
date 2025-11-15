# �� Release Notes – v0.1.0-alpha

**Release Date**: November 15, 2025  
**Version**: v0.1.0-alpha  
**Status**: ✅ Alpha - Production Ready for Testing

---

## 🎉 Welcome to Nextcloud Vereins-App!

The first alpha release of **Nextcloud Vereins-App** is here! This is a complete club/association management system built natively for Nextcloud with Vue 3 and a modern API.

**Thank you for trying the first version!** Your feedback is crucial for making this app better. 🙏

---

## ✨ What's Included

### 👥 Member Management
- ✅ Full CRUD operations (Create, Read, Update, Delete)
- ✅ Member profiles with name, email, address, IBAN, BIC, role
- ✅ Responsive member table with inline editing
- ✅ Role-based data storage (Mitglied, Kassierer, Admin)

### 💰 Finance Management
- ✅ Fee tracking and management
- ✅ Status tracking: open, paid, overdue
- ✅ Statistics dashboard showing total outstanding, paid amounts
- ✅ Quick member selection and fee creation

### 🎨 User Interface
- ✅ Vue 3 + Vite modern frontend
- ✅ Dark mode support
- ✅ Responsive design (desktop, tablet, mobile)
- ✅ Nextcloud design integration
- ✅ Tab-based navigation

### 📚 Documentation
- ✅ Comprehensive README with features and quickstart
- ✅ Detailed INSTALLATION guide with troubleshooting
- ✅ Public ROADMAP for v0.1 through v1.0
- ✅ AGPL-3.0 License included
- ✅ Ready to contribute: Contributing guidelines

---

## �� Quick Start

### Installation (2 minutes)

```bash
cd /var/www/nextcloud/apps/
git clone https://github.com/Wacken2012/nextcloud-verein.git verein
cd verein
npm install && npm run build
sudo -u www-data php /var/www/nextcloud/occ app:enable verein
```

**Then**: Open Nextcloud → Apps → Verein → Add first members!

For detailed instructions, see [INSTALLATION.md](./INSTALLATION.md)

---

## 📊 Current Features

| Feature | Status | Notes |
|---------|--------|-------|
| Members CRUD | ✅ Complete | Full create, read, update, delete |
| Finance CRUD | ✅ Complete | Manage fees and billing |
| Statistics | ✅ Complete | Dashboard with key metrics |
| Dark Mode | ✅ Complete | Follows Nextcloud theme |
| Responsive UI | ✅ Complete | Works on all screen sizes |
| User Authentication | ✅ Complete | Nextcloud-native auth |
| API Endpoints | ✅ Complete | RESTful CRUD API |

---

## 🐛 Known Limitations

This is an **alpha release**. Be aware of:

### Security
- ❌ No role-based permissions yet (all users = Admin)
- ❌ No input validation on IBAN/BIC
- ⚠️ Use only in development/testing environments

### Features
- ❌ No export functionality (CSV/PDF)
- ❌ No automatic reminders
- ❌ No calendar/deck/document integrations yet (tabs are placeholders)
- ❌ No email notifications

### Quality
- ❌ No unit tests (0% coverage - coming in v0.2.0)
- ⚠️ Performance not optimized
- ⚠️ Bundle size: 387 KB (should be < 200 KB)

---

## 🗺️ What's Coming Next

### v0.2.0 (Q1 2026) - Beta Release
- 🔒 Rollen & Berechtigungen (Admin, Kassierer, Mitglied)
- 📊 CSV/PDF Export
- ✅ Input Validierung (IBAN, E-Mail, etc.)
- 📈 Erweiterte Statistiken & Charts
- 🧪 Unit Tests & Error Handling

### v0.3.0 (Q2 2026)
- 📧 Automatische Mahnungen
- 📅 Kalender Integration
- 🔔 Notification System
- 💾 SEPA XML Export

### v1.0.0 (Q4 2026) - Production Release
- ✅ 100% Test Coverage
- 🎯 Nextcloud App Store Release
- 📚 Complete Documentation
- 🌍 Internationalization (i18n)

**Full roadmap**: See [ROADMAP.md](./ROADMAP.md)

---

## 📞 Feedback & Support

We want to hear from you!

- **Found a bug?** → [GitHub Issues](https://github.com/Wacken2012/nextcloud-verein/issues)
- **Have an idea?** → [GitHub Discussions](https://github.com/Wacken2012/nextcloud-verein/discussions)
- **Want to contribute?** → [Contributing Guide](./CONTRIBUTING.md)

---

## 🛠️ For Developers

### Build from Source

```bash
# Clone & install
git clone https://github.com/Wacken2012/nextcloud-verein.git
cd nextcloud-verein
npm install

# Development (watch mode)
npm run dev

# Production build
npm run build
```

### Tech Stack
- **Frontend**: Vue 3, Vite, Sass
- **Backend**: PHP 8.1+, Nextcloud AppFramework
- **Database**: MySQL/MariaDB/PostgreSQL
- **API**: RESTful with JSON

### Project Structure
```
nextcloud-verein/
├── appinfo/          # App metadata & routes
├── lib/              # PHP Controllers & Services
├── js/               # Vue components
├── templates/        # PHP templates
├── tests/            # Unit tests
└── docs/             # Documentation
```

---

## 📋 Installation Requirements

- **Nextcloud**: 28.0+
- **PHP**: 8.1+
- **Database**: MySQL 5.7+, MariaDB 10.2+, or PostgreSQL 9.0+
- **Disk**: ~50 MB + data space

---

## 📈 Metrics

| Metric | Value |
|--------|-------|
| Total Files | 62 |
| Lines of Code (PHP) | ~2,000 |
| Lines of Code (Vue) | ~1,500 |
| Lines of Code (CSS) | ~300 |
| Bundle Size | 387 KB (gzipped: 91 KB) |
| Test Coverage | 0% (coming in v0.2.0) |

---

## 🙏 Credits

**Created by**: Stefan  
**License**: AGPL-3.0 (see [LICENSE](./LICENSE))

**Powered by**:
- [Nextcloud](https://nextcloud.com) – Self-hosted cloud platform
- [Vue.js](https://vuejs.org) – Progressive JavaScript framework
- [Vite](https://vitejs.dev) – Next-gen frontend build tool

---

## 📝 License

This project is licensed under **AGPL-3.0**. 

⚠️ **Important**: This means any modifications must be released under the same license. See [LICENSE](./LICENSE) for details.

---

## 🎯 Version History

- **v0.1.0-alpha** (Nov 15, 2025) – Initial alpha release with Members & Finance
- v0.2.0-beta (Q1 2026) – Planned
- v0.3.0 (Q2 2026) – Planned
- v1.0.0 (Q4 2026) – Planned

---

## ✅ What to Test

Help us improve! Please test:

1. ✅ Adding new members with all fields
2. ✅ Editing member data
3. ✅ Deleting members
4. ✅ Adding fees
5. ✅ Viewing statistics
6. ✅ Dark mode switching
7. ✅ Mobile/tablet responsiveness
8. ✅ Navigation between tabs

**Report any issues** at: [GitHub Issues](https://github.com/Wacken2012/nextcloud-verein/issues)

---

## 🚀 Ready to Get Started?

1. **Install**: [INSTALLATION.md](./INSTALLATION.md)
2. **Learn**: [README.md](./README.md)
3. **Plan**: [ROADMAP.md](./ROADMAP.md)
4. **Contribute**: [CONTRIBUTING.md](./CONTRIBUTING.md) (coming soon)

---

**Thank you for your interest in Nextcloud Vereins-App!** 🎉

Together, we're building the best club management system for Nextcloud. Let's make it amazing! 🚀
