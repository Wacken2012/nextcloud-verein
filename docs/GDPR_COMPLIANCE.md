# DSGVO-Konformitätsleitfaden / GDPR Compliance Guide

**Version**: 0.2.2  
**Datum**: Dezember 2025

---

## 🇩🇪 Deutsch

### Übersicht

Die Nextcloud Verein App unterstützt Vereine bei der Einhaltung der Datenschutz-Grundverordnung (DSGVO). Dieses Dokument beschreibt die implementierten Datenschutzfunktionen und gibt Empfehlungen für den DSGVO-konformen Betrieb.

### Implementierte DSGVO-Funktionen

#### 1. Auskunftsrecht (Art. 15 DSGVO)

**Funktion**: Mitglieder können ihre gespeicherten Daten exportieren.

**API-Endpunkt**: `GET /api/v1/privacy/export/{memberId}`

**Exportierte Daten**:
- Persönliche Stammdaten (Name, Adresse, E-Mail)
- Mitgliedschaftsinformationen (Eintrittsdatum, Rolle)
- Gebührenverlauf
- Einwilligungen

**Verwendung im Frontend**:
```
Einstellungen → DSGVO → "Meine Daten exportieren"
```

#### 2. Recht auf Löschung (Art. 17 DSGVO)

**Funktion**: Soft-Delete oder vollständige Löschung von Mitgliedsdaten.

**API-Endpunkt**: `POST /api/v1/privacy/delete/{memberId}`

**Löschmodi**:
- `soft_delete`: Anonymisierung der Daten (empfohlen für Aufbewahrungspflichten)
- `hard_delete`: Vollständige Löschung aller Daten

**Hinweis**: Steuerrechtliche Aufbewahrungspflichten beachten (6-10 Jahre für Finanzdaten).

#### 3. Einwilligungsverwaltung (Art. 7 DSGVO)

**Funktion**: Verwaltung von Einwilligungen für verschiedene Verarbeitungszwecke.

**API-Endpunkte**:
- `GET /api/v1/privacy/consent/{memberId}` - Einwilligungsstatus abrufen
- `POST /api/v1/privacy/consent/{memberId}` - Einwilligung speichern

**Einwilligungsarten**:
- Newsletter
- Veröffentlichung auf Website
- Fotoveröffentlichung
- Weitergabe an Dachverband

#### 4. Datenschutzerklärung (Art. 13/14 DSGVO)

**Funktion**: Zentrale Verwaltung der Datenschutzerklärung.

**API-Endpunkt**: `GET /api/v1/privacy/policy`

**Konfiguration**: Im Admin-Bereich unter Einstellungen.

### Empfehlungen für DSGVO-Konformität

#### Verarbeitungsverzeichnis

Führen Sie ein Verzeichnis aller Verarbeitungstätigkeiten gemäß Art. 30 DSGVO:

| Verarbeitung | Rechtsgrundlage | Speicherdauer |
|--------------|-----------------|---------------|
| Mitgliederverwaltung | Art. 6 (1) b - Vertrag | Dauer der Mitgliedschaft + 3 Jahre |
| Gebührenverwaltung | Art. 6 (1) b - Vertrag | 10 Jahre (steuerrechtlich) |
| Newsletter | Art. 6 (1) a - Einwilligung | Bis Widerruf |
| Statistiken | Art. 6 (1) f - Berechtigtes Interesse | Anonymisiert unbegrenzt |

#### Technische Maßnahmen

1. **Zugriffskontrolle**
   - Rollenbasierte Berechtigungen (Admin, Kassierer, Mitglied)
   - Jeder Nutzer sieht nur seine eigenen Daten

2. **Protokollierung**
   - Alle Zugriffe werden geloggt
   - Nachvollziehbarkeit von Änderungen

3. **Verschlüsselung**
   - HTTPS für alle Verbindungen
   - Nextcloud-Verschlüsselung für gespeicherte Daten

#### Organisatorische Maßnahmen

1. **Datenschutzbeauftragter**: Ab 20 Mitgliedern Prüfung erforderlich
2. **Schulungen**: Regelmäßige Sensibilisierung der Vorstände
3. **Auftragsverarbeitung**: Verträge mit Hosting-Anbietern prüfen

### API-Referenz

