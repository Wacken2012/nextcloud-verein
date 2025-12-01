# 📚 API Documentation – Nextcloud Vereins-App

**Version:** v0.2.1 | **Stand/Date:** 30. November 2025

**[🇩🇪 Deutsch](#deutsch)** | **[🇬🇧 English](#english)**

---

<a name="deutsch"></a>
# 🇩🇪 API Dokumentation (Deutsch)

## 🔐 Authentifizierung

Alle API-Endpunkte erfordern eine gültige Nextcloud-Session. Es gibt zwei Möglichkeiten:

### 1. Session-basiert (Browser)
Wenn du im Browser bei Nextcloud eingeloggt bist, wird die Session automatisch verwendet.

### 2. Basic Auth (API-Clients)
```bash
curl -u "username:app-password" https://your-nextcloud.com/index.php/apps/verein/members
```

> **Tipp**: Erstelle ein App-Passwort unter Nextcloud → Einstellungen → Sicherheit → Geräte & Sitzungen

---

## 🎭 Berechtigungen (RBAC)

Die App verwendet rollenbasierte Berechtigungen:

| Rolle | Berechtigungen |
|-------|----------------|
| **Admin** | Volle Kontrolle über alle Funktionen |
| **Kassierer** | Gebühren verwalten, Export |
| **Mitglied** | Nur eigene Daten einsehen |

### Verfügbare Permissions
- `members.view`, `members.create`, `members.update`, `members.delete`
- `fees.view`, `fees.create`, `fees.update`, `fees.delete`
- `export.members`, `export.fees`, `export.sepa`
- `roles.manage`

---

## 📋 Endpunkte

### Basis-URL
```
/index.php/apps/verein
```

---

## 👥 Members (Mitglieder)

### GET /members
Liste aller Mitglieder abrufen.

**Response:**
```json
[
  {
    "id": 1,
    "name": "Max Mustermann",
    "email": "max@example.com",
    "address": "Musterstraße 123, 12345 Berlin",
    "iban": "DE89370400440532013000",
    "bic": "COBADEFFXXX",
    "role": "member"
  }
]
```

---

### POST /members
Neues Mitglied erstellen.

**Request Body:**
```json
{
  "name": "Max Mustermann",
  "email": "max@example.com",
  "address": "Musterstraße 123",
  "iban": "DE89370400440532013000",
  "bic": "COBADEFFXXX",
  "role": "member"
}
```

**Validierung:**
- `name`: Pflichtfeld, 2-255 Zeichen
- `email`: Pflichtfeld, RFC 5322 Format, MX-Check
- `iban`: Optional, ISO 13616 mit Prüfsumme
- `bic`: Optional, ISO 9362 Format (8 oder 11 Zeichen)

**Response:** `201 Created`
```json
{
  "id": 17,
  "name": "Max Mustermann",
  ...
}
```

**Fehler:** `400 Bad Request`
```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "email": ["Ungültiges E-Mail-Format"],
    "iban": ["Ungültige IBAN-Prüfsumme"]
  }
}
```

---

### GET /members/{id}
Einzelnes Mitglied abrufen.

**Parameter:**
- `id` (path): Mitglieds-ID

**Response:** `200 OK` oder `404 Not Found`

---

### PUT /members/{id}
Mitglied aktualisieren.

**Parameter:**
- `id` (path): Mitglieds-ID

**Request Body:** Wie bei POST

---

### DELETE /members/{id}
Mitglied löschen.

**Response:** `200 OK`
```json
{
  "status": "success"
}
```

---

## 💰 Finance (Gebühren)

### GET /finance
Liste aller Gebühren abrufen.

**Response:**
```json
[
  {
    "id": 1,
    "memberId": 5,
    "amount": 50.00,
    "status": "paid",
    "dueDate": "2025-12-31",
    "paidDate": "2025-11-15",
    "description": "Mitgliedsbeitrag 2025"
  }
]
```

---

### POST /finance
Neue Gebühr erstellen.

**Request Body:**
```json
{
  "memberId": 5,
  "amount": 50.00,
  "dueDate": "2025-12-31",
  "description": "Mitgliedsbeitrag 2025"
}
```

**Felder:**
- `memberId`: Pflichtfeld (Integer)
- `amount`: Pflichtfeld (Float, min. 0.01)
- `dueDate`: Pflichtfeld (YYYY-MM-DD)
- `status`: Optional, default "open" (open|paid|overdue)
- `description`: Optional

---

### PUT /finance/{id}
Gebühr aktualisieren.

---

### DELETE /finance/{id}
Gebühr löschen.

---

## 📊 Statistics

### GET /statistics/members
Mitgliederstatistiken abrufen.

**Response:**
```json
{
  "total": 16,
  "byRole": {
    "member": 14,
    "admin": 1,
    "treasurer": 1
  },
  "growth": [
    { "month": "2025-06", "count": 2 },
    { "month": "2025-07", "count": 1 }
  ]
}
```

---

### GET /statistics/fees
Gebührenstatistiken abrufen.

**Response:**
```json
{
  "total": 645.00,
  "byStatus": {
    "open": 0.00,
    "paid": 645.00,
    "overdue": 0.00
  },
  "count": {
    "total": 12,
    "open": 0,
    "paid": 12,
    "overdue": 0
  }
}
```

---

## 📤 Export

### GET /export/members/csv
Mitglieder als CSV exportieren.

**Response:** CSV-Datei (UTF-8 mit BOM)
- Content-Type: `text/csv`
- Content-Disposition: `attachment; filename="members_2025-11-30_181500.csv"`

---

### GET /export/members/pdf
Mitglieder als PDF exportieren.

**Response:** PDF-Datei
- Content-Type: `application/pdf`

---

### GET /export/fees/csv
Gebühren als CSV exportieren.

---

### GET /export/fees/pdf
Gebühren als PDF exportieren.

---

### GET /sepa/export
SEPA XML Export für offene Gebühren.

**Response:** XML-Datei (pain.001)
- Content-Type: `application/xml`

---

## 🎭 Roles (RBAC)

### GET /roles
Liste aller Rollen.

**Response:**
```json
[
  {
    "id": 1,
    "name": "admin",
    "displayName": "Administrator",
    "permissions": ["members.view", "members.create", "..."]
  }
]
```

---

### POST /roles
Neue Rolle erstellen (nur Admin).

---

### GET /roles/users/{userId}
Rollen eines Benutzers abrufen.

---

### POST /roles/users
Rolle einem Benutzer zuweisen.

**Request Body:**
```json
{
  "userId": "alice",
  "roleId": 2
}
```

---

### DELETE /roles/users
Rolle von Benutzer entfernen.

---

### GET /permissions
Liste aller verfügbaren Berechtigungen.

---

## ❌ Fehlercodes

| Code | Bedeutung |
|------|-----------|
| `400` | Validierungsfehler (ungültige Daten) |
| `401` | Nicht authentifiziert |
| `403` | Keine Berechtigung |
| `404` | Ressource nicht gefunden |
| `500` | Interner Serverfehler |

### Fehler-Response Format
```json
{
  "status": "error",
  "message": "Beschreibung des Fehlers",
  "errors": {
    "field": ["Validierungsfehler 1", "Validierungsfehler 2"]
  }
}
```

---

## 🔧 cURL Beispiele

### Mitglieder abrufen
```bash
curl -u "admin:app-password" \
  https://your-nextcloud.com/index.php/apps/verein/members
```

### Neues Mitglied erstellen
```bash
curl -u "admin:app-password" \
  -X POST \
  -H "Content-Type: application/json" \
  -d '{"name":"Max Mustermann","email":"max@example.com"}' \
  https://your-nextcloud.com/index.php/apps/verein/members
```

### PDF Export
```bash
curl -u "admin:app-password" \
  -o members.pdf \
  https://your-nextcloud.com/index.php/apps/verein/export/members/pdf
```

---

## 📖 OpenAPI Spezifikation

Die vollständige OpenAPI 3.0 Spezifikation findest du unter:
- [`docs/api/openapi.yaml`](openapi.yaml)

Du kannst sie in Tools wie [Swagger UI](https://swagger.io/tools/swagger-ui/) oder [Postman](https://www.postman.com/) importieren.

---
---

<a name="english"></a>
# 🇬🇧 API Documentation (English)

## 🔐 Authentication

All API endpoints require a valid Nextcloud session. There are two methods:

### 1. Session-based (Browser)
When logged into Nextcloud in your browser, the session is used automatically.

### 2. Basic Auth (API Clients)
```bash
curl -u "username:app-password" https://your-nextcloud.com/index.php/apps/verein/members
```

> **Tip**: Create an app password under Nextcloud → Settings → Security → Devices & Sessions

---

## 🎭 Permissions (RBAC)

The app uses role-based access control:

| Role | Permissions |
|------|-------------|
| **Admin** | Full control over all functions |
| **Treasurer** | Manage fees, export |
| **Member** | View own data only |

### Available Permissions
- `members.view`, `members.create`, `members.update`, `members.delete`
- `fees.view`, `fees.create`, `fees.update`, `fees.delete`
- `export.members`, `export.fees`, `export.sepa`
- `roles.manage`

---

## 📋 Endpoints

### Base URL
```
/index.php/apps/verein
```

---

## 👥 Members

### GET /members
Retrieve list of all members.

**Response:**
```json
[
  {
    "id": 1,
    "name": "Max Mustermann",
    "email": "max@example.com",
    "address": "Musterstraße 123, 12345 Berlin",
    "iban": "DE89370400440532013000",
    "bic": "COBADEFFXXX",
    "role": "member"
  }
]
```

---

### POST /members
Create a new member.

**Request Body:**
```json
{
  "name": "Max Mustermann",
  "email": "max@example.com",
  "address": "Musterstraße 123",
  "iban": "DE89370400440532013000",
  "bic": "COBADEFFXXX",
  "role": "member"
}
```

**Validation:**
- `name`: Required, 2-255 characters
- `email`: Required, RFC 5322 format, MX check
- `iban`: Optional, ISO 13616 with checksum
- `bic`: Optional, ISO 9362 format (8 or 11 characters)

**Response:** `201 Created`
```json
{
  "id": 17,
  "name": "Max Mustermann",
  ...
}
```

**Error:** `400 Bad Request`
```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "email": ["Invalid email format"],
    "iban": ["Invalid IBAN checksum"]
  }
}
```

---

### GET /members/{id}
Retrieve a single member.

**Parameters:**
- `id` (path): Member ID

**Response:** `200 OK` or `404 Not Found`

---

### PUT /members/{id}
Update a member.

**Parameters:**
- `id` (path): Member ID

**Request Body:** Same as POST

---

### DELETE /members/{id}
Delete a member.

**Response:** `200 OK`
```json
{
  "status": "success"
}
```

---

## 💰 Finance (Fees)

### GET /finance
Retrieve list of all fees.

**Response:**
```json
[
  {
    "id": 1,
    "memberId": 5,
    "amount": 50.00,
    "status": "paid",
    "dueDate": "2025-12-31",
    "paidDate": "2025-11-15",
    "description": "Membership fee 2025"
  }
]
```

---

### POST /finance
Create a new fee.

**Request Body:**
```json
{
  "memberId": 5,
  "amount": 50.00,
  "dueDate": "2025-12-31",
  "description": "Membership fee 2025"
}
```

**Fields:**
- `memberId`: Required (Integer)
- `amount`: Required (Float, min. 0.01)
- `dueDate`: Required (YYYY-MM-DD)
- `status`: Optional, default "open" (open|paid|overdue)
- `description`: Optional

---

### PUT /finance/{id}
Update a fee.

---

### DELETE /finance/{id}
Delete a fee.

---

## 📊 Statistics

### GET /statistics/members
Retrieve member statistics.

**Response:**
```json
{
  "total": 16,
  "byRole": {
    "member": 14,
    "admin": 1,
    "treasurer": 1
  },
  "growth": [
    { "month": "2025-06", "count": 2 },
    { "month": "2025-07", "count": 1 }
  ]
}
```

---

### GET /statistics/fees
Retrieve fee statistics.

**Response:**
```json
{
  "total": 645.00,
  "byStatus": {
    "open": 0.00,
    "paid": 645.00,
    "overdue": 0.00
  },
  "count": {
    "total": 12,
    "open": 0,
    "paid": 12,
    "overdue": 0
  }
}
```

---

## 📤 Export

### GET /export/members/csv
Export members as CSV.

**Response:** CSV file (UTF-8 with BOM)
- Content-Type: `text/csv`
- Content-Disposition: `attachment; filename="members_2025-11-30_181500.csv"`

---

### GET /export/members/pdf
Export members as PDF.

**Response:** PDF file
- Content-Type: `application/pdf`

---

### GET /export/fees/csv
Export fees as CSV.

---

### GET /export/fees/pdf
Export fees as PDF.

---

### GET /sepa/export
SEPA XML export for open fees.

**Response:** XML file (pain.001)
- Content-Type: `application/xml`

---

## 🎭 Roles (RBAC)

### GET /roles
List all roles.

**Response:**
```json
[
  {
    "id": 1,
    "name": "admin",
    "displayName": "Administrator",
    "permissions": ["members.view", "members.create", "..."]
  }
]
```

---

### POST /roles
Create a new role (admin only).

---

### GET /roles/users/{userId}
Get roles for a user.

---

### POST /roles/users
Assign a role to a user.

**Request Body:**
```json
{
  "userId": "alice",
  "roleId": 2
}
```

---

### DELETE /roles/users
Remove a role from a user.

---

### GET /permissions
List all available permissions.

---

## ❌ Error Codes

| Code | Meaning |
|------|---------|
| `400` | Validation error (invalid data) |
| `401` | Not authenticated |
| `403` | No permission |
| `404` | Resource not found |
| `500` | Internal server error |

### Error Response Format
```json
{
  "status": "error",
  "message": "Error description",
  "errors": {
    "field": ["Validation error 1", "Validation error 2"]
  }
}
```

---

## 🔧 cURL Examples

### Retrieve members
```bash
curl -u "admin:app-password" \
  https://your-nextcloud.com/index.php/apps/verein/members
```

### Create new member
```bash
curl -u "admin:app-password" \
  -X POST \
  -H "Content-Type: application/json" \
  -d '{"name":"Max Mustermann","email":"max@example.com"}' \
  https://your-nextcloud.com/index.php/apps/verein/members
```

### PDF Export
```bash
curl -u "admin:app-password" \
  -o members.pdf \
  https://your-nextcloud.com/index.php/apps/verein/export/members/pdf
```

---

## 📖 OpenAPI Specification

The complete OpenAPI 3.0 specification can be found at:
- [`docs/api/openapi.yaml`](openapi.yaml)

You can import it into tools like [Swagger UI](https://swagger.io/tools/swagger-ui/) or [Postman](https://www.postman.com/).
