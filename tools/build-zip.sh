#!/usr/bin/env bash
# Build a distribution zip that includes production vendor/ (Dompdf) and LICENSE.txt.
# Usage: bash tools/build-zip.sh
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
	echo "Composer is required to build the distribution zip." >&2
	exit 1
fi

if [ ! -f "$ROOT/composer.lock" ]; then
	echo "composer.lock must be versioned and present to build the zip." >&2
	exit 1
fi

# Compile translation binaries from the versioned .po (also committed in languages/).
if command -v wp >/dev/null 2>&1; then
	echo "Compiling languages (.mo and .l10n.php)..."
	wp i18n make-mo "$ROOT/languages" --allow-root
	wp i18n make-php "$ROOT/languages" --allow-root
fi

echo "Installing production dependencies (composer.lock, --no-dev --optimize-autoloader)..."
$COMPOSER_BIN install --no-dev --optimize-autoloader --prefer-dist --no-interaction

if ! $COMPOSER_BIN audit --no-dev --locked; then
	echo "composer audit --no-dev --locked failed." >&2
	exit 1
fi

if [ ! -f "$ROOT/vendor/autoload.php" ]; then
	echo "vendor/autoload.php missing after composer install." >&2
	exit 1
fi

# Drop Composer bin stubs and leftover VCS dirs; keep Dompdf and runtime deps.
rm -rf "$ROOT/vendor/bin"
find "$ROOT/vendor" -type d \( -name '.git' -o -name 'Tests' -o -name 'tests' -o -name 'docs' -o -name '.github' \) -prune -exec rm -rf {} +

rm -rf "$DIST_DIR"
mkdir -p "$STAGE"

# Copy plugin files using .distignore (same exclusions as wordpress.org / git archive).
EXCLUDE_FILE="$(mktemp)"
grep -vE '^\s*(#|$)' "$ROOT/.distignore" > "$EXCLUDE_FILE"
tar \
	--exclude-from="$EXCLUDE_FILE" \
	--exclude='.git' \
	--exclude='dist' \
	--exclude='./composer' \
	-cf - . | tar -xf - -C "$STAGE"
rm -f "$EXCLUDE_FILE"

# Belt-and-suspenders: never ship Composer CLI stubs from vendor/.
rm -rf "$STAGE/vendor/bin"

if [ ! -f "$STAGE/vendor/autoload.php" ]; then
	echo "Staged zip is missing vendor/autoload.php." >&2
	exit 1
fi
if [ ! -f "$STAGE/LICENSE.txt" ]; then
	echo "Staged zip is missing LICENSE.txt." >&2
	exit 1
fi
if [ ! -f "$STAGE/languages/oli-pdf-invoices-packing-slips.pot" ]; then
	echo "Staged zip is missing languages/*.pot." >&2
	exit 1
fi
if [ ! -f "$STAGE/languages/oli-pdf-invoices-packing-slips-fr_FR.mo" ]; then
	echo "Staged zip is missing compiled languages/*.mo." >&2
	exit 1
fi
if [ ! -f "$STAGE/languages/oli-pdf-invoices-packing-slips-fr_FR.l10n.php" ]; then
	echo "Staged zip is missing compiled languages/*.l10n.php." >&2
	exit 1
fi
if [ -d "$STAGE/vendor/bin" ]; then
	echo "Staged zip must not include vendor/bin." >&2
	exit 1
fi

# Fail if a require-dev package leaked into the zip (e.g. PHPCS).
if [ -d "$STAGE/vendor/squizlabs" ] || [ -d "$STAGE/vendor/dealerdirect" ] || [ -d "$STAGE/vendor/wp-coding-standards" ]; then
	echo "Staged zip must not include Composer dev-dependencies." >&2
	exit 1
fi

cd "$DIST_DIR"
rm -f "$ZIP_PATH"
zip -r -q "$(basename "$ZIP_PATH")" "$PLUGIN_SLUG"

echo "Built $ZIP_PATH"
echo "Contents include vendor/autoload.php, LICENSE.txt, and languages/*.pot"
