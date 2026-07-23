# Laporan Audit Sistem SI-MANTIK

**Tanggal audit:** 21 Juni 2026  
**Versi aplikasi:** sipeni_v1 (Laravel 13 + Blade + Tailwind v4)  
**Metode:** Review statis kode sumber, konfigurasi, routes, middleware, tests, dan dokumentasi — dibandingkan dengan panduan pengguna, alur transaksi, dan roadmap RBAC.

---

## Ringkasan Eksekutif

SI-MANTIK adalah sistem manajemen aset dan persediaan yang **sudah layak operasional** untuk alur inti **permintaan → approval → disposisi → distribusi (SBBK) → penerimaan → retur**, modul inventory/aset, RKU, dan pemeliharaan. Fondasi RBAC (Spatie, permission = nama route, wildcard) dan audit logging sudah cukup matang.

Namun audit ini menemukan **celah keamanan yang perlu segera ditangani** (eskalasi role super admin, API helper tanpa otorisasi, login tanpa rate limit), **fitur yang belum lengkap atau tidak selaras dengan dokumentasi** (notifikasi, pemakaian barang, handoff pengadaan), serta **keterbatasan operasional produksi** (tanpa CI/CD, backup otomatis, dan test end-to-end alur transaksi). *(Catatan: Compile SBBK tersembunyi sudah by design — digabung ke Distribusi.)*

| Area | Skor kesiapan | Risiko utama |
|------|---------------|--------------|
| **Keamanan** | Sedang | Eskalasi privilege, IDOR API helper, brute-force login |
| **Kelengkapan fitur** | Sedang–Baik | Modul disabled, drift dokumen–kode, notifikasi kosong |
| **RBAC & otorisasi** | Baik (Fase 1–2) | Fase 3 belum; fallback `user_modules` masih aktif |
| **Testing** | Rendah–Sedang | ~80 test; alur transaksi penuh belum teruji otomatis |
| **Operasional / DevOps** | Rendah | Tanpa CI/CD, backup, hardening produksi |

**Rekomendasi prioritas:** Perbaiki 5 isu keamanan Critical/High dalam 1–2 sprint, lalu tutup gap workflow pengadaan→distribusi dan tambahkan pipeline CI + backup sebelum go-live produksi penuh.

---

## 1. Metodologi Audit

### Ruang lingkup

1. **Keamanan** — autentikasi, otorisasi, CSRF/XSS/SQLi, upload file, exposure data, rate limiting, audit log
2. **Fitur** — modul sidebar vs routes, alur bisnis vs enum status, integrasi (email, queue, notifikasi)
3. **Infrastruktur** — tests, Docker, logging, backup, performa, dependensi
4. **Dokumentasi** — keselarasan panduan pengguna vs implementasi aktual

### Referensi utama

| Sumber | Path |
|--------|------|
| Panduan pengguna | `docs/panduan-pengguna/` |
| Alur transaksi | `docs/ALUR_TRANSAKSI.md` |
| RBAC Fase 1–2 | `docs/rbac-phase1/`, `docs/rbac-phase2/` |
| Routes | `routes/web.php` |
| Tests | `tests/Feature/` (14 file, ~80 metode) |

### Batasan

Audit ini **tidak** mencakup penetration testing dinamis, scan CVE dependensi otomatis, atau review konfigurasi `.env` produksi aktual. Disarankan dilakukan sebelum deployment final.

---

## 2. Temuan Keamanan

### 2.1 Ringkasan per severity

| Severity | Jumlah | Contoh |
|----------|--------|--------|
| **Critical** | 1 | Eskalasi role `super_administrator` via admin user |
| **High** | 6 | API helper tanpa permission, IDOR, login tanpa throttle, user nonaktif bisa login |
| **Medium** | 12 | GET logout CSRF, XSS print template, upload public disk, error message ke user |
| **Low** | 8 | Tanpa 2FA, seed password lemah, health endpoint publik |

### 2.2 Critical & High — detail

