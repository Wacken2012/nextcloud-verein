# Verein App – Quick Handbook (v0.2.0-beta)

This handbook provides key guidance for installing, using, and troubleshooting the Nextcloud Verein app.

## Installation
- Requirements: Nextcloud 27+, PHP 8.1+, Database (MariaDB/MySQL).
- App path: `/nextcloud/apps/verein`.
- Deployment: Copy `lib/`, `templates/`, `appinfo/`, and `js/dist/` into the app directory.
- Then run:
  - `occ app:disable verein && occ app:enable verein`
  - `occ maintenance:repair` (clears frontend caches)

## Getting Started
- Open the app via the Nextcloud menu “Verein”.
- The dashboard shows member and fee statistics.
- Tabs: “Members”, “Finance”, “Calendar”, “Tasks”, “Documents”.

## Features
- Members: list, create, update, delete.
- Finance: fees with status `open`/`paid`/`overdue` and due dates.
- Statistics: dashboard numbers come from endpoints `statistics/members` and `statistics/fees`.

## Charts (optional)
- Charts are disabled by default (stability). 
- Enable: Settings → “Enable charts”. 
- Note: When enabled, a debug log (`[ChartDebug]`) records canvas sizes for analysis.

## Settings
- API: `GET /apps/verein/settings/app` (app status), `PUT /apps/verein/settings/charts/{enabled}` (charts toggle).
- Available via the “Settings” tab in the app.

## Known Notes (CSP)
- Browser console may show inline script warnings. The app relies on Nextcloud’s default Content-Security-Policy and loads core scripts (`core/common`, `core/main`).
- If “OC is not defined” appears, reload the app or run `occ maintenance:repair`.

## Troubleshooting
- Blank page / UI blocked:
  - Disable/enable the app and run `maintenance:repair`.
  - Ensure `js/dist/nextcloud-verein.mjs` and `style.css` are present.
- Incorrect numbers:
  - Dashboard uses `StatisticsService`. `open` is treated like `pending`, `due` is when due date passed, `overdue` is explicit status.

## Support
- Check Nextcloud admin logs and browser console.
- Keep charts disabled and rely on numeric stats if needed.

## Release Notes (v0.2.0-beta)
- Stability improvements around dashboard and navigation.
- Optional charts toggle in settings.
- Skip links visually hidden, kept accessible via keyboard focus.
