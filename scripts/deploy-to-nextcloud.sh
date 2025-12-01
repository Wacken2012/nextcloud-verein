#!/usr/bin/env bash
set -euo pipefail

# Deploy built assets from this repository to a local Nextcloud installation.
# Usage: sudo ./scripts/deploy-to-nextcloud.sh [--dry-run]

DRY_RUN=0
if [[ "${1:-}" == "--dry-run" ]]; then
  DRY_RUN=1
fi

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DIST_DIR="$REPO_ROOT/js/dist"
LIB_DIR="$REPO_ROOT/lib"
TEMPLATES_DIR="$REPO_ROOT/templates"
APPINFO_DIR="$REPO_ROOT/appinfo"
VENDOR_DIR="$REPO_ROOT/vendor"

if [[ ! -d "$DIST_DIR" ]]; then
  echo "Error: built assets not found at $DIST_DIR"
  echo "Run 'npm run build' in the repo root first."
  exit 2
fi

# Common Nextcloud app install locations to check
CANDIDATES=(
  "/var/www/html/nextcloud/apps/verein"
  "/var/www/nextcloud/apps/verein"
  "/var/www/nextcloud/apps/verein"
  "/usr/share/nextcloud/apps/verein"
)

TARGET=""
for p in "${CANDIDATES[@]}"; do
  if [[ -d "$p" ]]; then
    TARGET="$p"
    break
  fi
done

if [[ -z "$TARGET" ]]; then
  echo "No installed 'verein' app found in standard locations."
  echo "Please provide the target path as the first argument, for example:"
  echo "  sudo $0 /var/www/html/nextcloud/apps/verein"
  exit 3
fi

echo "Deploy target detected: $TARGET"

if [[ "$DRY_RUN" -eq 1 ]]; then
  echo "Dry run enabled — showing rsync commands only"
fi

# Ensure target subdirs exist
mkdir -p "$TARGET/js"
mkdir -p "$TARGET/js/dist"

# Helper to run or echo
run_rsync() {
  local src="$1"
  local dst="$2"
  if [[ "$DRY_RUN" -eq 1 ]]; then
    echo "+ rsync -av --delete '$src' '$dst'"
  else
    echo "+ rsync -av --delete '$src' '$dst'"
    rsync -av --delete "$src" "$dst"
  fi
}

run_cp() {
  local src="$1"
  local dst="$2"
  if [[ "$DRY_RUN" -eq 1 ]]; then
    echo "+ cp '$src' '$dst'"
  else
    echo "+ cp '$src' '$dst'"
    cp "$src" "$dst"
  fi
}

# Sync js/dist
run_rsync "$DIST_DIR/" "$TARGET/js/dist/"

# Sync top-level js files (external scripts that are not part of the bundle)
run_cp "$REPO_ROOT/js/admin-roles.js" "$TARGET/js/admin-roles.js"

# Sync PHP backend and templates (use careful overwrite)
run_rsync "$LIB_DIR/" "$TARGET/lib/"
run_rsync "$TEMPLATES_DIR/" "$TARGET/templates/"
run_rsync "$APPINFO_DIR/" "$TARGET/appinfo/"

# Sync composer vendor dependencies (includes TCPDF)
if [[ -d "$VENDOR_DIR" ]]; then
  run_rsync "$VENDOR_DIR/" "$TARGET/vendor/"
fi

# Set ownership to www-data (recommended for most Nextcloud installs) if run as root
if [[ "$DRY_RUN" -eq 1 ]]; then
  echo "+ chown -R www-data:www-data $TARGET (skipped in dry-run)"
else
  if [[ "$(id -u)" -eq 0 ]]; then
    echo "+ chown -R www-data:www-data $TARGET"
    chown -R www-data:www-data "$TARGET"
  else
    echo "! Skipping chown (not running as root)."
  fi
fi

echo "Deployment complete."

echo "Next steps:"
echo " - If Nextcloud is served from /var/www/html/nextcloud use the occ utility to reload apps:" 
if [[ -x "/var/www/html/nextcloud/occ" ]]; then
  echo "     sudo -u www-data php /var/www/html/nextcloud/occ app:enable verein || true"
  echo "     sudo -u www-data php /var/www/html/nextcloud/occ maintenance:mode --on && sudo -u www-data php /var/www/html/nextcloud/occ maintenance:mode --off || true"
elif [[ -x "/var/www/nextcloud/occ" ]]; then
  echo "     sudo -u www-data php /var/www/nextcloud/occ app:enable verein || true"
  echo "     sudo -u www-data php /var/www/nextcloud/occ maintenance:mode --on && sudo -u www-data php /var/www/nextcloud/occ maintenance:mode --off || true"
else
  echo "     Run the appropriate 'occ' commands for your Nextcloud installation to reload the app if necessary."
fi

echo "Visit your Nextcloud and test the app UI (e.g. https://your-nextcloud/apps/verein)."
