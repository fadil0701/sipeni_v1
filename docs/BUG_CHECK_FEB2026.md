# Pemeriksaan Bug Setelah Pengembangan – Feb 2026

Dokumen ini merangkum hasil pengecekan bug pada modul yang telah dikembangkan/diperbaiki.

---

## Bug yang Ditemukan dan Sudah Diperbaiki

### 1. Pemakaian Barang – Auto-approve admin mengabaikan kegagalan validasi stok

**Lokasi:** `app/Http/Controllers/Transaction/PemakaianBarangController.php` – `store()` dan `update()`

**Masalah:**  
Saat admin membuat/update pemakaian dengan status **DIAJUKAN**, sistem memanggil `approve()`. Jika validasi stok di `approve()` gagal (qty melebihi stok), `approve()` mengembalikan redirect dengan session `error`. Di `store()`/`update()` return value diabaikan: transaksi tetap di-commit dan user mendapat pesan sukses "Pemakaian berhasil dibuat dan disetujui", padahal pemakaian tetap status DIAJUKAN dan tidak disetujui.

**Perbaikan:**
- Setelah memanggil `approve()`, cek `session()->has('error')`.
- Jika ada error: `DB::rollBack()` lalu redirect ke halaman show dengan session error.
- Jika tidak ada error: `DB::commit()` lalu return response dari `approve()`.
- Di view `transaction/pemakaian-barang/show.blade.php` ditambah blok tampilan `session('success')` dan `session('error')` agar pesan tampil.

---

### 2. KIR – Potensi error saat ruangan tidak ada (null)

**Lokasi:** `app/Http/Controllers/Asset/KartuInventarisRuanganController.php` – `show()`, `edit()`, `update()`

**Masalah:**  
Pengecekan unit kerja memakai `$kir->ruangan->id_unit_kerja`. Jika ruangan dihapus dari master atau `id_ruangan` tidak valid, relasi `ruangan` bisa null dan memicu error saat akses property.

**Perbaikan:**  
Menggunakan null-safe: `$kir->ruangan?->id_unit_kerja`. Jika null, dianggap tidak dalam unit kerja user sehingga akses ditolak (403).

---

### 3. Mutasi Aset – Potensi error saat ruangan asal/tujuan tidak ada

**Lokasi:** `app/Http/Controllers/Asset/MutasiAsetController.php` – `show()`

**Masalah:**  
`$mutasiAset->ruanganAsal->id_unit_kerja` dan `ruanganTujuan->id_unit_kerja` bisa error jika ruangan dihapus dari master.

**Perbaikan:**  
Menggunakan null-safe: `$mutasiAset->ruanganAsal?->id_unit_kerja` dan `$mutasiAset->ruanganTujuan?->id_unit_kerja` untuk pengecekan otorisasi.

---

## Area yang Dicek dan Tidak Ditemukan Bug

- **KIR:** store/update/destroy – sinkronisasi RegisterAset.id_ruangan dan InventoryItem; filter create (Register Aset tanpa KIR).
- **Mutasi Aset:** store/update – sinkronisasi KIR, RegisterAset, InventoryItem; validasi ruangan asal = KIR; simpan `oldIdRuanganTujuan` sebelum update.
- **Pemakaian Barang:** approve() – validasi stok per inventory dan per DataStock; update DataStock dan DataInventory; role approve/reject (kepala_unit).
- **Permintaan Barang & Approval:** filter Persediaan/Farmasi; permintaan lainnya (freetext); disposisi ke Pengadaan untuk item tanpa stock; tombol Mengetahui/Verifikasi/Tolak di index.
- **Retur Barang:** validasi status (hanya DRAFT/DIAJUKAN di form); update stok saat terima.
- **Stock Adjustment:** filter gudang kategori; index filter barang dan tanggal.
- **Register Aset:** update logic saat ruangan dihapus (old id_ruangan, update InventoryItem sebelum hapus KIR).

---

## Rekomendasi (bukan bug, peningkatan)

1. **Pemakaian Barang – Validasi gudang–unit kerja:** Tambah validasi di store/update bahwa `id_gudang` milik `id_unit_kerja` yang dipilih (konsistensi data).
2. **Mutasi Aset – Hapus mutasi:** Saat mutasi dihapus, saat ini KIR/RegisterAset tetap di ruangan tujuan. Jika diinginkan "revert ke ruangan asal", perlu logika tambahan (opsional).
3. **Testing:** Disarankan uji skenario: create pemakaian DIAJUKAN oleh admin dengan qty melebihi stok; hapus ruangan yang masih dipakai KIR; mutasi dengan ruangan yang kemudian dihapus.

---

**Tanggal:** 02 Feb 2026  
**Versi:** 1.0
