#!/usr/bin/env bash
# Compatibility wrapper — distribution zips are built by tools/build-zip.sh.
set -euo pipefail
exec "$(cd "$(dirname "$0")/.." && pwd)/tools/build-zip.sh" "$@"
