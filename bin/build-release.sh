#!/usr/bin/env bash
# Build a wordpress.org-style release zip that includes vendor/ (no Composer needed on the server).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PLUGIN_SLUG="oli-pdf-invoices-packing-slips"
VERSION="$(grep -m1 "Version:" "$ROOT/oli-pdf-invoices-packing-slips.php" | sed 's/.*Version:[[:space:]]*//')"
DIST_DIR="$ROOT/dist"
STAGE="$DIST_DIR/$PLUGIN_SLUG"
ZIP_PATH="$DIST_DIR/${PLUGIN_SLUG}-${VERSION}.zip"

cd "$ROOT"

COMPOSER_BIN=""
if command -v composer >/dev/null 2>&1; then
	COMPOSER_BIN="composer"
elif [ -f "$ROOT/composer" ]; then
	COMPOSER_BIN="php $ROOT/composer"
elif [ -f "$ROOT/composer.phar" ]; then
	COMPOSER_BIN="php $ROOT/composer.phar"
else
	echo "Composer is required to build the release zip." >&2
	exit 1
fi

echo "Installing production dependencies..."
$COMPOSER_BIN install --no-dev --optimize-autoloader --prefer-dist --no-interaction --no-security-blocking

if [ ! -f "$ROOT/vendor/autoload.php" ]; then
	echo "vendor/autoload.php missing after composer install." >&2
	exit 1
fi

rm -rf "$DIST_DIR"
mkdir -p "$STAGE"

# Copy plugin files, excluding development-only paths.
tar \
	--exclude='.git' \
	--exclude='.github' \
	--exclude='dist' \
	--exclude='tests' \
	--exclude='bin' \
	--exclude='node_modules' \
	--exclude='IMPLEMENTATION_SUMMARY.md' \
	--exclude='.gitignore' \
	--exclude='phpcs.xml' \
	--exclude='composer' \
	--exclude='composer.phar' \
	-cf - . | tar -xf - -C "$STAGE"

if [ ! -f "$STAGE/vendor/autoload.php" ]; then
	echo "Staged zip is missing vendor/autoload.php." >&2
	exit 1
fi

cd "$DIST_DIR"
rm -f "$ZIP_PATH"
zip -r -q "$(basename "$ZIP_PATH")" "$PLUGIN_SLUG"

echo "Built $ZIP_PATH"
echo "Contents include vendor/autoload.php: yes"
