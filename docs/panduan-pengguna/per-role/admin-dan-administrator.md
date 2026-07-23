# Panduan: Admin & Administrator (IT)

> **Anda adalah audiens utama menu Panduan Pengguna** di aplikasi (bersama Super Administrator). Role operasional tidak dapat membuka `/panduan`.

## Profil role

| | |
|---|---|
| **Role** | `admin`, `administrator` (legacy) |
| **Akses** | Berbasis permission yang diberikan |
| **Modul default** | Master, Inventory, Transaksi, Planning, Procurement, Finance, Reports (+ sesuai permission) |

## Kegunaan

**Pengelola aplikasi / Admin IT** — mengelola user, role, master data, dan dukungan operasional sesuai permission yang ditetapkan.

## Menu umum

| Menu | Kegunaan |
|------|----------|
| **Panduan Pengguna** | Matriks role, panduan per role, cara kelola akses |
| **Akses & Kontrol → User** | CRUD user + modul menu |
| **Akses & Kontrol → Role** | Permission matrix |
| **Organisasi** | Pegawai, unit, jabatan |
| **Master data / Inventory** | Dukungan operasional (jika diberi permission) |

## Bacaan wajib

1. [05 — Mengelola Role, Permission & User](../05-mengelola-role-permission-dan-user.md)
2. [04 — Matriks Akses Role](../04-matrik-akses-role.md)
3. Setup teknisi: [teknisi_atem](./teknisi_atem.md) · [teknisi_it](./teknisi_it.md)

## Langkah: Setup user baru

1. Master Pegawai → pastikan NIP, unit, dan **jabatan** benar (penting untuk dropdown teknisi)
2. User → Tambah → email, password
3. Assign role kanonik (bukan legacy jika memungkinkan)
4. Centang **modul menu** yang sesuai
5. Test login sebagai user tersebut

## Langkah: Tambah permission ke role

1. (Opsional) `permission:sync-routes` setelah ada route baru
2. Role → Edit → centang permission
3. Simpan
4. User logout/login (atau tunggu cache menu ter-refresh)

## Akun & keamanan

Akun dikelola administrator sistem. Password baru minimal **12 karakter** (huruf besar, angka, simbol). Lihat [01 — Pengenalan & Login](../01-pengenalan-dan-login.md).

## Dokumen teknis

- [HAK_AKSES_ROLE_DAN_DELEGASI.md](../../HAK_AKSES_ROLE_DAN_DELEGASI.md)
- [rbac-phase1/README.md](../../rbac-phase1/README.md)
