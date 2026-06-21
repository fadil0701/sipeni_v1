#!/usr/bin/env python3
"""
SI-MANTIK — Otomasi visual di browser (Playwright)

Menampilkan proses input di Chrome dari login sampai penerimaan selesai.

Setup sekali:
  pip install -r requirements.txt
  playwright install chromium

Jalankan:
  python run_browser.py
  python run_browser.py --headless          # tanpa jendela (CI)
  python run_browser.py --slow-mo 800       # lebih lambat agar mudah diikuti
"""

from __future__ import annotations

import argparse
import os
import sys
from datetime import datetime
from pathlib import Path

from dotenv import load_dotenv

ROOT = Path(__file__).resolve().parent
sys.path.insert(0, str(ROOT))

from simantik.bootstrap import load_bootstrap
from simantik.browser.assets import BuiltAssetsFallback, ensure_ui_assets
from simantik.browser.client import SimantikBrowser
from simantik.personas import load_flow_config
from simantik.report import RunReport
from simantik.scenarios.browser_flow import run_browser_flow


def env_bool(name: str, default: bool = False) -> bool:
    val = os.getenv(name)
    if val is None:
        return default
    return val.strip().lower() in ("1", "true", "yes", "on")


def main() -> int:
    load_dotenv(ROOT / ".env")

    if hasattr(sys.stdout, "reconfigure"):
        try:
            sys.stdout.reconfigure(encoding="utf-8")
        except Exception:
            pass

    parser = argparse.ArgumentParser(description="SI-MANTIK browser automation (visual)")
    parser.add_argument("--base-url", default=os.getenv("SIMANTIK_BASE_URL", "http://127.0.0.1:8000"))
    parser.add_argument(
        "--project-root",
        default=os.getenv("SIMANTIK_PROJECT_ROOT", str(ROOT.parent.parent)),
    )
    parser.add_argument("--html", action="store_true", help="Simpan laporan HTML")
    parser.add_argument(
        "--headed",
        action="store_true",
        help="Paksa tampilkan jendela browser (default: terlihat)",
    )
    parser.add_argument(
        "--headless",
        action="store_true",
        help="Sembunyikan browser — hanya untuk CI",
    )
    parser.add_argument(
        "--slow-mo",
        type=int,
        default=int(os.getenv("SIMANTIK_BROWSER_SLOW_MO", "400")),
        help="Jeda ms antar aksi Playwright (default 400)",
    )
    parser.add_argument(
        "--step-pause",
        type=float,
        default=float(os.getenv("SIMANTIK_BROWSER_STEP_PAUSE", "1.2")),
        help="Jeda detik antar langkah bisnis (default 1.2)",
    )
    args = parser.parse_args()

    headless = args.headless or env_bool("SIMANTIK_BROWSER_HEADLESS", False)
    if args.headed:
        headless = False

    project_root = Path(args.project_root).resolve()
    config_path = ROOT / "config" / "personas.yaml"

    print(f"Bootstrap data dari: {project_root}")
    bootstrap = load_bootstrap(project_root)
    if bootstrap:
        print("  [OK] Bootstrap artisan OK")
    else:
        print("  [WARN] Bootstrap gagal — pastikan DB sudah di-seed")
        return 1

    flow = load_flow_config(config_path, bootstrap)
    report = RunReport(title="Browser Flow", base_url=args.base_url)

    asset_msg, asset_fallback = ensure_ui_assets(project_root, args.base_url)
    if asset_msg:
        print(f"  [INFO] {asset_msg}")

    browser = SimantikBrowser(
        args.base_url,
        headless=headless,
        slow_mo=args.slow_mo,
        step_pause=args.step_pause,
    )

    print()
    print("=" * 72)
    print("SI-MANTIK Browser Automation")
    print(f"URL: {args.base_url}")
    print(f"Mode: {'headless (tanpa jendela)' if headless else 'HEADED — jendela Chrome akan muncul'}")
    if headless:
        print("Tip: hapus --headless atau tambahkan --headed")
    print("=" * 72)
    print()

    with browser.session():
        run_browser_flow(browser, flow, bootstrap, report)

    if asset_fallback:
        asset_fallback.restore()

    report.print_summary()

    if args.html:
        report_dir = ROOT / os.getenv("SIMANTIK_REPORT_DIR", "reports")
        ts = datetime.now().strftime("%Y%m%d_%H%M%S")
        out = report_dir / f"simantik_browser_{ts}.html"
        report.write_html(out)
        print(f"Laporan HTML: {out}")

    return 1 if report.failed_count else 0


if __name__ == "__main__":
    raise SystemExit(main())