#### CRIT-01: Eskalasi privilege ke Super Administrator

**Deskripsi:** User dengan permission `admin.users.update` dapat menetapkan role `super_administrator` ke akun mana pun. Validasi hanya `exists:roles,id` tanpa allowlist role terlindungi.

**Lokasi:** `app/Http/Controllers/Admin/UserController.php` (validasi `role_ids.*`)

**Dampak:** Admin IT atau operator user management dapat memperoleh bypass penuh seluruh sistem.

**Rekomendasi:** Hanya `super_administrator` yang boleh menetapkan role sistem (`super_administrator`, dll.). Tambahkan guard di `SuperAdminGuard` atau policy khusus.

---

#### HIGH-01: API helper tanpa middleware `role`

**Deskripsi:** Route lookup master dan JSON helper hanya memerlukan login (`auth`), **tanpa** pengecekan permission route name. Route `api.*` sengaja dikecualikan dari sync permission.

**Lokasi:**
- `routes/web.php` L97–100 (`api/master/*`)
- `routes/web.php` L162–181 (`api/*` — gudang, permintaan, stock)
- `app/Console/Commands/SyncPermissionsFromRoutes.php` (skip `api.*`)

**Dampak:** Pegawai unit A dapat mengakses data gudang/inventory/permintaan unit B via ID.

**Rekomendasi:** Tambahkan `middleware(['role'])` **atau** validasi scope `UserScope` di setiap controller helper; daftarkan permission ke sync routes.

---

#### HIGH-02: IDOR pada endpoint lookup master

**Deskripsi:** `MasterLookupController` mengembalikan pegawai, gudang, inventory untuk `id_unit_kerja` arbitrer tanpa memverifikasi unit caller.

**Lokasi:** `app/Http/Controllers/Api/MasterLookupController.php`

**Rekomendasi:** Gunakan `UserScope::resolveUnitKerjaIds()` dan tolak unit di luar scope (403).

---

#### HIGH-03: IDOR pada `show` transaksi

**Deskripsi:** Beberapa controller `show`/`findOrFail($id)` tanpa cek kepemilikan unit, meskipun `index` sudah di-scope.

**Lokasi contoh:** `PermintaanBarangController::show()`, helper `DistribusiController` (inventory, permintaan detail)

**Rekomendasi:** Terapkan pola yang sama dengan KIR (`KirDokumenFlowTest` — cross-unit ditolak) ke semua entitas transaksi.

---

#### HIGH-04: Login tanpa rate limiting

**Deskripsi:** POST `/login` tidak memakai middleware `throttle`.

**Lokasi:** `routes/web.php`, `app/Http/Controllers/Auth/LoginController.php`

**Rekomendasi:** `throttle:5,1` pada login; log percobaan gagal via `AuditHelper` (`login_failed`, `lockout` sudah didefinisikan tapi belum dipakai).

---

#### HIGH-05: User nonaktif masih bisa login

**Deskripsi:** Kolom `is_active` ada di model user dan form admin, tetapi `LoginController` tidak mengecek status sebelum `Auth::attempt()`.

**Lokasi:** `LoginController.php` vs `UserController.php`

**Rekomendasi:** Setelah attempt berhasil, cek `is_active`; jika false, logout dan tampilkan pesan generik.

---

#### HIGH-06: Pesan exception ditampilkan ke user

**Deskripsi:** Beberapa controller men-flash `$e->getMessage()` ke session error — dapat mengekspos detail SQL/path internal.

**Lokasi contoh:** `PermintaanBarangController`, `PenerimaanBarangController`, `RkuController`

**Rekomendasi:** Pesan generik ke user; detail hanya di log server (`LOG_LEVEL=warning` produksi).

---

### 2.3 Medium — ringkas

