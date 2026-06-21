"""Smoke test: setiap role/login mengakses halaman yang diizinkan & ditolak."""

from __future__ import annotations

import os
import time

from simantik.client import SimantikClient
from simantik.personas import Persona
from simantik.report import RunReport


def run_smoke(client: SimantikClient, personas: list[Persona], report: RunReport) -> None:
    report.title = "SI-MANTIK Smoke Test per Role"
    login_delay = float(os.getenv("SIMANTIK_LOGIN_DELAY", "13"))

    try:
        r = client.get("/login")
        ok = r.status_code == 200
        report.add("system", "Preflight /login", ok, f"Status {r.status_code}")
        if not ok:
            return
    except Exception as exc:
        report.add("system", "Preflight /login", False, str(exc))
        return

    for persona in personas:
        client.logout()
        if login_delay > 0:
            time.sleep(login_delay)
        t0 = time.perf_counter()
        try:
            login = client.login(persona.email, persona.password)
        except RuntimeError as exc:
            report.add(persona.label, "Login", False, str(exc))
            continue
        ms = int((time.perf_counter() - t0) * 1000)
        report.add(persona.label, "Login", login.ok, login.message, ms)
        if not login.ok:
            continue

        for path in persona.allowed_get:
            result = client.check_get(path, expect_status=(200, 302))
            report.add(persona.label, f"Akses diizinkan {path}", result.ok, result.message)

        for path in persona.forbidden_get:
            result = client.check_get(path, expect_status=403)
            report.add(persona.label, f"Akses ditolak {path}", result.ok, result.message)

        client.logout()
