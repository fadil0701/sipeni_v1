"""Alur transaksi lengkap: permintaan → approval → distribusi → penerimaan."""

from __future__ import annotations

import os
import re
import time
from typing import Any

from urllib.parse import urlparse

from bs4 import BeautifulSoup
from simantik.client import SimantikClient
from simantik.report import RunReport


def _login_step(client: SimantikClient, report: RunReport, label: str, email: str, password: str) -> bool:
    client.logout()
    delay = float(os.getenv("SIMANTIK_LOGIN_DELAY", "13"))
    if delay > 0:
        time.sleep(delay)
    t0 = time.perf_counter()
    result = client.login(email, password)
    ms = int((time.perf_counter() - t0) * 1000)
    report.add(label, "Login", result.ok, result.message, ms)
    return result.ok


def _extract_permintaan_id(html: str) -> str | None:
    matches = re.findall(r"/transaction/permintaan-barang/(\d+)", html)
    return matches[-1] if matches else None


def _extract_distribusi_id(html: str) -> str | None:
    matches = re.findall(r"/transaction/distribusi/(\d+)", html)
    return matches[-1] if matches else None


def _distribusi_id_from_url(url: str) -> str | None:
    path = urlparse(url).path.rstrip("/")
    if "/transaction/distribusi/" not in path:
        return None
    tail = path.split("/transaction/distribusi/")[-1]
    return tail if tail.isdigit() else None


def _find_distribusi_for_permintaan(
    client: SimantikClient,
    permintaan_id: str,
    *,
    keterangan: str = "Distribusi otomasi Python",
) -> str | None:
    index_html = client.get("/transaction/distribusi").text
    for dist_id in reversed(list(dict.fromkeys(re.findall(r"/transaction/distribusi/(\d+)", index_html)))):
        show = client.get(f"/transaction/distribusi/{dist_id}")
        html = show.text
        if keterangan in html and f"/transaction/permintaan-barang/{permintaan_id}" in html:
            return dist_id
    return None


def _extract_penerimaan_id(html: str) -> str | None:
    matches = re.findall(r"/transaction/penerimaan-barang/(\d+)", html)
    return matches[-1] if matches else None


def _find_approval_log_with_action(client: SimantikClient, action_fragment: str) -> str | None:
    """Verifikasi/approve ada di halaman detail, bukan form inline di index."""
    index_html = client.get("/transaction/approval").text
    log_ids = list(dict.fromkeys(re.findall(r"/transaction/approval/(\d+)", index_html)))
    for log_id in log_ids:
        show = client.get(f"/transaction/approval/{log_id}")
        if client.find_form_action_ids(show.text, action_fragment):
            return log_id
    return None


def _pick_barang_id(barang_opts: list[tuple[str, str]], bootstrap_id: str | None) -> str:
    if bootstrap_id:
        return str(bootstrap_id)
    for value, label in barang_opts:
        if "DMY" in label.upper() or "DUMMY" in label.upper():
            return value
    return barang_opts[0][0] if barang_opts else ""


def _parse_barang_from_template(html: str) -> list[tuple[str, str]]:
    """Barang ada di <template id=\"itemTemplate\"> — baris detail di-render JS."""
    soup = BeautifulSoup(html, "html.parser")
    template = soup.find("template", id="itemTemplate")
    if not template:
        return []
    select = template.find("select", class_=lambda c: c and "select-data-barang" in (c if isinstance(c, str) else " ".join(c)))
    if not select:
        return []
    options: list[tuple[str, str]] = []
    for opt in select.find_all("option"):
        value = (opt.get("value") or "").strip()
        if value:
            options.append((value, opt.get_text(strip=True)))
    return options