| ID | Isu | Lokasi / catatan |
|----|-----|------------------|
| MED-01 | GET `/logout` — CSRF logout paksa | `routes/web.php` L68 |
| MED-02 | Password admin create: hanya `min:8` (tanpa kompleksitas) | `UserController.php` vs `PasswordUpdateRequest` |
| MED-03 | Stored XSS via print template `{{{key}}}` unescaped | `PrintTemplateRenderer.php` |
| MED-04 | Upload file di disk `public` — path predictable | `config/filesystems.php`, controllers upload |
| MED-05 | Import Excel tanpa batas baris/ukuran ketat | `InventoryDataImportController.php` |
| MED-06 | Debug log `$request->all()` pada store permintaan | `PermintaanBarangController.php` |
| MED-07 | `.env.example`: `APP_DEBUG=true`, `SESSION_ENCRYPT=false` | `.env.example` |
| MED-08 | `trustProxies(at: '*')` — IP spoofing jika proxy tidak tepercaya | `bootstrap/app.php` |
| MED-09 | QR scan publik tanpa auth (metadata inventory) | `InventoryQrScanController`, route publik |
| MED-10 | `EnsureWorkflowAccess` middleware ada tapi tidak terdaftar di route | `EnsureWorkflowAccess.php` |
| MED-11 | `AssignablePermissions::editorMayAssignAll()` terlalu luas | `AssignablePermissions.php` |
| MED-12 | Audit middleware log payload penuh (PII) | `AuditRequestActivity.php` |

### 2.4 Low — ringkas

| ID | Isu |
|----|-----|
| LOW-01 | Tidak ada 2FA untuk akun privileged |
| LOW-02 | Tidak ada `AuthenticateSession` / re-auth setelah ganti password |
| LOW-03 | Kolom `is_superadmin` di kode bypass tapi tidak ada migrasi |
| LOW-04 | Seeder password default lemah (`password`, `Admin@123`) |
| LOW-05 | Endpoint `/up` publik (info minimal) |
| LOW-06 | Tanpa security headers (CSP, HSTS, Referrer-Policy) |
| LOW-07 | Dual audit table (`activity_logs` + `audit_logs`) — kompleksitas |
| LOW-08 | `RBAC_LEGACY_USER_MODULES_FALLBACK=true` default — sidebar bisa divergen |

### 2.5 Yang sudah baik (keamanan)

- RBAC Spatie dengan permission = route name; wildcard native
- Bypass enterprise **hanya** `super_administrator` (bukan role `admin` IT)
- CSRF default Laravel pada grup `web`
- Blade mayoritas pakai `{{ }}` (escaped)
- Query builder/Eloquent — tidak ditemukan SQL injection dari input user
- Sanitizer audit untuk field password/token
- `SuperAdminGuard` mencegah penghapusan super admin terakhir
- Test RBAC: `RbacPhase1AuthorizationTest`, `RbacPhase2UserScopeTest`, `KirDokumenFlowTest`

---

## 3. Temuan Kelengkapan Fitur

### 3.1 Matriks modul

