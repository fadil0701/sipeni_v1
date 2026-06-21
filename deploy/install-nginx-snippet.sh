#!/usr/bin/env bash
# Pasang snippet nginx SI-MANTIK ke /etc/nginx/snippets/simantik.conf
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
echo "==> Tambahkan di server block puspelkes.jakarta.go.id (sebelum location /):"
echo ""
echo "    include /etc/nginx/snippets/simantik.conf;"
echo ""
echo "==> Lalu uji dan reload:"
echo ""
echo "    sudo nginx -t && sudo systemctl reload nginx"
echo "    curl -f http://127.0.0.1:7001/demo-simantik/up"
echo "    curl -I https://puspelkes.jakarta.go.id/demo-simantik/up"