def run_transaction_flow(
    client: SimantikClient,
    flow: dict[str, dict[str, str]],
    bootstrap: dict[str, Any],
    report: RunReport,
) -> None:
    report.title = "SI-MANTIK Alur Transaksi End-to-End"

    pemohon = flow.get("pemohon")
    kepala = flow.get("kepala_unit")
    kasubbag = flow.get("kasubbag_tu")
    gudang = flow.get("admin_gudang")
    penerima = flow.get("penerima") or pemohon

    if not all([pemohon, kepala, kasubbag, gudang, penerima]):
        report.add("system", "Konfigurasi flow", False, "Persona flow tidak lengkap di config/personas.yaml")
        return

    # --- 1. Buat permintaan draft ---
    if not _login_step(client, report, "Pemohon", pemohon["email"], pemohon["password"]):
        return

    create_page = client.get("/transaction/permintaan-barang/create")
    if create_page.status_code != 200:
        report.add("Pemohon", "Buka form permintaan", False, f"Status {create_page.status_code}")
        return

    html = create_page.text
    unit_opts = client.parse_select_options(html, "id_unit_kerja")
    pegawai_opts = client.parse_select_options(html, "id_pemohon")
    barang_opts = client.parse_select_options(html, "detail[0][id_data_barang]")
    if not barang_opts:
        barang_opts = _parse_barang_from_template(html)
    satuan_opts = client.parse_select_options(html, "detail[0][id_satuan]")

    unit_id = str(bootstrap.get("unit_kerja_id") or (unit_opts[0][0] if unit_opts else ""))
    pegawai_id = str(bootstrap.get("pegawai_id") or (pegawai_opts[0][0] if pegawai_opts else ""))
    barang_id = _pick_barang_id(barang_opts, str(bootstrap.get("barang_id") or "") or None)
    satuan_id = str(bootstrap.get("satuan_id") or (satuan_opts[0][0] if satuan_opts else ""))

    if not all([unit_id, pegawai_id, barang_id, satuan_id]):
        report.add(
            "Pemohon",
            "Data master form",
            False,
            f"unit={unit_id} pegawai={pegawai_id} barang={barang_id} satuan={satuan_id}",
        )
        return

    form_data: list[tuple[str, Any]] = [
        ("id_unit_kerja", unit_id),
        ("id_pemohon", pegawai_id),
        ("tanggal_permintaan", time.strftime("%Y-%m-%d")),
        ("tipe_permintaan", "RUTIN"),
        ("jenis_permintaan[]", "PERSEDIAAN"),
        ("keterangan", "Otomasi Python E2E"),
        ("detail[0][tipe_barang]", "master"),
        ("detail[0][id_data_barang]", barang_id),
        ("detail[0][qty_diminta]", "1"),
        ("detail[0][id_satuan]", satuan_id),
    ]

    store = client.post("/transaction/permintaan-barang", form_data, html_for_csrf=html)

    if "/permintaan-barang/create" in store.url:
        errors = client.extract_errors(store.text)
        detail = "; ".join(errors) if errors else f"validasi gagal status={store.status_code}"
        report.add("Pemohon", "Buat permintaan draft", False, detail)
        return

    permintaan_id = _extract_permintaan_id(store.text)
    if not permintaan_id:
        index = client.get("/transaction/permintaan-barang")
        permintaan_id = _extract_permintaan_id(index.text)

    ok = permintaan_id is not None
    report.add(
        "Pemohon",
        "Buat permintaan draft",
        ok,
        f"permintaan_id={permintaan_id or '?'} status={store.status_code}",
    )
    if not ok or not permintaan_id:
        return

    ajukan = client.post(f"/transaction/permintaan-barang/{permintaan_id}/ajukan")
    report.add("Pemohon", "Ajukan permintaan", ajukan.status_code in (200, 302), f"Status {ajukan.status_code}")

    # --- 2. Kepala unit — mengetahui ---
    if not _login_step(client, report, "Kepala Unit", kepala["email"], kepala["password"]):
        return
    approval_html = client.get("/transaction/approval").text
    mengetahui_ids = client.find_form_action_ids(approval_html, "mengetahui")
    if not mengetahui_ids:
        report.add("Kepala Unit", "Temukan approval mengetahui", False, "Tidak ada form mengetahui di inbox")
        return
    log_id = mengetahui_ids[0]
    r = client.post(f"/transaction/approval/{log_id}/mengetahui")
    report.add("Kepala Unit", f"Mengetahui (log #{log_id})", r.status_code in (200, 302), f"Status {r.status_code}")

    # --- 3. Kasubbag TU — verifikasi ---
    if not _login_step(client, report, "Kasubbag TU", kasubbag["email"], kasubbag["password"]):
        return
    log_id = _find_approval_log_with_action(client, "verifikasi")
    if not log_id:
        report.add("Kasubbag TU", "Temukan approval verifikasi", False, "Buka /transaction/approval — tidak ada log step 3 MENUNGGU")
        return
    show = client.get(f"/transaction/approval/{log_id}")
    r = client.post(f"/transaction/approval/{log_id}/verifikasi", html_for_csrf=show.text)
    report.add("Kasubbag TU", f"Verifikasi (log #{log_id})", r.status_code in (200, 302), f"Status {r.status_code}")

    # --- 4. Admin gudang — distribusi + kirim ---
    if not _login_step(client, report, "Admin Gudang", gudang["email"], gudang["password"]):
        return

    gudang_pusat = str(bootstrap.get("gudang_pusat_id") or "")
    gudang_unit = str(bootstrap.get("gudang_unit_id") or "")
    inventory_id = str(bootstrap.get("inventory_id") or "")

    if not all([gudang_pusat, gudang_unit, inventory_id]):
        report.add(
            "Admin Gudang",
            "Data distribusi",
            False,
            "Jalankan ComprehensiveDummySeeder untuk stok/inventory dummy",
        )
        return

    dist_data: list[tuple[str, Any]] = [
        ("id_permintaan", permintaan_id),
        ("tanggal_distribusi", time.strftime("%Y-%m-%d")),
        ("id_gudang_asal", gudang_pusat),
        ("id_gudang_tujuan", gudang_unit),
        ("id_pegawai_pengirim", pegawai_id),
        ("keterangan", "Distribusi otomasi Python"),
        ("detail[0][id_inventory]", inventory_id),
        ("detail[0][qty_distribusi]", "1"),
        ("detail[0][id_satuan]", satuan_id),
        ("detail[0][harga_satuan]", "25000"),
    ]
    dist = client.post("/transaction/distribusi", dist_data)
    distribusi_id = _distribusi_id_from_url(dist.url) or _find_distribusi_for_permintaan(client, permintaan_id)
    if not distribusi_id:
        distribusi_id = _extract_distribusi_id(dist.text)
    ok = dist.status_code in (200, 302) and distribusi_id
    report.add("Admin Gudang", "Buat draft distribusi", ok, f"distribusi_id={distribusi_id or '?'}")
    if not ok or not distribusi_id:
        return

    show_before = client.get(f"/transaction/distribusi/{distribusi_id}")
    kirim = client.post(
        f"/transaction/distribusi/{distribusi_id}/kirim",
        html_for_csrf=show_before.text,
    )
    show_after = client.get(f"/transaction/distribusi/{distribusi_id}")
    kirim_ok = kirim.status_code in (200, 302) and (
        client.page_contains(show_after.text, "selesai")
        or client.page_contains(show_after.text, "dikirim")
    )
    report.add(
        "Admin Gudang",
        "Kirim distribusi",
        kirim_ok,
        f"Status HTTP {kirim.status_code} | distribusi #{distribusi_id}",
    )
    if not kirim_ok:
        return

    # --- 5. Penerima — verifikasi penerimaan ---
    if not _login_step(client, report, "Penerima Unit", penerima["email"], penerima["password"]):
        return
    penerimaan_index = client.get("/transaction/penerimaan-barang")
    penerimaan_id = _extract_penerimaan_id(penerimaan_index.text)
    if not penerimaan_id:
        dist_show = client.get(f"/transaction/distribusi/{distribusi_id}")
        penerimaan_id = _extract_penerimaan_id(dist_show.text)
    if not penerimaan_id:
        report.add("Penerima Unit", "Temukan penerimaan", False, "Belum ada record penerimaan setelah kirim")
        return

    show = client.get(f"/transaction/penerimaan-barang/{penerimaan_id}")
    verify = client.post(
        f"/transaction/penerimaan-barang/{penerimaan_id}/verify",
        [("verifikasi", "sesuai")],
        html_for_csrf=show.text,
    )
    ok = verify.status_code in (200, 302)
    report.add(
        "Penerima Unit",
        f"Verifikasi penerimaan #{penerimaan_id}",
        ok,
        f"Status {verify.status_code}",
    )

    client.logout()