| Modul | Status | Catatan |
|-------|--------|---------|
| Dashboard | ✅ Implemented | Metrik, chart, antrian urgent |
| Permintaan Barang | ✅ Implemented | CRUD, multi-item, ajukan |
| Approval multi-level | ✅ Implemented | 7 aksi POST |
| Draft disposisi | ✅ Implemented | Driven ApprovalLog step 4 |
| Compile SBBK (legacy) | ✅ Digabung | Redirect ke Distribusi Barang (SBBK) |
| Distribusi (SBBK) | ⚠️ Partial | Implementasi beda dari dokumentasi (lihat §3.3) |
| Penerimaan | ⚠️ Partial | Auto-create on kirim; tidak ada create manual |
| Retur | ✅ Implemented | CRUD + ajukan/terima/tolak |
| Peminjaman antar unit | ✅ Implemented | Flow lengkap + test |
| **Pemakaian Barang** | ❌ Disabled | Constructor `abort(404)`; routes dikomentari |
| RKU | ✅ Implemented | Workflow + test engine |
| RKU Aktivitas | ❌ Placeholder | Label sidebar; route tidak ada |
| Pengadaan (paket + proses) | ⚠️ Partial | CRUD paket; proses read-only |
| Inventory & stok | ✅ Implemented | CRUD, import, adjustment, farmasi |
| Register Aset / KIR / Mutasi | ✅ Implemented | Termasuk TTE KIR |
| Pemeliharaan & kalibrasi | ✅ Implemented | + test flow |
| Keuangan / Pembayaran | ⚠️ Basic | CRUD sederhana |
| Laporan | ⚠️ Partial | 5 halaman; export hanya 2 (stock gudang, maintenance) |
| Print templates | ⚠️ Flag-gated | Default **OFF**; hanya SBBK terintegrasi |
| Admin users/roles | ✅ Implemented | + audit |
| Audit trail | ⚠️ Partial | HTTP mutation log; bukan full before/after entity |
| **Notifikasi in-app/email** | ❌ Missing | Bell UI placeholder (badge hardcoded "3") |
| Mobile / responsive | ✅ Implemented | Sidebar drawer, breakpoint responsif |
| Portal legacy (`/requests`) | ⚠️ Legacy | Parallel flow selain transaksi utama |

### 3.2 Fitur yang belum tersedia / dinonaktifkan

| Fitur | Status | Referensi |
|-------|--------|-----------|
| Pemakaian Barang | Dinonaktifkan sengaja | `PemakaianBarangController.php` L30, `routes/web.php` L263–267 |
| Notifikasi bisnis (email/in-app) | Belum ada | Tidak ada `app/Mail/`, `app/Notifications/`, `app/Jobs/` |
| 2FA | Belum ada | — |
| Approval terpisah pemeliharaan/pengadaan | Placeholder menu | `PermissionHelper.php` → route salah |
| RKU Aktivitas (modul terpisah) | Belum ada | Sidebar label only |
| Export laporan kartu stok, transaksi, aset | Belum ada | `ReportController` |
| Print template untuk permintaan, retur, KIR dinamis | Belum di-wire | `docs/DAFTAR_DOKUMEN_CETAK.md` |
| Queue jobs bisnis | Belum ada | `queue:listen` di dev tapi zero `app/Jobs/` |
| CI/CD pipeline | Belum ada | Tidak ada `.github/workflows/` |
| Backup database otomatis | Belum ada | Hanya volume Docker manual |

### 3.3 Gap alur bisnis (dokumentasi vs kode)

#### GAP-WF-01: Status permintaan — nama legacy vs kanonik

**Dokumentasi** (`ALUR_TRANSAKSI.md`): `DISETUJUI`, `DIPROSES`, `SELESAI`  
**Kode** (`PermintaanBarangStatus` enum): `diverifikasi`, `proses_distribusi`, `selesai`

Enum punya `fromLegacy()` untuk migrasi data, tetapi panduan user perlu diselaraskan.

#### GAP-WF-02: Verifikasi kasubbag vs approve kepala pusat

Dokumentasi menjelaskan approve/reject terpisah di kepala pusat. Kode dapat langsung mengubah status ke `proses_distribusi` atau `menunggu_pengadaan` pada **verifikasi kasubbag** (`ApprovalPermintaanService`).

#### GAP-WF-03: Handoff pengadaan → distribusi

Setelah paket pengadaan selesai, status permintaan berhenti di `barang_tersedia`. **Tidak ada otomatisasi** lanjut ke disposisi/distribusi. Permintaan bisa "terjebak" tanpa aksi manual pengurus barang.

#### GAP-WF-04: Semantik "kirim" distribusi

**Dokumentasi:** kirim → status `dikirim` → unit menunggu → penerimaan manual  
**Kode** (`DistribusiService::kirim()`): kurangi stok, auto-create penerimaan `MENUNGGU_VERIFIKASI`, distribusi langsung `selesai`

Perilaku fungsional ada, tetapi **tidak selaras** dengan step-by-step panduan.

