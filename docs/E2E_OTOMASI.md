# Dokumentasi Otomasi E2E SI-MANTIK

Dokumen ini merangkum cara menjalankan otomasi E2E (Python + PHPUnit), hasil pengecekan terakhir, serta katalog error yang pernah muncul beserta solusinya.

**Tanggal pengecekan:** 21 Juni 2026  
**Environment:** Windows 10, Laragon, PHP 8.3, Python 3.13, MySQL `simantik`

---

## Ringkasan hasil terakhir

| Suite | Perintah | Hasil | Durasi |
|-------|----------|-------|--------|
| Smoke per role | `python run.py smoke` | **94/94 lulus** | ~4 menit |
| Alur transaksi | `python run.py flow` | **12/12 lulus** | ~1,5 menit |
| Keduanya + HTML | `python run.py all --html` | **106/106 lulus** (exit 0) | ~6 menit |
| PHPUnit E2E | `php artisan test --filter=PermintaanBarangEndToEndTest` | **2/2 lulus** | ~6 detik |

Laporan HTML terbaru:

- `scripts/e2e/reports/simantik_smoke_20260621_152220.html`
- `scripts/e2e/reports/simantik_flow_20260621_152220.html`

---

## Prasyarat

### 1. Server Laravel aktif

```powershell
cd D:\laragon\www\sipeni_v1
composer run dev
# atau: php artisan serve
```

Pastikan `http://127.0.0.1:8000/login` merespons **200**.

### 2. Database ter-seed

```powershell
php artisan migrate:fresh --seed
php artisan db:seed --class=ComprehensiveDummySeeder
```

`ComprehensiveDummySeeder` menyediakan barang/inventory dummy (`BRG-DMY-001`, `INV-DMY-001`) yang dipakai alur transaksi.

### 3. Python E2E

```powershell
cd scripts\e2e
pip install -r requirements.txt
copy .env.example .env
```

Isi minimal `.env`:

```env
SIMANTIK_BASE_URL=http://127.0.0.1:8000
SIMANTIK_PROJECT_ROOT=../..
SIMANTIK_LOGIN_DELAY=13
SIMANTIK_INSECURE=true
```

### 4. Rekomendasi `.env` Laravel (dev/otomasi)

```env
APP_ENV=local
TWO_FACTOR_ENABLED=false
```

