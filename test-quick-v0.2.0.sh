#!/bin/bash

# 🧪 Nextcloud Verein App - Quick Test Script v0.2.0
# Testet alle 6 Features

set -e

echo "🚀 Nextcloud Verein v0.2.0 - Quick Test Script"
echo "=============================================="
echo ""

# Credentials abfragen
read -sp "Nextcloud Benutzername: " NC_USER
echo
read -sp "Nextcloud Passwort: " NC_PASS
echo
read -p "Nextcloud URL (default: http://localhost/nextcloud): " NC_URL
NC_URL=${NC_URL:-http://localhost/nextcloud}

APP_URL="$NC_URL/apps/verein"
ADMIN_URL="$NC_URL/index.php/settings/admin/verein"

echo ""
echo "Teste mit Credentials: $NC_USER @ $NC_URL"
echo ""

# Farben
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

test_count=0
pass_count=0
fail_count=0

run_test() {
    local test_name=$1
    local curl_cmd=$2
    
    test_count=$((test_count + 1))
    echo -n "[$test_count] Testing: $test_name ... "
    
    response=$(eval "$curl_cmd" 2>/dev/null || echo "FAILED")
    
    if echo "$response" | grep -q "error\|FAILED\|401\|403\|404" 2>/dev/null; then
        echo -e "${RED}❌ FAILED${NC}"
        echo "    Response: ${response:0:100}"
        fail_count=$((fail_count + 1))
    else
        echo -e "${GREEN}✅ PASSED${NC}"
        pass_count=$((pass_count + 1))
    fi
}

# FEATURE 1: Mitgliederverwaltung
echo -e "${YELLOW}=== FEATURE 1: Mitgliederverwaltung ===${NC}"
run_test "GET /api/v1/members" "curl -s -u '$NC_USER:$NC_PASS' '$APP_URL/api/v1/members'"
run_test "GET /api/v1/members (mit Limit)" "curl -s -u '$NC_USER:$NC_PASS' '$APP_URL/api/v1/members?limit=5'"
echo ""

# FEATURE 3: Beitragsabrechnung
echo -e "${YELLOW}=== FEATURE 3: Beitragsabrechnung ===${NC}"
run_test "GET /api/v1/fees" "curl -s -u '$NC_USER:$NC_PASS' '$APP_URL/api/v1/fees'"
run_test "GET /api/v1/fees (Filter: open)" "curl -s -u '$NC_USER:$NC_PASS' '$APP_URL/api/v1/fees?status=open'"
echo ""

# FEATURE 4: SEPA-Export
echo -e "${YELLOW}=== FEATURE 4: SEPA-Export ===${NC}"
run_test "GET SEPA Export Info" "curl -s -u '$NC_USER:$NC_PASS' '$APP_URL/api/v1/export/sepa' | head -c 50"
echo ""

# FEATURE 5a: RBAC
echo -e "${YELLOW}=== FEATURE 5a: RBAC (Rollenverwaltung) ===${NC}"
run_test "GET /api/v1/roles" "curl -s -u '$NC_USER:$NC_PASS' '$APP_URL/api/v1/roles'"
run_test "GET /api/v1/roles/club/music" "curl -s -u '$NC_USER:$NC_PASS' '$APP_URL/api/v1/roles/club/music'"
run_test "GET /api/v1/roles/1" "curl -s -u '$NC_USER:$NC_PASS' '$APP_URL/api/v1/roles/1'"
echo ""

# FEATURE 5d: API-Authentifizierung
echo -e "${YELLOW}=== FEATURE 5d: API-Authentifizierung ===${NC}"
run_test "Auth: Valid credentials" "curl -s -u '$NC_USER:$NC_PASS' -w '%{http_code}' '$APP_URL/api/v1/roles' | tail -c 4"
run_test "Auth: Invalid credentials (should be 401)" "curl -s -u 'wrong:wrong' -w '%{http_code}' '$APP_URL/api/v1/roles' | tail -c 4"
run_test "Auth: No credentials (should be 401)" "curl -s -w '%{http_code}' '$APP_URL/api/v1/roles' | tail -c 4"
echo ""

# UI Tests
echo -e "${YELLOW}=== UI Tests ===${NC}"
echo "1. Dashboard: $NC_URL/apps/verein/"
echo "2. Members: $NC_URL/apps/verein/#/members"
echo "3. Fees: $NC_URL/apps/verein/#/fees"
echo "4. SEPA Export: $NC_URL/apps/verein/#/sepa"
echo "5. Admin RBAC: $ADMIN_URL"
echo ""

# Summary
echo ""
echo "=============================================="
echo -e "Test Summary:"
echo -e "  Total: $test_count"
echo -e "  ${GREEN}Passed: $pass_count${NC}"
echo -e "  ${RED}Failed: $fail_count${NC}"
echo ""

if [ $fail_count -eq 0 ]; then
    echo -e "${GREEN}✅ All tests passed!${NC}"
    exit 0
else
    echo -e "${RED}❌ Some tests failed. Check output above.${NC}"
    exit 1
fi