#### GAP-WF-05: Compile SBBK tidak terlihat di menu — **by design (resolved)**

Route `transaction.compile-distribusi.*` sengaja **hanya redirect** ke **Distribusi Barang (SBBK)**. Tahap Compile terpisah digabung: proses disposisi di Daftar Permintaan langsung membuat SBBK. **Tidak perlu** menambah item sidebar Compile.

#### GAP-WF-06: Dashboard masih query Pemakaian Barang

Modul disabled, tetapi antrian dashboard mungkin masih mereferensikan model `PemakaianBarang`.

### 3.4 RBAC — status fase

| Fase | Status | Isi |
|------|--------|-----|
| **Fase 1** | ✅ Selesai | DB permissions, Spatie wildcard, super_admin bypass only, seeder + test |
| **Fase 2** | ✅ Mostly done | `UserScope`, 16 role kanonik, refactor controller, `rbac:audit` CLI |
| **Fase 3** | ❌ Belum | Hapus `user_modules` fallback, unify workflow_permissions, permission rename `module.action`, hapus role check hardcoded di controller |

**Technical debt Fase 3:** `config/sipeni.php` — `RBAC_LEGACY_USER_MODULES_FALLBACK=true` default; `DistribusiController` masih cek role name langsung.

### 3.5 Seed data demo

**Default `DatabaseSeeder`:** RBAC, master data, user per jabatan, approval flow, template SBBK.

**Tidak di-seed default:** `ComprehensiveDummySeeder` (dikomentari) — tidak ada chain permintaan→distribusi→penerimaan out-of-the-box.

Fresh install cocok untuk uji login/RBAC; **lemah untuk demo end-to-end transaksi** tanpa input manual.

---

## 4. Temuan Infrastruktur & Operasional

### 4.1 Testing

| Metrik | Nilai |
|--------|-------|
| Feature test files | 14 |
| Unit test files | 2 |
| Total metode test | ~80 |
| Controllers | ~55 |
| Model factories | 1 (`UserFactory` only) |

**Area teruji dengan baik:** RBAC (3 file), RKU workflow (9 test), maintenance (9), audit model (8), KIR/register aset, peminjaman, retur partial.

**Area belum/baru smoke test:**

```
Permintaan → Approval → Disposisi → Buat SBBK (Distribusi) → Kirim → Penerimaan
```

`CriticalFlowSmokeTest` hanya cek redirect guest (15 route), bukan validasi bisnis.

**Rekomendasi:** `PermintaanBarangEndToEndTest`, factories untuk entitas transaksi, coverage threshold pada `app/Services/`.

### 4.2 CI/CD

- **Tidak ada** GitHub Actions / pipeline otomatis di repo
- `composer run test` tersedia lokal; tidak dijalankan otomatis on PR

### 4.3 Docker

| Aspek | Status |
|-------|--------|
| Image PHP 8.3-apache | ✅ Ada |
| Web + queue worker | ✅ `docker-compose.yml` |
| MySQL optional | ✅ `docker-compose.db.yml` |
| Frontend build di Dockerfile | ❌ Tidak — andalkan `public/build/` pre-built |
| `.dockerignore` | ❌ Tidak ada |
| Auto-migrate on start | ❌ Manual `docker exec ... migrate` |
| Healthcheck compose | ❌ `/up` ada tapi tidak di-wire |
| Storage volume | ❌ Upload/logs bisa ephemeral |

### 4.4 Logging & monitoring

| Komponen | Status |
|----------|--------|
| `AuditRequestActivity` middleware | ✅ POST/PUT/PATCH/DELETE |
| Pail (dev) | ✅ `composer run dev` |
| APM (Sentry, Pulse, Telescope) | ❌ Tidak aktif produksi |
| Alert queue failed jobs | ❌ Tidak ada |
| Structured log shipping | ❌ Tidak dikonfigurasi |

### 4.5 Backup & migrasi

