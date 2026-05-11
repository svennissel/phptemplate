#!/usr/bin/env bash

set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "$0")" && pwd)"
CSS_DIR="$PROJECT_ROOT/public/css"
WEBFONTS_DIR="$CSS_DIR/webfonts"

if ! command -v curl >/dev/null 2>&1; then
  echo "Fehler: curl ist nicht installiert."
  exit 1
fi

if ! command -v pyftsubset >/dev/null 2>&1; then
  echo "Fehler: pyftsubset ist nicht installiert."
  echo "Bitte installiere fonttools (z. B. pip install fonttools brotli)."
  exit 1
fi

mkdir -p "$WEBFONTS_DIR"

TIMESTAMP=$(date +"%Y%m%d%H%M")
CSS_FILE="$CSS_DIR/fontawesome.css"

TMP_DIR="$(mktemp -d)"
cleanup() {
  rm -rf "$TMP_DIR"
}
trap cleanup EXIT

FA_VERSION="7.2.0"
BASE_URL="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@${FA_VERSION}"

echo "Lade Font Awesome ${FA_VERSION} Webfonts ..."
curl -fsSL "$BASE_URL/webfonts/fa-solid-900.woff2" -o "$TMP_DIR/fa-solid-900.woff2"
curl -fsSL "$BASE_URL/webfonts/fa-regular-400.woff2" -o "$TMP_DIR/fa-regular-400.woff2"

# Statische Codepoints passend zu public/css/fontawesome.css
UNICODES="U+f007,U+f0c0,U+f015,U+f0c5,U+f00c,U+002b,U+f068,U+f2ea,U+f291,U+f2ed,U+f021"

echo "Erzeuge minimierte WOFF2-Dateien ..."
SOLID_FILENAME="fa-solid-900-${TIMESTAMP}.woff2"
REGULAR_FILENAME="fa-regular-400-${TIMESTAMP}.woff2"

pyftsubset "$TMP_DIR/fa-solid-900.woff2" \
  --output-file="$WEBFONTS_DIR/$SOLID_FILENAME" \
  --flavor=woff2 \
  --unicodes="$UNICODES" \
  --layout-features='*' \
  --glyph-names \
  --symbol-cmap \
  --legacy-cmap \
  --notdef-glyph \
  --notdef-outline \
  --recommended-glyphs \
  --name-IDs='*' \
  --name-legacy \
  --name-languages='*'

pyftsubset "$TMP_DIR/fa-regular-400.woff2" \
  --output-file="$WEBFONTS_DIR/$REGULAR_FILENAME" \
  --flavor=woff2 \
  --unicodes="$UNICODES" \
  --layout-features='*' \
  --glyph-names \
  --symbol-cmap \
  --legacy-cmap \
  --notdef-glyph \
  --notdef-outline \
  --recommended-glyphs \
  --name-IDs='*' \
  --name-legacy \
  --name-languages='*'

echo "Aktualisiere $CSS_FILE ..."
# Lösche alte WOFF2 Dateien im Verzeichnis
find "$WEBFONTS_DIR" -name "fa-solid-900-*.woff2" -not -name "$SOLID_FILENAME" -delete
find "$WEBFONTS_DIR" -name "fa-regular-400-*.woff2" -not -name "$REGULAR_FILENAME" -delete
# Falls noch die alten ohne Zeitstempel existieren:
rm -f "$WEBFONTS_DIR/fa-solid-900.woff2" "$WEBFONTS_DIR/fa-regular-400.woff2"

sed -i "s|url(\"webfonts/fa-solid-900[^\"]*\")|url(\"webfonts/$SOLID_FILENAME\")|g" "$CSS_FILE"
sed -i "s|url(\"webfonts/fa-regular-400[^\"]*\")|url(\"webfonts/$REGULAR_FILENAME\")|g" "$CSS_FILE"

echo "Fertig: Minimierte WOFF2-Dateien in public/css/webfonts aktualisiert."