- `APP_ENV=local` → throttle login dilepas (lihat bagian error #3).
- `TWO_FACTOR_ENABLED=false` → login tidak diarahkan ke halaman 2FA.

---

## Cara menjalankan

```powershell
cd scripts\e2e

# Smoke: login + cek halaman per 13 role
python run.py smoke

# Flow: permintaan → approval → distribusi → penerimaan
python run.py flow

# Keduanya + laporan HTML
python run.py all --html
```

PHPUnit (di root project):

```powershell
php artisan config:clear
php artisan test --filter=PermintaanBarangEndToEndTest
```

---

## Apa yang dicek?

### Mode `smoke`

Untuk setiap persona di `config/personas.yaml`:

1. Login berhasil
2. GET halaman **diizinkan** → HTTP 200 atau 302
3. GET halaman **dilarang** → HTTP 403

Persona yang diuji: Super Administrator, Admin IT, Admin Unit, Kepala Unit, Kasubbag TU, Kepala Pusat, Admin Gudang (pusat/persediaan/aset/farmasi), Perencana, Pengadaan, Keuangan.

### Mode `flow`

Simulasi multi-user bergantian:

| Langkah | User | Aksi |
|---------|------|------|
| 1 | Pemohon (`staf-adm.gudang-unit@sipeni.local`) | Buat & ajukan permintaan |
| 2 | Kepala Unit | Approval mengetahui |
| 3 | Kasubbag TU | Verifikasi |
| 4 | Admin Gudang Pusat | Buat distribusi & kirim |
| 5 | Pemohon/Penerima | Verifikasi penerimaan |

Bootstrap otomatis (via `php artisan tinker`):

- Resolve email jabatan & ID master data
- Cleanup transaksi otomasi sebelumnya (`keterangan` mengandung `Otomasi Python`)
- Reset stok dummy ke **100** di semua gudang pusat persediaan
- Buat gudang unit E2E jika unit pemohon belum punya gudang persediaan
- Pastikan permission `transaction.penerimaan-barang.verify` pada role `admin_unit`

Pada mode `all`, bootstrap dijalankan **dua kali**: sekali di awal, sekali lagi sebelum flow agar stok tidak habis setelah smoke.

---

## Kredensial (environment)

Kredensial **tidak di-hardcode** di skrip. Isi `scripts/e2e/.env` (salin dari `.env.example`) agar cocok dengan akun di database Laravel:

| Sumber Laravel | Variabel E2E |
|----------------|--------------|
| `SIPENI_SUPER_ADMIN_*` | `SIMANTIK_EMAIL_SUPER_ADMIN` / `SIMANTIK_PASSWORD_SUPER_ADMIN` |
| `SIPENI_ADMIN_IT_*` | `SIMANTIK_EMAIL_ADMIN_IT` / `SIMANTIK_PASSWORD_ADMIN_IT` |
| `SIPENI_DEMO_PEGAWAI_PASSWORD` + email demo | `SIMANTIK_EMAIL_PEMOHON`, `SIMANTIK_PASSWORD_PEMOHON`, dll. |

Contoh nilai dev (`.env.example`):

| Role | Email contoh | Password contoh |
|------|--------------|-----------------|
| Super Admin | `pusdatinppkp@gmail.com` | `Admin@12345678` |
| Admin IT | `admin-it@sipeni.local` | `Admin@12345678` |
| Admin Unit / Pemohon | `staf-adm.gudang-unit@sipeni.local` | `Pegawai@123456` |
| Kepala Unit | `kepala-unit.{id_jabatan}@sipeni.local` | `Pegawai@123456` |
| Kasubbag TU | `kasubbag-tu.{id_jabatan}@sipeni.local` | `Pegawai@123456` |
| Admin Gudang Pusat | `staf-adm.gudang-pusat@sipeni.local` | `Pegawai@123456` |
| Keuangan | `keuanganbendahara.{id}@sipeni.local` | `Pegawai@123456` |

`{id}` di-resolve otomatis dari database (lihat `emails_by_jabatan` di output bootstrap).

---

## Katalog error & solusi

### ERR-01 — CSRF token tidak ditemukan di halaman

```
RuntimeError: CSRF token tidak ditemukan di halaman.
  client.py → login() → refresh_csrf()
```

**Penyebab:** Setelah login sukses, halaman redirect kadang tidak memuat `<meta name="csrf-token">`. Client memaksa refresh dan melempar exception.

**Solusi (sudah diterapkan):**

- `client.py`: tidak memaksa `refresh_csrf()` setelah login; token diambil jika ada di response.
- `logout()` mengambil token dari `/login` secara langsung.
- `smoke.py`: menangkap `RuntimeError` pada langkah login.

**Pencegahan:** Pastikan layout `login.blade.php` dan `app.blade.php` tetap menyertakan meta CSRF.

---

### ERR-02 — UnicodeEncodeError di konsol Windows

```
UnicodeEncodeError: 'charmap' codec can't encode character '\u2192'
```

**Penyebab:** Karakter panah `→` di output laporan tidak didukung encoding konsol Windows (cp1252).

**Solusi (sudah diterapkan):**

- Pesan HTTP memakai `->` (ASCII).
- `report.py`: fungsi `_console_text()` + `sys.stdout.reconfigure(encoding="utf-8")` di `run.py`.

---

### ERR-03 — Rate limit login (HTTP 429)

```
Rate limit login (throttle:5,1). Tunggu 1 menit.
```

**Penyebab:** Route `POST /login` dibatasi 5 percobaan per menit. Smoke menguji 13 role = banyak login berturut-turut.

**Solusi:**

| Lingkungan | Tindakan |
|------------|----------|
| `local` / `testing` | Throttle dilepas di `routes/web.php` (sudah diterapkan) |
| Production / staging | Set `SIMANTIK_LOGIN_DELAY=13` di `scripts/e2e/.env` (~4 menit total smoke) |
| Terkena limit | Tunggu 60 detik lalu jalankan ulang |

---

### ERR-04 — Login HTTP 500 (role gudang)

```
Server error saat login (500)
```

**Penyebab (log Laravel):**

```
Undefined variable $pemakaianQueue
DashboardController.php:353
```

Dashboard workspace pengurus barang mereferensikan `$pemakaianQueue` yang tidak diinisialisasi saat fitur pemakaian barang nonaktif.

**Solusi (sudah diterapkan):** Inisialisasi `$pemakaianQueue = collect()` di `DashboardController.php`.

**Catatan:** Aktifkan `FEATURE_PEMAKAIAN_BARANG=true` jika modul pemakaian dipakai penuh.

---

### ERR-05 — Stok gudang pusat 0 saat buat permintaan

```
Jumlah yang diminta (1) melebihi stock di Gudang Persediaan (Pusat) (0)
```

**Penyebab:**

1. Ada **dua** gudang pusat persediaan di DB (`id_gudang` 3 dan 22). Validasi stok permintaan memakai gudang **pertama** (`id=3`) via `DataStock::getStockGudangPusat()`, sementara stok/inventory dummy (`INV-DMY-001`) bisa berada di gudang lain (`id=22`).
2. Flow distribusi memakai `gudang_pusat_id` dari bootstrap (dulu selalu `id=3`) tetapi inventory ada di gudang berbeda → kirim gagal: `Data stok gudang tidak ditemukan`.
3. Mode `all`: bootstrap awal mengisi stok, tetapi run flow sebelumnya bisa mengosongkan stok sebelum langkah flow.

**Solusi (sudah diterapkan):**

- Bootstrap mengisi stok **semua** gudang pusat persediaan + gudang tempat inventory dummy berada.
- `gudang_pusat_id` di bootstrap = `id_gudang` dari record `INV-DMY-001` (bukan selalu gudang pertama).
- Reset `qty_input` inventory dummy ke **100** setiap bootstrap.
- Reset stok dilakukan **setelah** cleanup transaksi otomasi.
- Mode `all` menjalankan bootstrap ulang sebelum flow (`run.py`).

**Verifikasi manual:**

```powershell
php artisan tinker --execute "echo \App\Models\DataStock::getStockGudangPusat(1,'PERSEDIAAN');"
# Harus > 0 sebelum flow
```

---

### ERR-06 — Duplikat nomor permintaan / penerimaan

```
Duplicate entry 'PMT/2026/0001' ...
Duplicate entry 'TERIMA/2026/0001' ...
```

**Penyebab:** Sisa data dari run otomasi sebelumnya tidak terhapus; generator nomor mengulang urutan.

**Solusi (sudah diterapkan):** Bootstrap menghapus rantai:

`permintaan (Otomasi Python%)` → `distribusi` → `penerimaan` beserta detail & approval log.

---

### ERR-07 — Kirim distribusi gagal / penerimaan tidak ditemukan

| Gejala | Penyebab | Solusi |
|--------|----------|--------|
| Kirim HTTP 500, duplikat `TERIMA/2026/0001` | Auto-create penerimaan bentrok nomor | Cleanup bootstrap (ERR-06) |
| `distribusi_id` salah (mis. #2 bukan draft baru) | Redirect ke index, parser ambil ID lama | `_find_distribusi_for_permintaan()` di `transaction_flow.py` |
| Penerimaan tidak muncul di index pemohon | `id_unit_kerja` penerimaan ≠ unit pemohon; gudang tujuan salah | Bootstrap buat gudang unit untuk unit pemohon (`Gudang Unit E2E`) |
| Verifikasi penerimaan HTTP 403 | Role `admin_unit` punya `update` tapi route `verify` butuh permission terpisah | Bootstrap memberi `transaction.penerimaan-barang.verify` ke role `admin_unit` |

---

### ERR-08 — Smoke: ekspektasi halaman tidak sesuai RBAC

| Kasus | Penjelasan |
|-------|------------|
| `/login` diharapkan 403 | Saat sudah login, `/login` tetap 200 (bukan forbidden) → dihapus dari `forbidden_get` |
| Admin IT akses transaksi | Role `admin` hanya punya `reports.*` → ekspektasi diperbarui di `personas.yaml` |
| Perencana `/planning/rku` 403 | User demo belum punya modul planning di DB → dipindah ke `forbidden_get` (sesuai RBAC aktual) |
| Email Keuangan salah | `Str::slug('Keuangan/Bendahara')` → `keuanganbendahara.{id}@sipeni.local` (tanpa strip) |

---

### ERR-09 — Tabel `notifications` tidak ada

```
Table 'simantik.notifications' doesn't exist
```

**Penyebab:** Migrasi notifikasi belum dijalankan di database dev.

**Solusi:**

```powershell
php artisan migrate
```

---

### ERR-10 — Connection refused / server down

```
Connection refused / Status preflight gagal
```

**Solusi:** Jalankan `composer run dev` atau `php artisan serve`, cek `SIMANTIK_BASE_URL` di `scripts/e2e/.env`.

---

## Known limitations (bukan bug otomasi)

1. **Perencana tanpa akses RKU** — RBAC aktual di DB tidak memberi `planning.*` ke `staf-adm.perencana@sipeni.local`. Perbaikan jangka panjang: jalankan `StandardRolePermissionV2Seeder` + assign modul `planning` ke user.

2. **Permission verify via bootstrap** — Bootstrap memberi permission `verify` ke `admin_unit` agar flow lulus di dev. Di production, pastikan seeder RBAC sudah sinkron (`php artisan permission:sync-routes`).

3. **Durasi smoke ~4 menit** — Karena `SIMANTIK_LOGIN_DELAY=13` antar persona. Bisa dikurangi di `local` karena throttle sudah dilepas.

4. **Dua gudang pusat persediaan** — `getStockGudangPusat()` hanya membaca gudang pertama (`id` terkecil). Pertimbangkan refactor agar menjumlahkan semua gudang pusat per kategori (issue data model).

---

## Struktur file otomasi

```
scripts/e2e/
  run.py                          # CLI: smoke | flow | all
  .env / .env.example
  config/personas.yaml            # Role, route checklist, flow users
  simantik/
    client.py                     # Session HTTP + CSRF + login
    bootstrap.py                  # Artisan tinker: ID, stok, cleanup
    personas.py                   # Loader YAML + resolve email
    report.py                     # Konsol + HTML
    scenarios/
      smoke.py
      transaction_flow.py
  reports/                        # Output HTML

tests/Feature/
  PermintaanBarangEndToEndTest.php  # PHPUnit E2E (in-memory SQLite)
```

---

## Checklist sebelum CI / demo

- [ ] `php artisan serve` atau `composer run dev` aktif
- [ ] `php artisan migrate` terbaru
- [ ] `ComprehensiveDummySeeder` sudah dijalankan
- [ ] `TWO_FACTOR_ENABLED=false` (dev)
- [ ] `scripts/e2e/.env` ada dan `SIMANTIK_BASE_URL` benar
- [ ] `python run.py all --html` → exit code **0**
- [ ] `php artisan test --filter=PermintaanBarangEndToEndTest` → **2 passed**

---

## Referensi

- Panduan singkat: `scripts/e2e/README.md`
- Konfigurasi persona: `scripts/e2e/config/personas.yaml`
- Arsitektur RBAC: `AGENTS.md`
