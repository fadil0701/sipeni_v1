#!/usr/bin/env bash
set -euo pipefail

PORT="$(grep '^APP_PORT=' .env 2>/dev/null | head -1 | cut -d= -f2- | tr -d '"' || echo 7001)"

echo "==> Docker langsung (:${PORT})"
echo -n "  GET :${PORT}/up → "
curl -fsS -o /dev/null -w '%{http_code}\n' "http://127.0.0.1:${PORT}/up" 2>/dev/null || echo "GAGAL"

echo -n "  GET :${PORT}/demo-simantik/up → "
curl -fsS -o /dev/null -w '%{http_code}\n' "http://127.0.0.1:${PORT}/demo-simantik/up" 2>/dev/null || echo "GAGAL"

echo ""
echo "==> Nginx host portal (:8081)"
echo -n "  GET /demo-simantik/up → "
code=$(curl -fsS -o /dev/null -w '%{http_code}' "http://127.0.0.1:8081/demo-simantik/up" 2>/dev/null || echo "000")
echo "$code"
if [ "$code" != "200" ]; then
    echo "    PERINGATAN: harus 200. Jika 404, snippet /demo-simantik/ belum dipasang di nginx host."
fi

echo ""
echo "==> Nginx config (butuh sudo)"
if command -v sudo >/dev/null 2>&1; then
    sudo nginx -T 2>/dev/null | grep -nE 'location /demo-simantik|snippets/simantik' | head -20 \
        || echo "  (tidak menemukan — pasang ./deploy/install-nginx-snippet.sh)"
else
    echo "  sudo tidak tersedia — cek manual: nginx -T | grep simantik"
fi

echo ""
echo "==> .env (APP_URL / SESSION_PATH)"
if [ -f .env ]; then
    grep -E '^(APP_URL|ASSET_URL|APP_SUBPATH|SESSION_PATH|APP_USE_REQUEST_URL)=' .env || true
else
    echo "  .env tidak ada"
fi
