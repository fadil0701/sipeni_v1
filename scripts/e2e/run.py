#!/usr/bin/env python3
"""
SI-MANTIK — Otomasi E2E per role (Python)

Usage:
  cd scripts/e2e
  pip install -r requirements.txt
  copy .env.example .env

  # Smoke test semua role
  python run.py smoke

  # Alur transaksi lengkap (multi-user)
  python run.py flow

  # Alur transaksi DI BROWSER (jendela Chrome terlihat)
  python run.py browser
  # atau: python run_browser.py

  # Keduanya + laporan HTML
  python run.py all --html
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
from simantik.client import SimantikClient
from simantik.personas import load_flow_config, load_personas
from simantik.report import RunReport
from simantik.scenarios.smoke import run_smoke
from simantik.scenarios.transaction_flow import run_transaction_flow


def env_bool(name: str, default: bool = True) -> bool:
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

    parser = argparse.ArgumentParser(description="SI-MANTIK automation per role")
    parser.add_argument(
        "mode",
        choices=("smoke", "flow", "all", "browser"),
        help="smoke=cek halaman per role, flow=alur transaksi HTTP, browser=alur di Chrome, all=smoke+flow",
    )
    parser.add_argument("--html", action="store_true", help="Simpan laporan HTML")
    parser.add_argument(
        "--headed",
        action="store_true",
        help="(mode browser) Paksa tampilkan jendela browser",
    )
    parser.add_argument(
        "--headless",
        action="store_true",
        help="(mode browser) Sembunyikan browser (CI)",
    )
    parser.add_argument("--base-url", default=os.getenv("SIMANTIK_BASE_URL", "http://127.0.0.1:8000"))
    parser.add_argument(
        "--project-root",
        default=os.getenv("SIMANTIK_PROJECT_ROOT", str(ROOT.parent.parent)),
        help="Path ke root Laravel (untuk bootstrap artisan)",
    )
    args = parser.parse_args()

    project_root = Path(args.project_root).resolve()
    config_path = ROOT / "config" / "personas.yaml"
    timeout = int(os.getenv("SIMANTIK_TIMEOUT", "30"))
    verify_ssl = not env_bool("SIMANTIK_INSECURE", True)

    print(f"Bootstrap data dari: {project_root}")
    bootstrap = load_bootstrap(project_root)
    if bootstrap:
        print("  [OK] Bootstrap artisan OK")
    else:
        print("  [WARN] Bootstrap artisan gagal — email pattern mungkin tidak ter-resolve")

    client = SimantikClient(args.base_url, timeout=timeout, verify_ssl=verify_ssl)
    reports: list[RunReport] = []

    if args.mode in ("smoke", "all"):
        personas = load_personas(config_path, bootstrap)
        if not personas:
            print("ERROR: Tidak ada persona yang bisa di-resolve. Pastikan DB sudah di-seed.")
            return 1
        smoke_report = RunReport(title="Smoke", base_url=args.base_url)
        run_smoke(client, personas, smoke_report)
        smoke_report.print_summary()
        reports.append(smoke_report)

    if args.mode == "browser":
        from simantik.browser import SimantikBrowser
        from simantik.browser.assets import ensure_ui_assets
        from simantik.scenarios.browser_flow import run_browser_flow

        headless = args.headless or env_bool("SIMANTIK_BROWSER_HEADLESS", False)
        if args.headed:
            headless = False
        slow_mo = int(os.getenv("SIMANTIK_BROWSER_SLOW_MO", "500"))
        step_pause = float(os.getenv("SIMANTIK_BROWSER_STEP_PAUSE", "1.5"))

        flow = load_flow_config(config_path, bootstrap)
        browser_report = RunReport(title="Browser Flow", base_url=args.base_url)

        asset_msg, asset_fallback = ensure_ui_assets(project_root, args.base_url)
        if asset_msg:
            print(f"  [INFO] {asset_msg}")

        browser = SimantikBrowser(
            args.base_url,
            headless=headless,
            slow_mo=slow_mo,
            step_pause=step_pause,
        )
        print()
        print("=" * 72)
        print("SI-MANTIK Browser Automation")
        print(f"URL: {args.base_url}")
        print(f"Mode: {'headless (tanpa jendela)' if headless else 'HEADED — jendela Chrome akan muncul'}")
        if headless:
            print("Tip: jalankan `python run.py browser --headed` agar browser terlihat")
        print("=" * 72)
        print()
        with browser.session():
            run_browser_flow(browser, flow, bootstrap, browser_report)
        if asset_fallback:
            asset_fallback.restore()
        browser_report.print_summary()
        reports.append(browser_report)
    elif args.mode in ("flow", "all"):
        if args.mode == "all":
            print("Bootstrap ulang sebelum flow (reset stok & cleanup transaksi otomasi)...")
            bootstrap = load_bootstrap(project_root)
            if bootstrap:
                print("  [OK] Bootstrap flow OK")
        flow = load_flow_config(config_path, bootstrap)
        flow_report = RunReport(title="Flow", base_url=args.base_url)
        run_transaction_flow(client, flow, bootstrap, flow_report)
        flow_report.print_summary()
        reports.append(flow_report)

    if args.html and reports:
        report_dir = ROOT / os.getenv("SIMANTIK_REPORT_DIR", "reports")
        ts = datetime.now().strftime("%Y%m%d_%H%M%S")
        for i, rep in enumerate(reports):
            suffix = "smoke" if i == 0 and len(reports) > 1 else ("flow" if len(reports) > 1 else args.mode)
            out = report_dir / f"simantik_{suffix}_{ts}.html"
            rep.write_html(out)
            print(f"Laporan HTML: {out}")

    failed = sum(r.failed_count for r in reports)
    return 1 if failed else 0


if __name__ == "__main__":
    raise SystemExit(main())
