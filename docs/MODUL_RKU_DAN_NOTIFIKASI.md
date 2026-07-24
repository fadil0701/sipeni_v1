# Modul RKU, penyimpanan data, dan standarisasi notifikasi

Dokumen ini merangkum implementasi perbaikan RKU + fondasi notifikasi global (Mei 2026).

## 1. Penyebab utama data RKU gagal tersimpan

1. **Nilai `details.*.jenis_rku` tidak cocok skema**  
   Form mengirim nilai di luar ENUM / aturan (misalnya nilai legacy), sementara kolom `rku_detail.jenis_rku` memakai **BARANG, JASA, MODAL** (setelah migrasi `2026_05_12_120000_update_rku_detail_jenis_rku_to_barang_jasa_modal.php`; nilai lama **ASET** di basis data diubah ke **MODAL**).

2. **Perbaikan**  
   Opsi form dan validasi (`CreateRkuRequest`, `UpdateRkuRequest`, `RkuValidationService`) diseragamkan ke **Barang / Jasa / Modal**.

## 2. Route & controller

- Resource: `Route::resource('rku', RkuController::class)` di grup `planning.` → nama rute `planning.rku.*` (bukan `rku.*` tanpa prefix).
- `store` / `update`: transaksi DB tetap di `RkuService`; `update` mem-patch header hanya untuk **kunci yang benar-benar ada** di array hasil `validated()` agar tidak menimpa `jenis_rku` header jika field tidak dikirim form.

## 3. Filter daftar RKU

- Form index memakai query `status_rku`; controller sebelumnya memakai kunci `status` saja → filter status tidak jalan.  
  Sekarang: `status_rku` dipetakan ke `filters['status']`.
- `getPaginatedList` mendukung `tahun` atau `tahun_anggaran` pada filter tahun.

## 4. Sidebar & menu modul

- **Transaksi → RKU** (`?context=unit`): role unit-scoped (`admin_unit`, dll.) — hanya RKU unit sendiri.
- **Perencanaan → Daftar RKU** (`?context=daftar`): tim pusat/perencana (`planning.rku.view_all` atau bukan unit-scoped) — seluruh unit; boleh tambah RKU.
- Jangan memasang kedua label ke URL yang sama tanpa `context` — itu menyebabkan highlight ganda di sidebar.

## 5. Standarisasi notifikasi (fondasi global)

| Lapisan | Perilaku |
|--------|----------|
| **Flash banner** | Komponen Blade `x-ui.flash-messages` disematkan sekali di `layouts/app` (atas konten). Mendukung `success`, `error`, `warning`, `info`, `status`. Tombol tutup (`data-dismiss-flash`). |
| **Toast** | `window.Sipeni.toast(pesan, tipe, durasiMs)` di `resources/js/notifications/sipeni-notify.js`; stack `#sipeni-toast-stack` di layout; gaya di `resources/css/app.css`. |
| **Konfirmasi** | Tetap modal tunggal di `resources/js/layout/app-layout.js` (`form-confirm`). Ekspor: `window.Sipeni.confirm(pesan) → Promise<boolean>`. |
| **Loading submit** | Tetap perilaku `global-loading` + disable tombol submit di `app-layout.js`. |

Opsi `.env` / `config/sipeni.php`:

- `SIPENI_TOAST_MIRROR_FLASH` — jika `true`, satu flash (success/error/warning/info) juga ditampilkan sebagai toast via JSON `#sipeni-flash-json`.

## 6. Checklist debugging RKU / form

- [ ] Network: POST ke `/planning/rku`, payload `details[n][jenis_rku]` salah satu dari `BARANG`, `JASA`, `MODAL`.
- [ ] Response: redirect dengan `errors` vs `success`.
- [ ] DB: baris `rku_header` + `rku_detail` setelah commit.
- [ ] User tanpa baris detail: validasi `details.required` / `details.min`.
- [ ] Console: tidak ada error JS pada hitung total / tambah baris.

## 7. Refactor & design system (jangka panjang)

1. **Pindahkan markup baris detail RKU** ke satu partial Blade + satu modul JS (Vite) untuk menghindari duplikasi create/edit.
2. **Audit bertahap** view lain: hapus blok `@if(session('success'))` duplikat setelah mempercayai layout flash.
3. **FormRequest** untuk semua aksi workflow RKU (submit/approve/reject) agar pesan konsisten.
4. **Design tokens** Tailwind (warna status, radius tombol) di satu layer `@theme` / komponen Blade enterprise.

## 8. Best practice Laravel (yang sudah dipakai)

- Form Request untuk `store` / `update`
- Service + transaksi DB untuk operasi multi-tabel
- Policy + permission route-name
- Flash redirect konsisten (`->with('success'| 'error', ...)`)
