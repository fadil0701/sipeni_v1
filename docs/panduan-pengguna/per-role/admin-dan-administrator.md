# Panduan: Admin & Administrator (IT)

> **Akses di aplikasi:** Menu sidebar **Panduan Pengguna** — bab ini otomatis ditampilkan untuk role Anda.

## Profil role

| | |
|---|---|
| **Role** | `admin`, `administrator` (legacy) |
| **Akses** | Berbasis permission yang diberikan administrator |
| **Modul default** | Master, Inventory, Transaksi, Planning, Procurement, Finance, Reports |

## Kegunaan

**Pengelola aplikasi / Admin IT** — mengelola user, role, master data, dan dukungan operasional sesuai permission yang ditetapkan.

## Menu umum

| Menu | Kegunaan |
|------|----------|
| **Akses & Kontrol → User** | CRUD user |
| **Akses & Kontrol → Role** | Permission matrix |
| **Organisasi** | Pegawai, unit, jabatan |
| **Master data / Inventory** | Dukungan operasional (jika diberi permission) |

## Langkah: Setup user baru

1. Master Pegawai → pastikan NIP & unit benar
2. User → Tambah → email, password
3. Assign role kanonik (bukan legacy jika memungkinkan)
4. Test login sebagai user tersebut

## Langkah: Tambah permission ke role

1. Role → Edit → centang permission route
2. Simpan
3. User logout/login

## Akun & keamanan

Akun dikelola administrator sistem. Password baru minimal **12 karakter** (huruf besar, angka, simbol). Lihat [01 — Pengenalan & Login](../01-pengenalan-dan-login.md).

## Dokumen teknis

- [HAK_AKSES_ROLE_DAN_DELEGASI.md](../../HAK_AKSES_ROLE_DAN_DELEGASI.md)
- [rbac-phase1/README.md](../../rbac-phase1/README.md)
