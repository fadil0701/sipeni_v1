# Panduan: Super Administrator

> **Akses di aplikasi:** Menu sidebar **Panduan Pengguna** — bab ini otomatis ditampilkan untuk role Anda.

## Profil role

| | |
|---|---|
| **Level** | Pusat |
| **Akses** | Lintas unit dan seluruh modul |
| **Modul default** | Semua modul |

## Kegunaan

Mengelola seluruh sistem: konfigurasi, user, role, audit, dan intervensi data lintas unit bila diperlukan.

## Menu yang umumnya digunakan

- **Semua menu sidebar** (Transaksi, Inventory, Aset, Planning, Procurement, Finance, Maintenance, Monitoring, Organisasi, Akses & Kontrol)
- **Dashboard**

## Tugas harian / berkala

1. **User & Role** — Akses & Kontrol → User / Role & Workflow Authority
2. **Audit** — Executive Activity Timeline
3. **Master organisasi** — Pegawai, Unit Kerja, Jabatan
4. **Monitoring** — Laporan stok, audit trail
5. **Intervensi transaksi** — Approval, distribusi, jika eskalasi dari unit

## Langkah penting

### Menambah user baru

1. Organisasi → Pegawai (pastikan data pegawai ada)
2. Akses & Kontrol → User → Tambah
3. Assign **role kanonik** + pastikan pegawai ter-link
4. Set `unit_kerja_id` pada role jika role unit-scoped

### Mengubah permission role

1. Akses & Kontrol → Role → Edit
2. Centang permission (nama = route)
3. Simpan → cache permission otomatis di-reset

## Yang perlu dihindari

- Jangan bagikan akun ini ke operator harian
- Setelah ubah permission role, minta user logout/login agar menu refresh

## Dokumen terkait

- [admin-dan-administrator.md](./admin-dan-administrator.md) — perbedaan dengan Admin IT
- [../04-matrik-akses-role.md](../04-matrik-akses-role.md)
