#!/usr/bin/env bash
set -euo pipefail

# Simple integration smoke test: expect HTTP 200 for GET /members/1
# Usage: ./test_member_get.sh [URL] [USER:PASS]

URL="${1:-http://localhost/nextcloud/index.php/apps/verein/members/1}"
AUTH="${2:-authtest:NcAuth2025}"

HTTP=$(curl -sS -u "$AUTH" -o /dev/null -w "%{http_code}" "$URL")
if [ "$HTTP" -eq 200 ]; then
  echo "OK: HTTP $HTTP from $URL"
  exit 0
else
  echo "FAIL: HTTP $HTTP from $URL"
  exit 2
fi
