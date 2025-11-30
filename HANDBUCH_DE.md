# Verein App – Kurzes Handbuch (v0.2.0-beta)

Dieses Handbuch beschreibt die wichtigsten Schritte zur Installation, Nutzung und Fehlerbehebung der Nextcloud-Vereins-App.

## Installation
- Voraussetzungen: Nextcloud 27+, PHP 8.1+, Datenbank (MariaDB/MySQL).
- App-Verzeichnis: `/nextcloud/apps/verein`.
- Deployment: Kopiere `lib/`, `templates/`, `appinfo/` und `js/dist/` in das App-Verzeichnis.
- Anschließend ausführen:
  - `occ app:disable verein && occ app:enable verein`
  - `occ maintenance:repair` (löscht Frontend-Caches)

## Erste Schritte
- Öffne die App über das Nextcloud-Menü „Verein“.
- Das Dashboard zeigt Mitglieder- und Gebührenstatistiken.
- Tabs: „Mitglieder“, „Finanzen“, „Termine“, „Aufgaben“, „Dokumente“.

## Funktionen
- Mitglieder: Liste, Anlegen, Bearbeiten, Löschen.
- Finanzen: Gebühren mit Status `open`/`paid`/`overdue` und Fälligkeitsdatum.
- Statistiken: Dashboard-Zahlen kommen aus den Endpunkten `statistics/members` und `statistics/fees`.

## Diagramme (optional)
- Diagramme sind standardmäßig deaktiviert (Stabilität). 
- Aktivierung: Einstellungen → „Diagramme aktivieren“. 
- Hinweis: Bei Aktivierung erfolgt ein Debug-Log (`[ChartDebug]`) zur Analyse der Canvas-Größe.

## Einstellungen
- API: `GET /apps/verein/settings/app` (App-Status), `PUT /apps/verein/settings/charts/{enabled}` (Diagramme).
- In der Oberfläche unter „Einstellungen“ erreichbar.

## Bekannte Hinweise (CSP)
- Browser-Konsole kann Warnungen zu Inline-Skripten zeigen. Die App überlässt Content-Security-Policy dem Nextcloud-Kern und lädt Kernskripte (`core/common`, `core/main`).
- Falls „OC is not defined“ erscheint, App neu laden oder `occ maintenance:repair` ausführen.

## Fehlerbehebung
- Leere Seite / UI blockiert:
  - App deaktivieren/aktivieren, `maintenance:repair` ausführen.
  - Prüfe, ob `js/dist/nextcloud-verein.mjs` und `style.css` bereitstehen.
- Zahlen stimmen nicht:
  - Dashboard zieht Daten aus `StatisticsService`. `open` wird wie `pending` gezählt, `due` wenn Fälligkeitsdatum überschritten, `overdue` bei explizitem Status.

## Support
- Logs prüfen: Nextcloud-Admin-Logs und Browser-Konsole.
- Bei Bedarf Diagramme deaktiviert lassen und nur mit Zahlen arbeiten.

## Release-Hinweise (v0.2.0-beta)
- Stabilitätsverbesserungen im Dashboard und Navigation.
- Optionaler Diagramm-Schalter in den Einstellungen.
- Skip-Links visuell versteckt, bleiben per Tastatur fokussierbar.
