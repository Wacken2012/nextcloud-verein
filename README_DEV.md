// 🚀 Nextcloud Vereins-App – Projektstart
// Ziel: Native Nextcloud-App für Vereinsverwaltung (Mitglieder, Beiträge, SEPA, Notenarchiv)

// ✅ Projektstruktur
// nextcloud-app-verein/
// ├── appinfo/        # App-Metadaten (info.xml, routes.php)
// ├── lib/            # Controller, Service, Db-Modelle
// ├── templates/      # Vue/HTML Templates
// ├── js/             # Vue-Komponenten
// ├── tests/          # PHPUnit-Tests

// ✅ Roadmap
// Phase 1: Setup (1 Woche)
// - Nextcloud Dev-Umgebung (Docker/VM) installieren
// - App-Boilerplate klonen
// - VS Code mit Extensions (PHP Intelephense, Vue, Copilot) einrichten
// - GitHub-Repo + Actions für Tests

// Phase 2: Core-Modul Mitgliederverwaltung (2–3 Wochen)
// - Tabelle members (id, name, address, email, iban, bic, role)
// - MembersController mit CRUD-Endpunkten
// - Vue-Komponenten: Mitgliederliste, Formular
// - Rollenverwaltung: Vorstand, Kassierer, Mitglied

// Phase 3: Beitragsabrechnung (2 Wochen)
// - Tabelle fees (id, member_id, amount, status, due_date)
// - Status-Logik (offen, bezahlt, überfällig)
// - CSV-Export

// Phase 4: SEPA-Export (2 Wochen)
// - Integration php-sepa-xml
// - Generierung SEPA-XML für offene Beiträge
// - Download-Funktion

// Phase 5: Zusatzmodule (4–6 Wochen)
// - Notenarchiv (Nextcloud-Files API)
// - Kalender-Integration (Nextcloud-Calendar API)
// - Chat-Integration (Talk API)

// Phase 6: Release (1–2 Wochen)
// - App für Nextcloud-App-Store vorbereiten
// - Screenshots, Beschreibung, Dokumentation
// - Optional: Raspberry-Pi-Image mit Nextcloud + App

// ✅ Copilot-Tipps
// - Schreibe präzise Kommentare wie:
//   // Create Nextcloud controller for managing members
//   → Copilot generiert Controller-Grundgerüst
// - Nutze Commit-Messages als Kontext für Copilot
// - Verwende Snippets für CRUD-Operationen und Tests

