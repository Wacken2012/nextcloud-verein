# 🧪 Aktionsplan: Tests & Verifikation v0.2.0

**Status:** Ready to test all 6 features  
**Ziel:** Alle Features überprüfen und testen

---

## 1️⃣ FEATURE 1: Mitgliederverwaltung - TESTING

### API-Endpoints zum Testen:
```bash
# 1. Alle Mitglieder abrufen
curl -u ncuser:password \
  http://localhost/nextcloud/apps/verein/api/v1/members

# 2. Neues Mitglied erstellen
curl -u ncuser:password -X POST \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Max Mustermann",
    "email": "max@example.com",
    "iban": "DE89370400440532013000",
    "bic": "COBADEFF",
    "address": "Teststraße 123"
  }' \
  http://localhost/nextcloud/apps/verein/api/v1/members

# 3. Mitglied bearbeiten (mit ID)
curl -u ncuser:password -X PUT \
  -H "Content-Type: application/json" \
  -d '{"name": "Updated Name"}' \
  http://localhost/nextcloud/apps/verein/api/v1/members/1

# 4. Mitglied löschen
curl -u ncuser:password -X DELETE \
  http://localhost/nextcloud/apps/verein/api/v1/members/1
```

### UI-Test (Dashboard):
- Öffne: http://localhost/nextcloud/apps/verein/#/members
- Verifiziere:
  - ✅ Mitgliederliste wird angezeigt
  - ✅ "Neues Mitglied" Button funktioniert
  - ✅ Formular öffnet sich
  - ✅ Mitglied kann gespeichert werden
  - ✅ Suchfilter funktioniert
  - ✅ Rollen-Filter funktioniert

---

## 2️⃣ FEATURE 3: Beitragsabrechnung - TESTING

### API-Endpoints zum Testen:
```bash
# 1. Alle Gebühren abrufen
curl -u ncuser:password \
  http://localhost/nextcloud/apps/verein/api/v1/fees

# 2. Neue Gebühr erstellen
curl -u ncuser:password -X POST \
  -H "Content-Type: application/json" \
  -d '{
    "member_id": 1,
    "amount": 25.00,
    "due_date": "2025-12-31",
    "status": "open"
  }' \
  http://localhost/nextcloud/apps/verein/api/v1/fees

# 3. Gebühr aktualisieren
curl -u ncuser:password -X PUT \
  -H "Content-Type: application/json" \
  -d '{"status": "paid", "payment_method": "bank_transfer"}' \
  http://localhost/nextcloud/apps/verein/api/v1/fees/1

# 4. CSV-Export (wenn möglich)
curl -u ncuser:password \
  'http://localhost/nextcloud/apps/verein/api/v1/export/fees/csv?status=open' \
  > fees.csv
```

### UI-Test (Dashboard):
- Öffne: http://localhost/nextcloud/apps/verein/#/fees
- Verifiziere:
  - ✅ Gebührenliste wird angezeigt
  - ✅ Status-Filter funktioniert (open, paid, overdue)
  - ✅ "Neue Gebühr" Button funktioniert
  - ✅ Inline-Bearbeitung des Status funktioniert
  - ✅ CSV-Export Button vorhanden

---

## 3️⃣ FEATURE 4: SEPA-Export - TESTING

### API-Endpoints zum Testen:
```bash
# 1. SEPA XML generieren
curl -u ncuser:password -X POST \
  -H "Content-Type: application/json" \
  -d '{
    "creditorId": "DE89ZZZ999999999",
    "creditorName": "Mein Verein",
    "executionDate": "2025-12-01",
    "memberIds": [1, 2, 3],
    "format": "pain001"
  }' \
  http://localhost/nextcloud/apps/verein/api/v1/export/sepa

# 2. SEPA Datei herunterladen
curl -u ncuser:password \
  http://localhost/nextcloud/apps/verein/api/v1/export/sepa/download \
  > payment.xml
```

### UI-Test (Dashboard):
- Öffne: http://localhost/nextcloud/apps/verein/#/sepa
- Oder: Export Dialog → SEPA Tab
- Verifiziere:
  - ✅ SEPA-Export Dialog öffnet sich
  - ✅ Mitglied-Filter funktioniert
  - ✅ Status-Filter funktioniert
  - ✅ "Download" Button funktioniert
  - ✅ XML-Datei wird heruntergeladen

---

## 4️⃣ FEATURE 5a: RBAC - TESTING

### Admin-Panel Test:
- Öffne: http://localhost/nextcloud/index.php/settings/admin/verein
- Verifiziere:
  - ✅ Admin-Panel öffnet sich
  - ✅ "Rollen verwalten" Sektion sichtbar
  - ✅ Tabelle mit bestehenden Rollen
  - ✅ Admin, Treasurer, Member Rollen vorhanden
  - ✅ "Neue Rolle" Button vorhanden (aber Modal noch implementieren)

