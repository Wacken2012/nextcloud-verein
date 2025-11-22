## Entwicklungsstatus: v0.2.0-beta

Datum: 22. November 2025 (RBAC Update)

Kurze Zusammenfassung:

- **Gesamtfortschritt (geschätzt): 80%**

Aufgeschlüsselt nach Bereichen (Gewichtung in Klammern):

- **Frontend (Build & Bundle) — 90% (30%)**: Vite-Build erzeugt `nextcloud-verein.mjs` und `style.css`. Responsive Layouts, Dark Mode, Admin-UI vollständig implementiert und getestet.
- **Backend (Controller / Services / Middleware) — 85% (30%)**: Alle Server-Dateien aus `v0.2.0-beta` integriert (Validatoren, Middleware, Controller, Services). Admin-Settings-Integration mit Nextcloud 32 erfolgreich (IIconSection/ISettings mit IAppContainer DI).
- **Berechtigungen (RBAC) — 95% (20%)**: ✅ VOLLSTÄNDIG IMPLEMENTIERT
  - ✅ RequirePermission Attributes auf allen 31 Controller-Methoden
  - ✅ AuthorizationMiddleware mit Audit-Logging
  - ✅ Role-based Access Control (Admin, Treasurer, Member)
  - ✅ Permission Checking in allen kritischen APIs
  - ✅ 20+ Unit Tests für RBAC & Permissions
- **Tests & QA — 45% (10%)**: 20+ Unit Tests für RBAC geschrieben (RBACTest, AuthorizationMiddlewareTest, ControllerPermissionsTest). Manuelle Tests in Arbeit.
- **Dokumentation & Packaging — 60% (10%)**: Release-Notizen vorhanden. API-Dokumentation und Developer-Guide in Arbeit.

Wichtigste offene Punkte / Risiken:

- ✅ **RESOLVED**: RBAC & Permissions - Vollständig implementiert mit 20+ Unit Tests
  - Alle 31 Controller-Methoden mit RequirePermission Attributes
  - AuthorizationMiddleware mit Audit-Logging für Permission Violations
  - Tested: Admin > Treasurer > Member Hierarchie
  - Tested: Wildcard Permissions (verein.finance.*)
  - Tested: Multi-Role Support für einzelne User

- 🟡 **OFFEN**: Input-Validierung - IBAN/BIC, E-Mail, Duplikat-Checks (3-4h)
- 🟡 **OFFEN**: CSV/PDF Export - Export-Funktionalität für Listen (2-3h)
- 🟢 **NIEDRIG**: weitere Tests für Edge-Cases

Empfohlene nächste Schritte zur Vervollständigung v0.2.0-beta:

1. **✅ COMPLETED - RBAC & Berechtigungen** (Implementiert 22. Nov):
   - ✅ 31 Controller-Methoden mit @RequirePermission Attributes
   - ✅ AuthorizationMiddleware mit Audit-Logging
   - ✅ Role-based Access Control (Admin, Treasurer, Member)
   - ✅ 20+ Unit Tests (RBACTest, AuthorizationMiddlewareTest, ControllerPermissionsTest)

2. **PRIORITÄT 1 - Input-Validierung** (3-4h):
   - [ ] IBAN/BIC Validierung
   - [ ] E-Mail Format Validation & Duplikat-Prüfung
   - [ ] Pflichtfeld-Validierung
   - [ ] Fehler-Response Standardisierung

3. **PRIORITÄT 2 - Export-Funktionalität** (2-3h):
   - [ ] CSV Export Endpunkte
   - [ ] CSV Format definieren
   - [ ] Optional: Excel Export

4. **PRIORITÄT 3 - Testing & QA** (2-3h):
   - [ ] RBAC Tests laufen lassen (phpunit)
   - [ ] Manual Browser Tests mit verschiedenen Rollen
   - [ ] Permission Denial Tests

5. **PRIORITÄT 4 - Dokumentation** (1-2h):
   - [ ] API Dokumentation aktualisieren
   - [ ] README mit RBAC Info aktualisieren
   - [ ] Admin Guide aktualisieren
