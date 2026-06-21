"""Alur transaksi end-to-end di browser (terlihat oleh user)."""

from __future__ import annotations

import os
import re
import time
from typing import Any

from simantik.browser.client import SimantikBrowser
from simantik.report import RunReport

KETERANGAN_PERMINTAAN = "Otomasi Python E2E"
KETERANGAN_DISTRIBUSI = "Distribusi otomasi Python"


def _record(report: RunReport, persona: str, step: str, ok: bool, detail: str) -> None:
    report.add(persona, step, ok, detail)


def _login_as(
    browser: SimantikBrowser,
    report: RunReport,
    label: str,
    email: str,
    password: str,
) -> bool:
    browser.logout(persona=label)
    delay = float(os.getenv("SIMANTIK_LOGIN_DELAY", "2"))
    if delay > 0:
        time.sleep(min(delay, 5))
    try:
        browser.login(email, password, persona=label)
        _record(report, label, "Login", True, f"Login berhasil sebagai {email}")
        return True
    except Exception as exc:
        _record(report, label, "Login", False, str(exc))
        return False


def _find_approval_log_with_action(browser: SimantikBrowser, action_fragment: str) -> str | None:
    """Cari log approval yang punya form aksi di halaman detail."""
    assert browser.page is not None
    page = browser.page
    page.goto(browser.url("/transaction/approval"))
    browser.pause(0.8)
    log_ids = list(
        dict.fromkeys(re.findall(r"/transaction/approval/(\d+)", page.content()))
    )
    for log_id in log_ids:
        page.goto(browser.url(f"/transaction/approval/{log_id}"))
        browser.pause(0.5)
        if page.locator(f'form[action*="{action_fragment}"]').count() > 0:
            return log_id
        if action_fragment == "mengetahui" and page.locator('form[action*="/mengetahui"]').count():
            return log_id
        if action_fragment == "verifikasi" and (
            page.locator("#formVerifikasi").count()
            or page.locator('button:has-text("Verifikasi")').count()
        ):
            return log_id
    return None


def _ensure_distribusi_pegawai_pengirim(
    browser: SimantikBrowser,
    distribusi_id: str,
    pegawai_id: str,
) -> None:
    page = browser.page
    assert page is not None
    if not pegawai_id:
        return
    page.goto(browser.url(f"/transaction/distribusi/{distribusi_id}"))
    browser.pause(0.5)
    pengirim = page.locator("dt:has-text('Pegawai Pengirim') + dd").first
    if pengirim.count() and (pengirim.inner_text() or "").strip() not in ("", "-"):
        return
    browser.show_step("Admin Gudang", "Isi pegawai pengirim di Edit distribusi")
    page.goto(browser.url(f"/transaction/distribusi/{distribusi_id}/edit"))
    browser.pause(0.8)
    browser.set_select_value("#id_pegawai_pengirim", pegawai_id)
    browser.click_submit_and_wait("#formDistribusi")
    browser.pause(0.5)


def _fill_permintaan_form(
    browser: SimantikBrowser,
    *,
    unit_id: str,
    pegawai_id: str,
    barang_id: str,
    satuan_id: str,
) -> None:
    page = browser.page
    assert page is not None

    browser.show_step("Pemohon", "Membuka form Tambah Permintaan Barang")
    browser.goto("/transaction/permintaan-barang/create")
    browser.pause(1.2)

    # Pastikan tipe RUTIN terpilih (default, tapi eksplisit untuk validateForm)
    rutin = page.locator('input[name="tipe_permintaan"][value="RUTIN"]')
    if rutin.count():
        rutin.check()

    if unit_id:
        browser.set_select_value("#id_unit_kerja", unit_id)
    if pegawai_id:
        browser.set_select_value("#id_pemohon", pegawai_id)

    browser.show_step("Pemohon", "Pilih jenis PERSEDIAAN")
    persediaan_cb = page.locator('input[name="jenis_permintaan[]"][value="PERSEDIAAN"]')
    if persediaan_cb.count():
        persediaan_cb.check()
        page.evaluate(
            """() => {
                document.querySelectorAll('input[name="jenis_permintaan[]"]').forEach(cb => {
                    cb.dispatchEvent(new Event('change', { bubbles: true }));
                });
                if (typeof filterDataBarangByJenisPermintaan === 'function') {
                    filterDataBarangByJenisPermintaan();
                }
            }"""
        )
        browser.pause(1.0)

    browser.show_step("Pemohon", "Isi detail barang")
    browser.wait_for_item_row()

    barang_sel = ".item-row .select-data-barang"
    if barang_id:
        try:
            browser.wait_for_select_option(barang_sel, barang_id, timeout_ms=15000)
        except Exception:
            pass
        browser.set_select_value(barang_sel, barang_id)
        page.evaluate(
            """(barangId) => {
                const row = document.querySelector('.item-row');
                const select = row?.querySelector('.select-data-barang');
                if (!select) return;
                const opt = select.querySelector(`option[value="${barangId}"]`);
                const satuanId = opt?.getAttribute('data-satuan');
                const satuan = row.querySelector('.field-satuan');
                if (satuanId && satuan && typeof setPermintaanSatuanValue === 'function') {
                    setPermintaanSatuanValue(satuan, satuanId);
                } else if (satuanId && satuan) {
                    satuan.value = satuanId;
                    satuan.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }""",
            barang_id,
        )
    elif satuan_id:
        browser.set_select_value(".item-row .field-satuan", satuan_id)

    page.locator(".item-row .qty-input").first.fill("1")
    page.fill("#keterangan", KETERANGAN_PERMINTAAN)


