# Panduan: Pengurus Barang

> **Akses di aplikasi:** Menu sidebar **Panduan Pengguna** — bab ini otomatis ditampilkan untuk role Anda.

## Profil role

| | |
|---|---|
| **Level** | Pusat |
| **Modul default** | Inventory, Transaksi, Aset, Pemeliharaan |

## Kegunaan

**Pengelola operasional persediaan** — disposisi permintaan, buat & kirim **SBBK**, distribusi, oversight gudang pusat, serta **oversight pemeliharaan** (daftar permintaan teknisi, service report, jadwal, kalibrasi).

## Menu utama

| Menu | Kegunaan |
|------|----------|
| **Approval** | **Disposisi** permintaan barang ke gudang / disposisi pemeliharaan ke teknisi atau vendor |
| **Distribusi → Daftar Permintaan** | Monitor disposisi / proses ke SBBK |
| **Distribusi → Distribusi Barang (SBBK)** | Buat, proses, kirim, cetak SBBK |
| **Inventory** | Stok pusat, kartu stok, opname |
| **Aset** | Register, KIR, mutasi |
| **Pemeliharaan** | Daftar permintaan teknisi, Laporan Servis, Jadwal, Kalibrasi |

## Alur permintaan (setelah verifikasi / cabang distribusi)

1. **Approval** → disposisi ke gudang kategori (Persediaan/Farmasi/Aset) bila stok master cukup
2. Monitor **Daftar Permintaan** — admin gudang kategori proses item
3. **Buat SBBK** langsung dari proses disposisi (menu Distribusi) — tidak ada tahap Compile terpisah
4. **Kirim SBBK** → cetak PDF
5. Unit **penerimaan** → status selesai

## Alur pemeliharaan (ringkas)

1. Unit ajukan permintaan → approval
2. **Disposisi** ke Teknisi ATEM / Teknisi IT / Vendor
3. Teknisi (atau Anda) isi **Laporan Servis**
4. Mengetahui SR sesuai flow; bila Pending spare part → jalur pengadaan

## Tips

- Prioritaskan permintaan CITO
- Cek stok pusat sebelum disposisi — jika kosong / permintaan lainnya, jalur pengadaan (Kepala Pusat)
- Satu SBBK per disposisi/kategori gudang (sesuai implementasi saat ini)
- Bedakan **role teknisi** (hak menu) dengan **jabatan pegawai ATEM/IT Support** (dropdown nama di form SR)

## Dokumen cetak

SBBK PDF — lihat [DAFTAR_DOKUMEN_CETAK.md](../../DAFTAR_DOKUMEN_CETAK.md)
