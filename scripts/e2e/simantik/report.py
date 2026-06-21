"""Laporan hasil otomasi (konsol + HTML)."""

from __future__ import annotations

import sys
from dataclasses import dataclass, field
from datetime import datetime, timezone
from html import escape
from pathlib import Path

from colorama import Fore, Style, init as colorama_init

colorama_init(autoreset=True)


def _console_text(text: str) -> str:
    """Hindari UnicodeEncodeError di konsol Windows (cp1252)."""
    encoding = getattr(sys.stdout, "encoding", None) or "utf-8"
    try:
        text.encode(encoding)
        return text
    except UnicodeEncodeError:
        return text.encode(encoding, errors="replace").decode(encoding)


@dataclass
class StepResult:
    persona: str
    step: str
    passed: bool
    detail: str
    duration_ms: int = 0


@dataclass
class RunReport:
    title: str
    base_url: str
    started_at: datetime = field(default_factory=lambda: datetime.now(timezone.utc))
    results: list[StepResult] = field(default_factory=list)

    def add(self, persona: str, step: str, passed: bool, detail: str, duration_ms: int = 0) -> None:
        self.results.append(
            StepResult(persona=persona, step=step, passed=passed, detail=detail, duration_ms=duration_ms)
        )

    @property
    def passed_count(self) -> int:
        return sum(1 for r in self.results if r.passed)

    @property
    def failed_count(self) -> int:
        return sum(1 for r in self.results if not r.passed)

    def print_summary(self) -> None:
        print()
        print("=" * 72)
        print(f"{self.title}")
        print(f"Base URL: {self.base_url}")
        print("=" * 72)
        for r in self.results:
            icon = f"{Fore.GREEN}[OK]{Style.RESET_ALL}" if r.passed else f"{Fore.RED}[FAIL]{Style.RESET_ALL}"
            print(f"  {icon} [{r.persona}] {r.step}")
            if not r.passed or "->" in r.detail:
                print(f"      {_console_text(r.detail)}")
        print("-" * 72)
        total = len(self.results)
        color = Fore.GREEN if self.failed_count == 0 else Fore.RED
        print(
            f"{color}Hasil: {self.passed_count}/{total} lulus"
            f"{Style.RESET_ALL} | gagal: {self.failed_count}"
        )
        print("=" * 72)

    def write_html(self, path: Path) -> None:
        path.parent.mkdir(parents=True, exist_ok=True)
        rows = []
        for r in self.results:
            status = "PASS" if r.passed else "FAIL"
            cls = "pass" if r.passed else "fail"
            rows.append(
                f"<tr class='{cls}'><td>{escape(r.persona)}</td>"
                f"<td>{escape(r.step)}</td><td>{status}</td>"
                f"<td>{escape(r.detail)}</td></tr>"
            )
        html = f"""<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>{escape(self.title)}</title>
  <style>
    body {{ font-family: system-ui, sans-serif; margin: 2rem; }}
    table {{ border-collapse: collapse; width: 100%; }}
    th, td {{ border: 1px solid #ddd; padding: 8px; text-align: left; }}
    th {{ background: #1e40af; color: white; }}
    tr.pass td:nth-child(3) {{ color: #15803d; font-weight: bold; }}
    tr.fail td:nth-child(3) {{ color: #b91c1c; font-weight: bold; }}
    tr.fail {{ background: #fef2f2; }}
  </style>
</head>
<body>
  <h1>{escape(self.title)}</h1>
  <p>Base URL: {escape(self.base_url)}</p>
  <p>Waktu: {escape(self.started_at.isoformat())}</p>
  <p><strong>{self.passed_count}/{len(self.results)}</strong> lulus</p>
  <table>
    <thead><tr><th>Persona</th><th>Langkah</th><th>Status</th><th>Detail</th></tr></thead>
    <tbody>{''.join(rows)}</tbody>
  </table>
</body>
</html>"""
        path.write_text(html, encoding="utf-8")
