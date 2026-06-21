"""Pastikan CSS/JS Laravel termuat saat otomasi browser."""

from __future__ import annotations

import re
from pathlib import Path

import requests


def _vite_dev_url(html: str) -> str | None:
    match = re.search(r"https?://[^\"']+:5173", html)
    return match.group(0) if match else None


def _vite_reachable(url: str, timeout: float = 2.0) -> bool:
    try:
        r = requests.get(url, timeout=timeout)
        return r.status_code < 500
    except requests.RequestException:
        return False


class BuiltAssetsFallback:
    """Sementara nonaktifkan public/hot agar Laravel pakai public/build."""

    def __init__(self, project_root: Path) -> None:
        self.hot = project_root / "public" / "hot"
        self.backup = project_root / "public" / "hot.e2e.bak"
        self._moved = False

    def activate(self) -> None:
        if self.hot.is_file() and not self.backup.is_file():
            self.hot.rename(self.backup)
            self._moved = True

    def restore(self) -> None:
        if self._moved and self.backup.is_file() and not self.hot.is_file():
            self.backup.rename(self.hot)
            self._moved = False


def ensure_ui_assets(project_root: Path, base_url: str) -> tuple[str | None, BuiltAssetsFallback | None]:
    """
    Cek apakah halaman memuat Vite dev. Jika dev mati, fallback ke npm run build.

    Returns (pesan info, fallback instance untuk restore — atau None).
    """
    manifest = project_root / "public" / "build" / "manifest.json"
    try:
        html = requests.get(f"{base_url.rstrip('/')}/login", timeout=10).text
    except requests.RequestException as exc:
        return (
            f"Tidak bisa akses {base_url}/login — pastikan server Laravel aktif ({exc})",
            None,
        )

    vite_url = _vite_dev_url(html)
    if not vite_url:
        return None, None

    if _vite_reachable(vite_url):
        return None, None

    if not manifest.is_file():
        return (
            f"Vite dev tidak aktif ({vite_url}) dan public/build belum ada.\n"
            "  Jalankan: npm run build   ATAU   composer run dev (server + vite)",
            None,
        )

    fallback = BuiltAssetsFallback(project_root)
    fallback.activate()
    return (
        f"Vite dev tidak aktif ({vite_url}) — otomasi memakai asset build (public/build).\n"
        "  Tip: `composer run dev` untuk tampilan hot-reload seperti biasa.",
        fallback,
    )