- ~107 file migrasi; beberapa **MySQL-specific** (enum ALTER)
- Test pakai SQLite in-memory — risiko drift MySQL vs SQLite
- **Tidak ada** script backup otomatis / `spatie/laravel-backup`
- Rollback plan ada di docs RBAC tapi `db:restore` mungkin tidak ada sebagai artisan command

### 4.6 Performa

**Positif:**
- Cache sidebar menu 24 jam (`PermissionHelper`)
- `SharedLayoutData` sekali per request
- `DataStock::buildBulkStockSnapshot()` batch query
- OPcache + config/view cache di Docker build

**Risiko:**
- N+1 di beberapa list endpoint
- Tidak ada `Model::preventLazyLoading()` di dev
- Cache driver database default (`.env.example`)

### 4.7 Dependensi

| Package | Catatan keamanan |
|---------|------------------|
| Laravel 13 | Bleeding edge — pantau advisory |
| spatie/laravel-permission | Surface RBAC |
| dompdf | PDF generation |
| maatwebsite/excel | Import file |
| openai-php/client | API key di env |

Tidak ada `composer audit` / Dependabot di CI.

---

## 5. Keselarasan Dokumentasi

| Topik | Dokumentasi | Implementasi | Selaras? |
|-------|-------------|--------------|----------|
| Status permintaan | Nama legacy | Enum kanonik | ❌ |
| Alur kirim distribusi | DIKIRIM → tunggu → terima | Auto penerimaan + selesai | ❌ |
| Compile SBBK (legacy) | Digabung ke Distribusi | Redirect only | ✅ by design |
| Pemakaian Barang | Disabled di panduan | Match (404) | ✅ |
| Notifikasi | Placeholder | Match (cosmetic) | ✅ |
| Admin IT bypass | Tidak bypass scope | Match RBAC Phase 1 | ✅ |
| Print templates | Optional env flag | Default false | ✅ |
| Panduan PDF | Tersedia | `php artisan panduan:export-pdf` | ✅ |

Dokumentasi **user-facing** (`docs/panduan-pengguna/`) sudah lengkap dan PDF-ready. Dokumentasi **teknis alur** (`ALUR_TRANSAKSI.md`) perlu diperbarui agar selaras dengan enum dan service aktual.

---

## 6. Roadmap Perbaikan

### P0 — Segera (1–2 sprint, pre-production)

| # | Item | Area | Effort |
|---|------|------|--------|
| 1 | Blok assign role `super_administrator` kecuali oleh super admin | Security | Rendah |
| 2 | Tambah permission + scope check pada semua route `api/*` dan `api/master/*` | Security | Sedang |
| 3 | Rate limit login + cek `is_active` + audit login gagal | Security | Rendah |
| 4 | Hapus GET logout; hentikan flash `$e->getMessage()` ke user | Security | Rendah |
| 5 | Scope check pada `show`/`update` entitas transaksi (IDOR) | Security | Sedang |
| 6 | GitHub Actions: `composer run test` on push/PR | Ops | Rendah |
| 7 | Backup MySQL otomatis sebelum deploy | Ops | Sedang |

### P1 — Pendek (2–4 sprint)

| # | Item | Area |
|---|------|------|
| 8 | Auto-resume `barang_tersedia` → disposisi/distribusi | Workflow |
| 9 | ~~Tambah Compile SBBK ke sidebar~~ — tidak perlu; sudah digabung ke Distribusi | UX |
| 10 | Selaraskan `ALUR_TRANSAKSI.md` dengan enum/service | Docs |
| 11 | Foundation notifikasi (event → in-app/email) untuk approval & penerimaan | Feature |
| 12 | Aktifkan `FEATURE_PRINT_TEMPLATES` di staging; wire dokumen lain | Feature |
| 13 | `composer audit` + Dependabot | Security |
| 14 | End-to-end test permintaan → penerimaan | Testing |
| 15 | Security headers middleware (CSP, HSTS) | Security |

### P2 — Menengah (backlog)

