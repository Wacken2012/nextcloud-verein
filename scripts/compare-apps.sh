#!/usr/bin/env bash
set -euo pipefail

OUR_APP_ROOT="${1:-$PWD}"
REFERENCE_APP_ROOT="${2:-/var/www/html/nextcloud/apps/files_sharing}"
REPORT_DIR="${3:-$PWD/tmp/compare-report}"
RBAC_KEYWORDS='role|permission|acl|rbac|group|share|policy|scope'

mkdir -p "$REPORT_DIR"

echo "# Comparing $OUR_APP_ROOT <-> $REFERENCE_APP_ROOT" | tee "$REPORT_DIR/summary.txt"

# 1) File-level overview
echo "## File stats" | tee -a "$REPORT_DIR/summary.txt"
# diff --no-index --stat might fail if one dir is missing, but we assume they exist.
# We use || true because diff returns 1 if files differ.
diff --no-index --stat "$REFERENCE_APP_ROOT" "$OUR_APP_ROOT" || true \
  | tee "$REPORT_DIR/diff-stat.txt"

# 2) PHP / JS diffs (only files present on both sides)
# We look for files in OUR_APP_ROOT and check if they exist in REFERENCE_APP_ROOT
find "$OUR_APP_ROOT" -type f \( -name '*.php' -o -name '*.js' \) | sort | while read -r file; do
  rel="${file#$OUR_APP_ROOT/}"
  ref="$REFERENCE_APP_ROOT/$rel"
  if [[ -f "$ref" ]]; then
    if ! diff -q "$ref" "$file" &>/dev/null; then
      out="$REPORT_DIR/${rel//\//_}.diff"
      echo ">>> $rel" | tee -a "$REPORT_DIR/summary.txt"
      mkdir -p "$(dirname "$out")"
      diff --unified=5 "$ref" "$file" > "$out" || true
    fi
  else
    # echo "Missing-in-reference: $rel" | tee -a "$REPORT_DIR/summary.txt"
    : # Silence missing files to reduce noise, as custom apps will have many unique files
  fi
done

# 3) RBAC-specific keyword hotspots
echo "## RBAC keyword hits" | tee -a "$REPORT_DIR/summary.txt"
# grep returns 1 if no matches found, so we add || true
grep -RInE "$RBAC_KEYWORDS" "$OUR_APP_ROOT/lib" "$OUR_APP_ROOT/js" 2>/dev/null || true \
  | tee "$REPORT_DIR/rbac-keywords.txt"

echo "Report generated in $REPORT_DIR"
