## Entwicklungsstatus: v0.2.0-beta

Datum: 19. November 2025

Kurze Zusammenfassung:

- **Gesamtfortschritt (geschätzt): 60%**

Aufgeschlüsselt nach Bereichen (Gewichtung in Klammern):

- **Frontend (Build & Bundle) — 80% (30%)**: Vite-Build erzeugt `nextcloud-verein.mjs` und `style.css`. Chart.js-Änderungen wurden experimentell vorgenommen (feature-branch). Noch offen: saubere Auslieferung/Kompatibilität des Bundles (`.mjs` vs `.js`) und Stabilisierung des Chart-Fixes.
- **Backend (Controller / Services / Middleware) — 70% (30%)**: Viele Server-Dateien aus `v0.2.0-beta` wurden inkrementell in die installierte App übernommen (Validatoren, Middleware, Controller). Syntax-Checks erfolgreich. Integrationstests fehlen noch.
- **CSP / Nonce-Injektion / Integration — 30% (20%)**: `PageController` setzt eine `ContentSecurityPolicy`, jedoch zeigen Middleware-Logs häufig `EmptyContentSecurityPolicy` für App-Requests, wodurch CSP-Nonce nicht immer injiziert wird. Ursache noch unklar und priorisiert.
- **Tests & QA — 20% (10%)**: Viele manuelle Lint- und Smoke-Checks, aber keine automatisierten Tests oder vollständige Browser-Smoketests mit authentifiziertem Benutzer.
- **Dokumentation & Packaging — 60% (10%)**: Release-Notizen und Handbücher sind vorhanden; Release-artefakte und finaler Packaging-Test stehen noch aus.

Wichtigste offene Punkte / Risiken:

- CSP/Nonce-Injektion: Finden, warum in vielen Requests `EmptyContentSecurityPolicy` verwendet wird.
- Ressourcen-Namens-Mismatch: Build erzeugt `.mjs`, Templates referenzieren `dist/nextcloud-verein` (Nextcloud sucht häufig `.js`).
- Chart.js-Regressionsursache: globale Registrierung der `Filler`-Plugin zeigte Regression; sichere lokale Registrierung prüfen und testen.

Empfohlene nächste Schritte:

1. Kurzfristig: Temporäre Spiegelung von `nextcloud-verein.mjs` → `nextcloud-verein.js` oder Anpassung der Template-Resource-Definition, um Ressourcen-Ladefehler zu vermeiden.
2. Authentifizierten Browser-Smoketest durchführen, um zu prüfen, ob bei angemeldeten Responses der `script-src`-Nonce gesetzt wird.
3. Repository-diff zwischen `v0.1.0-alpha` und `v0.2.0-beta` auf CSP-/Response-Änderungen überprüfen, um die Ursache für `EmptyContentSecurityPolicy` zu finden.
4. Chart-Fix weiter im `feature/fix-chart`-Branch ausarbeiten und nicht direkt auf `develop` mergen, bis stabil.

Kontakt / Verantwortlich: Automatisch erstellt durch das lokale Assistenzskript (Pair-Programming). Falls du möchtest, kann ich die nächsten Schritte automatisiert ausführen (Login-Test, Diff, oder PR-Vorbereitung).