| # | Item | Area |
|---|------|------|
| 16 | RBAC Fase 3 (hapus `user_modules` fallback) | RBAC |
| 17 | Export laporan kartu stok, transaksi, aset | Feature |
| 18 | Selesaikan atau hapus modul Pemakaian Barang sepenuhnya | Feature |
| 19 | Multi-stage Docker + `.dockerignore` + healthcheck | Ops |
| 20 | 2FA untuk super admin & admin IT | Security |
| 21 | Pindah upload sensitif off public disk | Security |
| 22 | Model factories; kurangi ketergantungan seeder di test | Testing |
| 23 | Ops runbook (`docs/OPERATIONS.md`) + root README | Docs |

---

## 7. Checklist Kesiapan Go-Live

Gunakan checklist ini sebelum produksi penuh:

### Keamanan
- [ ] P0 security items (§6) selesai
- [ ] `APP_DEBUG=false`, `LOG_LEVEL=warning`, `SESSION_ENCRYPT=true`, `SESSION_SECURE_COOKIE=true`
- [ ] Password default seeder diganti di produksi
- [ ] `TrustProxies` dikonfigurasi ke IP load balancer nyata
- [ ] Review data yang diekspos QR scan publik

### Fitur
- [ ] Alur permintaan→penerimaan diuji UAT dengan data real
- [ ] Handoff pengadaan→distribusi jelas (otomatis atau SOP manual)
- [x] Buat SBBK dari Daftar Permintaan / Distribusi (Compile terpisah tidak dipakai)
- [ ] Keputusan final: Pemakaian Barang on/off
- [ ] Print template: env flag & template produksi ready

### Operasional
- [ ] CI pipeline hijau
- [ ] Backup & restore procedure teruji
- [ ] Migrate dry-run di staging (MySQL)
- [ ] Monitoring exception (Sentry/similar)
- [ ] Log retention policy defined

### Dokumentasi
- [ ] `ALUR_TRANSAKSI.md` diselaraskan
- [ ] `.env.example` lengkap (semua var SIPENI)
- [ ] Tim operasional punya `docs/OPERATIONS.md`

---

## 8. Lampiran

### A. File kunci untuk remediation

```
app/Http/Controllers/Auth/LoginController.php
app/Http/Controllers/Admin/UserController.php
app/Http/Controllers/Api/MasterLookupController.php
app/Http/Controllers/Transaction/PermintaanBarangController.php
app/Http/Controllers/Transaction/DistribusiController.php
app/Services/DistribusiService.php
app/Services/ApprovalPermintaanService.php
app/Http/Middleware/AuditRequestActivity.php
app/Http/Middleware/EnsureWorkflowAccess.php
routes/web.php
config/sipeni.php
tests/Feature/
```

### B. Test coverage map (ringkas)

| Flow | Test file | Coverage |
|------|-----------|----------|
| Guest auth redirect | `CriticalFlowSmokeTest` | Smoke only |
| RBAC wildcard/bypass | `RbacPhase1AuthorizationTest` | Good |
| Unit scope | `RbacPhase2UserScopeTest` | Good |
| RKU workflow | `RkuWorkflowEngineTest` | Good |
| Peminjaman | `PeminjamanBarangFlowTest` | Happy path |
| Retur | `ReturBarangFlowTest` | Partial |
| KIR cross-unit | `KirDokumenFlowTest` | Good |
| Permintaan E2E | — | **Missing** |
| Approval chain | — | **Missing** |
| Distribusi kirim | `InventoryBusinessFlowTest` | Overstock only |
| Login security | — | **Missing** |

### C. Dokumen terkait

- [Panduan Pengguna](./panduan-pengguna/README.md) (+ PDF di `panduan-pengguna/pdf/`)
- [RBAC Fase 1](./rbac-phase1/README.md)
- [RBAC Fase 2](./rbac-phase2/README.md)

---

**Disusun oleh:** Audit otomatis + review kode  
**Revisi berikutnya:** Setelah P0 selesai, atau setiap rilis mayor