### API-Test (Rollen abrufen):
```bash
# Alle Rollen
curl -u ncuser:password \
  http://localhost/nextcloud/apps/verein/api/v1/roles

# Rollen für Musikverein
curl -u ncuser:password \
  'http://localhost/nextcloud/apps/verein/api/v1/roles/club/music'

# Einzelne Rolle
curl -u ncuser:password \
  http://localhost/nextcloud/apps/verein/api/v1/roles/1

# Neue Rolle erstellen
curl -u ncuser:password -X POST \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Trainer",
    "club_type": "sports",
    "description": "Trainiert die Mannschaft",
    "permissions": ["verein.member.manage", "verein.finance.read"]
  }' \
  http://localhost/nextcloud/apps/verein/api/v1/roles
```

---

## 5️⃣ FEATURE 5b: Dashboard-Widget - TESTING

### UI-Test:
- Öffne: http://localhost/nextcloud/apps/verein/
- Verifiziere:
  - ✅ Dashboard lädt sich
  - ✅ Statistik-Widget sichtbar
  - ✅ Mitgliederzahl wird angezeigt
  - ✅ Gebührenübersicht wird angezeigt
  - ✅ Charts/Graphen werden gerendert
  - ✅ Responsive auf Mobile

---

## 6️⃣ FEATURE 5d: API-Authentifizierung - TESTING

### Basic Auth Test:
```bash
# Mit richtigen Credentials - sollte 200 OK sein
curl -v -u ncuser:password \
  http://localhost/nextcloud/apps/verein/api/v1/roles

# Mit falschen Credentials - sollte 401 Unauthorized sein
curl -v -u wrong:credentials \
  http://localhost/nextcloud/apps/verein/api/v1/roles

# Ohne Auth - sollte 401 sein
curl -v http://localhost/nextcloud/apps/verein/api/v1/roles
```

### Permission Test:
```bash
# Test ob RequirePermission funktioniert
# (benötig Admin oder verein.role.manage Permission)

curl -u normaluser:password -X POST \
  -H "Content-Type: application/json" \
  -d '{"name": "Test"}' \
  http://localhost/nextcloud/apps/verein/api/v1/roles

# Sollte 403 Forbidden sein wenn User keine Berechtigung hat
```

---

## 📋 TESTING CHECKLISTE

### Mitgliederverwaltung (Feature 1)
- [ ] API GET /members funktioniert
- [ ] API POST /members funktioniert
- [ ] API PUT /members/{id} funktioniert
- [ ] API DELETE /members/{id} funktioniert
- [ ] UI: Mitgliederliste angezeigt
- [ ] UI: Neues Mitglied hinzufügbar
- [ ] UI: Mitglied bearbeitbar
- [ ] UI: Mitglied löschbar
- [ ] UI: Filter funktionieren

### Beitragsabrechnung (Feature 3)
- [ ] API GET /fees funktioniert
- [ ] API POST /fees funktioniert
- [ ] API PUT /fees/{id} funktioniert
- [ ] API DELETE /fees/{id} funktioniert
- [ ] API CSV-Export funktioniert
- [ ] UI: Gebührenliste angezeigt
- [ ] UI: Neue Gebühr hinzufügbar
- [ ] UI: Status bearbeitbar
- [ ] UI: Filter funktionieren

### SEPA-Export (Feature 4)
- [ ] API POST /export/sepa funktioniert
- [ ] API GET /export/sepa/download funktioniert
- [ ] XML wird generiert
- [ ] XML ist valide ISO 20022
- [ ] UI: SEPA Dialog öffnet sich
- [ ] UI: Download funktioniert
- [ ] UI: Preview angezeigt

### RBAC (Feature 5a)
- [ ] Admin-Panel öffnet sich
- [ ] Rollen werden angezeigt
- [ ] API GET /roles funktioniert
- [ ] API POST /roles funktioniert
- [ ] Permissions werden geprüft
- [ ] Unauthorized (403) bei fehlenden Rechten

### Dashboard (Feature 5b)
- [ ] Dashboard lädt sich
- [ ] Statistiken werden angezeigt
- [ ] Charts rendern korrekt
- [ ] Responsive auf Mobile
- [ ] Real-time Updates funktionieren

### API-Authentifizierung (Feature 5d)
- [ ] Basic Auth funktioniert
- [ ] Falsche Credentials → 401
- [ ] Fehlende Auth → 401
- [ ] RequirePermission funktioniert
- [ ] Authorized → 200/201/204
- [ ] Unauthorized → 403

---

## 🚀 Nächste Schritte

Nachdem alle Tests bestanden:
1. Integration-Tests schreiben für Controller
2. E2E-Tests für Vue-Komponenten
3. Performance-Tests (Load testing)
4. Security-Tests (CSRF, SQL Injection, etc.)
5. Dokumentation aktualisieren
6. Release v0.2.0-beta vorbereiten
