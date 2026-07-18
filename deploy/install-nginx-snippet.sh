#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
SRC="$ROOT/deploy/nginx-snippets/simantik.conf"
DEST="/etc/nginx/snippets/simantik.conf"

if [ ! -f "$SRC" ]; then
  echo "ERROR: $SRC tidak ditemukan"
  exit 1
fi

echo "==> Menyalin snippet nginx"
echo "    $SRC"
echo " -> $DEST"
sudo cp "$SRC" "$DEST"
sudo chmod 644 "$DEST"

echo ""
echo "==> Tambahkan di server block puspelkes (sebelum location /):"
echo ""
echo "    include /etc/nginx/snippets/simantik.conf;"
echo ""
echo "==> Lalu uji dan reload:"
echo ""
echo "    sudo nginx -t && sudo systemctl reload nginx"
echo "    ./deploy/verify-path.sh"