def run_browser_flow(
    browser: SimantikBrowser,
    flow: dict[str, dict[str, str]],
    bootstrap: dict[str, Any],
    report: RunReport,
) -> None:
    report.title = "SI-MANTIK Browser — Alur Transaksi (Visual)"
    page = browser.page
    assert page is not None

    pemohon = flow.get("pemohon")
    kepala = flow.get("kepala_unit")
    kasubbag = flow.get("kasubbag_tu")
    gudang = flow.get("admin_gudang")
    penerima = flow.get("penerima") or pemohon

    if not all([pemohon, kepala, kasubbag, gudang, penerima]):
        _record(report, "system", "Konfigurasi", False, "Persona flow tidak lengkap")
        return

    unit_id = str(bootstrap.get("unit_kerja_id") or "")
    pegawai_id = str(bootstrap.get("pegawai_id") or "")
    barang_id = str(bootstrap.get("barang_id") or "")
    satuan_id = str(bootstrap.get("satuan_id") or "")
    gudang_pusat = str(bootstrap.get("gudang_pusat_id") or "")
    gudang_unit = str(bootstrap.get("gudang_unit_id") or "")
    inventory_id = str(bootstrap.get("inventory_id") or "")

    # --- 1. Pemohon: buat & ajukan permintaan ---
    if not _login_as(browser, report, "Pemohon", pemohon["email"], pemohon["password"]):
        return

    _fill_permintaan_form(
        browser,
        unit_id=unit_id,
        pegawai_id=pegawai_id,
        barang_id=barang_id,
        satuan_id=satuan_id,
    )

    browser.show_step("Pemohon", "Menyimpan draft permintaan")
    still_on_create = True
    try:
        browser.click_submit_and_wait(
            "#formPermintaan",
            url_pattern=r"/transaction/permintaan-barang/?(\?.*)?$",
        )
        still_on_create = "/create" in page.url
    except Exception:
        still_on_create = "/create" in page.url

    permintaan_id = browser.extract_id_from_url(r"/transaction/permintaan-barang/(\d+)")
    if not permintaan_id and not still_on_create:
        page.goto(browser.url("/transaction/permintaan-barang"))
        browser.pause(0.8)
        for link in page.locator('a[href*="/transaction/permintaan-barang/"]').all():
            href = link.get_attribute("href") or ""
            if "/create" in href:
                continue
            row_text = link.evaluate("el => el.closest('tr')?.textContent || el.textContent || ''")
            if KETERANGAN_PERMINTAAN in (row_text or ""):
                m = re.search(r"/transaction/permintaan-barang/(\d+)", href)
                if m:
                    permintaan_id = m.group(1)
                    break
        if not permintaan_id:
            for link in page.locator('a[href*="/transaction/permintaan-barang/"]').all():
                href = link.get_attribute("href") or ""
                if "/create" in href:
                    continue
                m = re.search(r"/transaction/permintaan-barang/(\d+)", href)
                if m:
                    permintaan_id = m.group(1)
                    break

    ok = permintaan_id is not None
    if not ok and still_on_create:
        errors = browser.visible_errors()
        detail = "; ".join(errors) if errors else "Form tidak tersimpan — cek validasi di layar"
        _record(report, "Pemohon", "Buat permintaan draft", False, detail)
        return
    _record(report, "Pemohon", "Buat permintaan draft", ok, f"permintaan_id={permintaan_id or '?'}")
    if not ok:
        return

    browser.show_step("Pemohon", "Mengajukan permintaan")
    page.goto(browser.url(f"/transaction/permintaan-barang/{permintaan_id}"))
    browser.pause(0.8)
    ajukan_btn = 'form[action*="/ajukan"] button[type="submit"]'
    if page.locator(ajukan_btn).count():
        try:
            browser.click_confirm_submit(ajukan_btn, wait_navigation=True)
        except Exception:
            browser.click_confirm_submit(ajukan_btn)
        _record(report, "Pemohon", "Ajukan permintaan", True, "Permintaan diajukan")
    else:
        _record(report, "Pemohon", "Ajukan permintaan", False, "Tombol ajukan tidak ditemukan")
        return
    browser.pause()

    # --- 2. Kepala Unit: mengetahui ---
    if not _login_as(browser, report, "Kepala Unit", kepala["email"], kepala["password"]):
        return

    browser.show_step("Kepala Unit", "Approval — Mengetahui")
    log_id = _find_approval_log_with_action(browser, "mengetahui")
    if not log_id:
        mengetahui_inline = page.locator('form[action*="/mengetahui"] button[type="submit"]').first
        if mengetahui_inline.count():
            browser.click_confirm_submit('form[action*="/mengetahui"] button[type="submit"]')
            _record(report, "Kepala Unit", "Mengetahui", True, "Dari inbox approval")
        else:
            _record(report, "Kepala Unit", "Mengetahui", False, "Tidak ada aksi mengetahui")
            return
    else:
        browser.click_confirm_submit('form[action*="/mengetahui"] button[type="submit"]')
        _record(report, "Kepala Unit", f"Mengetahui (log #{log_id})", True, "OK")
    browser.pause()

    # --- 3. Kasubbag TU: verifikasi ---
    if not _login_as(browser, report, "Kasubbag TU", kasubbag["email"], kasubbag["password"]):
        return

    browser.show_step("Kasubbag TU", "Approval — Verifikasi & disposisi")
    log_id = _find_approval_log_with_action(browser, "verifikasi")
    if not log_id:
        _record(report, "Kasubbag TU", "Verifikasi", False, "Form verifikasi tidak ditemukan")
        return
    verifikasi_btn = '#formVerifikasi button[type="submit"], button:has-text("Verifikasi")'
    browser.click_confirm_submit(verifikasi_btn)
    _record(report, "Kasubbag TU", f"Verifikasi (log #{log_id})", True, "OK")
    browser.pause()

    # --- 4. Admin Gudang: distribusi + kirim (update stok keluar) ---
    if not _login_as(browser, report, "Admin Gudang", gudang["email"], gudang["password"]):
        return

    if not all([gudang_pusat, gudang_unit, inventory_id]):
        _record(report, "Admin Gudang", "Data distribusi", False, "Bootstrap tidak lengkap")
        return

    browser.show_step("Admin Gudang", "Buat draft distribusi (SBBK)")
    page.goto(browser.url("/transaction/distribusi/create"))
    browser.pause(1.0)

    browser.set_select_value("#id_permintaan", permintaan_id)
    browser.set_select_value("#id_gudang_asal", gudang_pusat)
    browser.pause(1.5)  # tunggu AJAX inventory
    browser.set_select_value("#id_gudang_tujuan", gudang_unit)
    if pegawai_id:
        browser.set_select_value("#id_pegawai_pengirim", pegawai_id)

    browser.wait_for_item_row()
    if page.locator('[name*="[id_inventory]"]').count():
        browser.set_select_value('[name*="[id_inventory]"]', inventory_id)
    page.locator('[name*="[qty_distribusi]"]').first.fill("1")
    page.locator('[name*="[harga_satuan]"]').first.fill("25000")
    page.fill("#keterangan", KETERANGAN_DISTRIBUSI)

    browser.show_step("Admin Gudang", "Simpan draft distribusi")
    try:
        browser.click_submit_and_wait("#formDistribusi")
    except Exception:
        pass
    browser.pause()

    distribusi_id = None
    page.goto(browser.url("/transaction/distribusi"))
    browser.pause(0.8)
    for link in page.locator('a[href*="/transaction/distribusi/"]').all():
        href = link.get_attribute("href") or ""
        m = re.search(r"/transaction/distribusi/(\d+)", href)
        if not m or "/edit" in href:
            continue
        did = m.group(1)
        page.goto(browser.url(f"/transaction/distribusi/{did}"))
        content = page.content()
        if KETERANGAN_DISTRIBUSI in content and permintaan_id in content:
            distribusi_id = did
            break

    ok = distribusi_id is not None
    _record(report, "Admin Gudang", "Buat draft distribusi", ok, f"distribusi_id={distribusi_id or '?'}")
    if not ok:
        return

    browser.show_step("Admin Gudang", "Kirim distribusi — stok gudang asal berkurang")
    _ensure_distribusi_pegawai_pengirim(browser, distribusi_id, pegawai_id)
    page.goto(browser.url(f"/transaction/distribusi/{distribusi_id}"))
    browser.pause(0.8)

    proses_btn = 'form[action*="/proses"] button[type="submit"]'
    if page.locator(proses_btn).count():
        browser.show_step("Admin Gudang", "Proses distribusi (draft → diproses)")
        browser.click_confirm_submit(proses_btn, wait_navigation=True)
        browser.pause(0.8)

    kirim_btn = 'form[action*="/kirim"] button[type="submit"]'
    if page.locator(kirim_btn).count():
        try:
            browser.click_confirm_submit(kirim_btn, wait_navigation=True)
        except Exception:
            browser.click_confirm_submit(kirim_btn)
        browser.pause()
        page.goto(browser.url(f"/transaction/distribusi/{distribusi_id}"))
        browser.pause(0.5)
        body = page.content().lower()
        kirim_ok = (
            "dikirim" in body
            or "selesai" in body
            or "berhasil dikirim" in body
            or page.locator(kirim_btn).count() == 0
        )
        _record(report, "Admin Gudang", "Kirim distribusi", kirim_ok, f"distribusi #{distribusi_id}")
        if not kirim_ok:
            return
    else:
        _record(report, "Admin Gudang", "Kirim distribusi", False, "Tombol kirim tidak ditemukan")
        return

    # --- 5. Penerima: verifikasi penerimaan (stok masuk unit) ---
    if not _login_as(browser, report, "Penerima Unit", penerima["email"], penerima["password"]):
        return

    browser.show_step("Penerima Unit", "Verifikasi penerimaan barang")
    page.goto(browser.url("/transaction/penerimaan-barang"))
    browser.pause(0.8)

    penerimaan_id = None
    for link in page.locator('a[href*="/transaction/penerimaan-barang/"]').all():
        href = link.get_attribute("href") or ""
        m = re.search(r"/transaction/penerimaan-barang/(\d+)", href)
        if m:
            penerimaan_id = m.group(1)
            page.goto(browser.url(f"/transaction/penerimaan-barang/{penerimaan_id}"))
            break

    if not penerimaan_id:
        _record(report, "Penerima Unit", "Temukan penerimaan", False, "Belum ada record penerimaan")
        return

    sesuai = page.locator('input[name="verifikasi"][value="sesuai"]')
    if sesuai.count():
        sesuai.check()
        page.locator('form[action*="/verify"] button[type="submit"]').click()
        browser.confirm_modal_if_open()
        page.wait_for_load_state("domcontentloaded")
        _record(
            report,
            "Penerima Unit",
            f"Verifikasi penerimaan #{penerimaan_id}",
            True,
            "Barang diterima — stok unit diperbarui",
        )
    else:
        _record(report, "Penerima Unit", "Verifikasi penerimaan", False, "Form verifikasi tidak ada")
        return
    browser.pause()

    # --- 6. Lihat kartu stok (pencatatan stok) ---
    browser.show_step("Penerima Unit", "Membuka Kartu Stok untuk cek pencatatan")
    page.goto(browser.url("/inventory/data-stock"))
    browser.pause(2.0)
    _record(report, "Penerima Unit", "Lihat data stok", True, "Halaman /inventory/data-stock dibuka")

    browser.show_step("Selesai", "Alur lengkap: permintaan → approval → distribusi → penerimaan → stok")
    browser.pause(3)
    browser.logout(persona="Penerima Unit")
