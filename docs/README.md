# 📚 Dokumentasi SI-MANTIK

Selamat datang di dokumentasi Sistem Informasi Manajemen Terintegrasi (SI-MANTIK).

## 📖 Daftar Dokumentasi

### 1. [Hak Akses, Role, dan Delegasi Permission](./HAK_AKSES_ROLE_DAN_DELEGASI.md)
Penjelasan teknis: permission = nama route, modul sidebar vs permission role, sinkronisasi `permission:sync-routes`, class `PermissionModule` / `AssignablePermissions`, serta perilaku Manajemen Role dan User (delegasi untuk non-admin).

**Untuk:** Developer dan admin yang mengonfigurasi akses sistem.

---

### 2. [Alur Transaksi Barang - Step by Step Guide](./ALUR_TRANSAKSI.md)
Panduan lengkap penggunaan sistem transaksi barang mulai dari permintaan hingga penerimaan dan retur.

**Isi:**
- Step-by-step guide untuk setiap tahap transaksi
- Penjelasan role dan permission
- Status transaksi dan workflow
- Tips & best practices
- Troubleshooting

**Untuk:** Semua user yang menggunakan sistem transaksi

---

### 3. [Diagram Alur Transaksi](./DIAGRAM_ALUR_TRANSAKSI.md)
Diagram visual dan flowchart lengkap alur transaksi barang.

**Isi:**
- Flowchart lengkap transaksi
- Role & Permission Matrix
- Status Flow Diagram
- Decision Points
- Data Flow
- Timeline Estimasi

**Untuk:** Developer, Admin, dan User yang ingin memahami alur sistem secara visual

---

## 🚀 Quick Start

### Untuk User Baru:
1. Baca [Alur Transaksi Barang](./ALUR_TRANSAKSI.md) untuk memahami cara menggunakan sistem
2. Lihat [Diagram Alur Transaksi](./DIAGRAM_ALUR_TRANSAKSI.md) untuk memahami workflow secara visual

### Untuk Developer:
1. Baca [Hak Akses, Role, dan Delegasi Permission](./HAK_AKSES_ROLE_DAN_DELEGASI.md) untuk konfigurasi permission dan panel admin.
2. Review [Diagram Alur Transaksi](./DIAGRAM_ALUR_TRANSAKSI.md) untuk memahami arsitektur sistem.
3. Lihat kode di folder `app/Http/Controllers/Transaction/` untuk implementasi detail.

---

## 📋 Ringkasan Alur Transaksi

```
1. Permintaan Barang (Pegawai/Kepala Unit)
   ↓
2. Approval Multi-Level (Kepala Unit → Kasubbag → Kepala Pusat)
   ↓
3. Disposisi (Admin Gudang)
   ↓
4. Proses Disposisi (Admin Gudang Kategori)
   ↓
5. Compile SBBK (Admin Gudang)
   ↓
6. Distribusi/Kirim (Admin Gudang)
   ↓
7. Penerimaan Barang (Pegawai/Kepala Unit)
   ↓
8. Retur Barang (Opsional - Pegawai/Kepala Unit)
```

---

## 🔑 Role & Access

| Role | Deskripsi | Akses Utama |
|------|-----------|-------------|
| **Pegawai** | User biasa | Permintaan, Penerimaan, Retur |
| **Kepala Unit** | Pimpinan unit kerja | Permintaan, Approval, Penerimaan, Retur |
| **Kasubbag TU** | Kasubbag Tata Usaha | Permintaan, Verifikasi Approval |
| **Kepala Pusat** | Pimpinan pusat | Permintaan, Final Approval |
| **Admin Gudang** | Pengurus barang pusat | Semua transaksi, Compile SBBK |
| **Admin Gudang Kategori** | Admin gudang spesifik | Proses Disposisi sesuai kategori |
| **Admin** | Super admin | Full access semua fitur |

---

## 📞 Support & Kontak

Jika ada pertanyaan atau butuh bantuan:
- **Email:** support@example.com
- **Phone:** +62-xxx-xxxx-xxxx
- **Documentation:** [Link ke dokumentasi lengkap]

---

## 📝 Changelog

### Version 1.0
- ✅ Dokumentasi Alur Transaksi lengkap
- ✅ Diagram Alur Transaksi visual
- ✅ Role & Permission Matrix
- ✅ Troubleshooting guide

---

**Last Updated:** April 2026
**Maintained by:** Tim Development SI-MANTIK



