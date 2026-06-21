# SI-MANTIK E2E Automation (Python)

Script otomasi untuk **mensimulasikan penggunaan sistem per role** — login, akses halaman, dan (opsional) menjalankan alur transaksi lengkap.

## Prasyarat

1. **Server Laravel aktif**
   ```bash
   composer run dev
   # atau: php artisan serve
   ```

2. **Database & akun**
   - Master data: `php artisan migrate:fresh --seed`
   - Stok dummy (alur transaksi): `php artisan db:seed --class=ComprehensiveDummySeeder`
   - **Akun login** diisi di `.env` Laravel (`SIPENI_SUPER_ADMIN_*`, `SIPENI_DEMO_*`) — bukan hardcode seeder
   - **Kredensial E2E** di `scripts/e2e/.env` (`SIMANTIK_EMAIL_*`, `SIMANTIK_PASSWORD_*`)

3. **Python 3.10+**
   ```bash
   cd scripts/e2e
   pip install -r requirements.txt
   copy .env.example .env
   ```

4. **2FA** — untuk otomasi tanpa hambatan, set di `.env` Laravel:
   ```env
   TWO_FACTOR_ENABLED=false
   ```

## Menjalankan

```bash
cd scripts/e2e

# Smoke test: setiap role coba halaman yang boleh & tidak boleh
python run.py smoke

# Alur transaksi: permintaan → approval → distribusi → penerimaan
python run.py flow

# Keduanya + laporan HTML
python run.py all --html

# Visual di browser (login → penerimaan, jendela Chrome terlihat)
pip install playwright
playwright install chromium
python run_browser.py
```

## Apa yang dicek?

### Mode `smoke`
Untuk setiap persona (super admin, admin unit, kepala unit, kasubbag TU, admin gudang, dll.):
- Login berhasil
- GET halaman **diizinkan** → status 200/302
- GET halaman **dilarang** → status 403

Konfigurasi persona: `config/personas.yaml`

### Mode `flow` (HTTP — tanpa browser)

Simulasi multi-user di belakang layar via `requests`. **Tidak ada jendela browser.**

### Mode `browser` (Playwright — jendela terlihat)

Simulasi yang sama tetapi di Chrome: login, isi form, klik tombol — Anda bisa melihat prosesnya.

```bash
# Setup sekali
pip install -r requirements.txt
python -m playwright install chromium

# Jalankan (jendela Chrome muncul)
python run.py browser
# atau
python run_browser.py

# Lebih lambat agar mudah diikuti
python run_browser.py --slow-mo 800 --step-pause 2
```

**Jangan** pakai `--headless` jika ingin melihat browser.

### Tampilan UI tidak ter-style / seperti HTML polos?

Laravel memuat CSS lewat **Vite** (`@vite`). Jika hanya `php artisan serve` tanpa Vite, halaman tampil tanpa Tailwind.

| Solusi | Perintah |
|--------|----------|
| Dev lengkap (disarankan) | `composer run dev` di root project (serve + vite) |
| Tanpa Vite dev | `npm run build` — pakai `public/build` |
| HMR salah IP | File `public/hot` mengarah ke IP lama — hapus atau jalankan ulang `npm run dev` |

`run_browser.py` otomatis fallback ke `public/build` jika Vite dev tidak aktif.

Setelah ubah `vite.config.js`, jalankan ulang `npm run dev` agar `public/hot` berisi `http://127.0.0.1:5173`.

### Mode `flow` (HTTP)
Simulasi multi-user bergantian:
1. **Pemohon** — buat & ajukan permintaan
2. **Kepala Unit** — mengetahui
3. **Kasubbag TU** — verifikasi
4. **Admin Gudang Pusat** — buat distribusi & kirim
5. **Pemohon/Penerima** — verifikasi penerimaan

## Kredensial

**Jangan mengandalkan password di seeder.** Isi `scripts/e2e/.env` (salin dari `.env.example`):

| Variabel | Role |
|----------|------|
| `SIMANTIK_EMAIL_SUPER_ADMIN` | Super Administrator |
| `SIMANTIK_EMAIL_ADMIN_IT` | Admin IT |
| `SIMANTIK_EMAIL_PEMOHON` | Pemohon / Admin Unit |
| `SIMANTIK_EMAIL_KEPALA_UNIT` | Kepala Unit |
| `SIMANTIK_EMAIL_KASUBBAG_TU` | Kasubbag TU |
| `SIMANTIK_EMAIL_ADMIN_GUDANG` | Admin Gudang Pusat |

Pasangan `SIMANTIK_PASSWORD_*` untuk tiap role, atau `SIMANTIK_PASSWORD_ADMIN` / `SIMANTIK_PASSWORD_PEGAWAI` sebagai fallback.

Akun di database Laravel dibuat via `SIPENI_*` di `.env` root (lihat `.env.production.example`).

Jika email role jabatan tidak di-set di `.env`, smoke test masih bisa resolve `kepala-unit.{id}@...` via bootstrap artisan.

## Laporan

```bash
python run.py all --html
# → scripts/e2e/reports/simantik_*.html
```

## Troubleshooting

Panduan lengkap error & solusi: **[docs/E2E_OTOMASI.md](../../docs/E2E_OTOMASI.md)** · Panduan end-user di aplikasi: menu **Panduan Pengguna** (`/panduan`)

| Masalah | Solusi |
|---------|--------|
| Connection refused | Pastikan `php artisan serve` jalan |
| Login gagal | Cocokkan `scripts/e2e/.env` dengan `SIPENI_*` di `.env` Laravel; re-seed jika perlu |
| Flow gagal stok 0 | Jalankan `python run.py flow` saja (bootstrap reset stok), atau `ComprehensiveDummySeeder` |
| Flow gagal setelah smoke (`all`) | Sudah ditangani: bootstrap ulang otomatis sebelum flow |
| 2FA challenge | `TWO_FACTOR_ENABLED=false` |
| Rate limit login | `APP_ENV=local` (throttle dilepas) atau `SIMANTIK_LOGIN_DELAY=13` |
| CSRF token tidak ditemukan | Update `simantik/client.py` terbaru; pastikan layout punya meta CSRF |
| Unicode error di Windows | Update `run.py` / `report.py` terbaru |

## Struktur

```
scripts/e2e/
  run.py                 # CLI utama
  config/personas.yaml   # Role & route checklist
  simantik/
    client.py            # Session + CSRF
    bootstrap.py         # Ambil ID dari artisan
    scenarios/smoke.py
    scenarios/transaction_flow.py
```