```bash
# Datenschutzerklärung abrufen
curl -X GET /api/v1/privacy/policy

# Einwilligungen eines Mitglieds
curl -X GET /api/v1/privacy/consent/{memberId}

# Einwilligung speichern
curl -X POST /api/v1/privacy/consent/{memberId} \
  -d '{"type": "newsletter", "given": true}'

# Daten exportieren
curl -X GET /api/v1/privacy/export/{memberId}

# Daten löschen
curl -X POST /api/v1/privacy/delete/{memberId} \
  -d '{"mode": "soft_delete"}'
```

---

## 🇬🇧 English

### Overview

The Nextcloud Verein App supports associations in complying with the General Data Protection Regulation (GDPR). This document describes the implemented privacy features and provides recommendations for GDPR-compliant operation.

### Implemented GDPR Functions

#### 1. Right of Access (Art. 15 GDPR)

**Function**: Members can export their stored data.

**API Endpoint**: `GET /api/v1/privacy/export/{memberId}`

**Exported Data**:
- Personal master data (name, address, email)
- Membership information (join date, role)
- Fee history
- Consents

**Frontend Usage**:
```
Settings → GDPR → "Export my data"
```

#### 2. Right to Erasure (Art. 17 GDPR)

**Function**: Soft-delete or complete deletion of member data.

**API Endpoint**: `POST /api/v1/privacy/delete/{memberId}`

**Deletion Modes**:
- `soft_delete`: Data anonymization (recommended for retention requirements)
- `hard_delete`: Complete deletion of all data

**Note**: Consider tax retention requirements (6-10 years for financial data).

#### 3. Consent Management (Art. 7 GDPR)

**Function**: Managing consent for various processing purposes.

**API Endpoints**:
- `GET /api/v1/privacy/consent/{memberId}` - Get consent status
- `POST /api/v1/privacy/consent/{memberId}` - Save consent

**Consent Types**:
- Newsletter
- Website publication
- Photo publication
- Transfer to umbrella organization

#### 4. Privacy Policy (Art. 13/14 GDPR)

**Function**: Central management of the privacy policy.

**API Endpoint**: `GET /api/v1/privacy/policy`

**Configuration**: In admin area under Settings.

### Recommendations for GDPR Compliance

#### Records of Processing Activities

Maintain a record of all processing activities according to Art. 30 GDPR:

| Processing | Legal Basis | Retention Period |
|------------|-------------|------------------|
| Member management | Art. 6 (1) b - Contract | Duration of membership + 3 years |
| Fee management | Art. 6 (1) b - Contract | 10 years (tax law) |
| Newsletter | Art. 6 (1) a - Consent | Until withdrawal |
| Statistics | Art. 6 (1) f - Legitimate interest | Anonymized indefinitely |

#### Technical Measures

1. **Access Control**
   - Role-based permissions (Admin, Treasurer, Member)
   - Each user only sees their own data

2. **Logging**
   - All access is logged
   - Traceability of changes

3. **Encryption**
   - HTTPS for all connections
   - Nextcloud encryption for stored data

#### Organizational Measures

1. **Data Protection Officer**: Review required from 20 members
2. **Training**: Regular awareness training for board members
3. **Data Processing Agreements**: Review contracts with hosting providers

### API Reference

```bash
# Get privacy policy
curl -X GET /api/v1/privacy/policy

# Get member consents
curl -X GET /api/v1/privacy/consent/{memberId}

# Save consent
curl -X POST /api/v1/privacy/consent/{memberId} \
  -d '{"type": "newsletter", "given": true}'

# Export data
curl -X GET /api/v1/privacy/export/{memberId}

# Delete data
curl -X POST /api/v1/privacy/delete/{memberId} \
  -d '{"mode": "soft_delete"}'
```

---

## Rechtlicher Hinweis / Legal Notice

Dieses Dokument dient nur zur Information und ersetzt keine rechtliche Beratung. Konsultieren Sie bei Fragen zur DSGVO-Konformität einen Datenschutzexperten.

This document is for informational purposes only and does not replace legal advice. Consult a data protection expert for questions about GDPR compliance.

---

**Weitere Informationen / Further Information**:
- [DSGVO Volltext](https://eur-lex.europa.eu/legal-content/DE/TXT/?uri=CELEX:32016R0679)
- [GDPR Full Text](https://eur-lex.europa.eu/legal-content/EN/TXT/?uri=CELEX:32016R0679)
- [Nextcloud Security](https://nextcloud.com/security/)
