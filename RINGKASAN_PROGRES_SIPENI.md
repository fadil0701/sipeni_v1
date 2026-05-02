# Ringkasan Progres Pengembangan SIPENI

Dokumen ini merangkum pekerjaan yang sudah diselesaikan dan daftar pekerjaan lanjutan yang belum dikerjakan pada sesi pengembangan terbaru. Bagian teknis diselaraskan dengan perilaku kode di repository SIPENI saat ini (`PermintaanBarangController`, `DataInventoryController`, `DistribusiService`, dll.).

### Status ringkas *(per 2026-05-03)*

| Kategori | Isi singkat |
|----------|----------------|
| **Sudah diselesaikan** | TTE dokumen KIR tahap internal: segel (`tte_document_seals`) + hash + QR verifikasi + **tiga slot TTE per peran** (`tte_document_signatures`: Kepala Pusat, Pengurus Barang, Kepala Unit) dengan aksi **Tandatangani** per akun pegawai terpetakan; halaman publik `/verifikasi-dokumen/{token}` menampilkan daftar status per peran; desain di `docs/TTE_DESAIN_TAHAP_1.md`. **Import Data Inventory:** tombol **Import data** di halaman Data Inventory, permission wildcard import untuk role gudang di `PermissionHelper` + entri `PermissionSeeder`, perbaikan **aktif ganda** menu sidebar Data Inventory vs Import (wildcard route). Retur / Kartu Stok / tes terkait sesuai catatan §3 Prioritas Menengah #4–#6 (2026-05-02). |
| **Sedang / parsial** | TTE: verifikasi dengan **input kode** tanpa URL, log audit akses halaman verifikasi, penyempurnaan metadata. Select2 vs sisa fallback **Choices.js** di beberapa blade (§2.E). UAT manual lintas role untuk modul besar. Hardening test ekstrem maintenance (§4.D). |
| **Akan dikerjakan / backlog terarah** | Integrasi **PSrE / BSrE** resmi bila kebijakan menghendaki. Opsional: membuka akses role tambahan ke dokumen KIR (mis. **Kepala Pusat** pada middleware route asset) agar penandatangan bisa mengakses dari browser tanpa workaround admin. Prioritas rendah: polish visual select, UX cetak unduhan, notifikasi peminjaman (§4.D). |

---

## 1) Pekerjaan Yang Sudah Diselesaikan

### A. Stabilitas Data Inventory & Master Referensi
- **Status: Selesai**
- Perbaikan error input `Data Inventory` saat `id_sub_kegiatan` kosong.
- Penambahan fallback agar proses simpan inventory tetap berjalan saat field sub kegiatan tidak dikirim dari form.
- Perbaikan alur validasi agar error lebih ramah pengguna dibanding crash runtime.

### B. Register Aset & KIR (Alur Data)
- **Status: Selesai**
- Perbaikan simpan `Register Aset` agar `id_inventory` tidak lagi tergantung hidden field JavaScript.
- Sinkronisasi backend dari `id_item -> id_inventory` sehingga submit tetap valid walau hidden input tidak terisi.
- Perbaikan sinkronisasi ruangan KIR agar update tidak lagi mass update semua item dalam 1 inventory.
- Sinkronisasi kini diarahkan ke item yang tepat (berbasis `id_item`, dengan fallback untuk data lama).
- Penyelarasan alur input agar tidak double input:
  - create register aset tidak lagi mengisi ruangan,
  - ruangan + penanggung jawab diisi pada tahap Input Penempatan KIR.
- Setelah simpan register aset, alur diarahkan langsung ke form Input Penempatan KIR dengan register terpilih otomatis.
- Pemisahan `nomor_register` vs `kode_register`:
  - `kode_register` tetap identitas item,
  - `nomor_register` menjadi nomor administrasi register aset (unik, generated).
- Penambahan command normalisasi data lama:
  - `register-aset:normalize-nomor` (`--dry-run` tersedia).

### C. KIR & Ringkasan Register Aset
- **Status: Selesai**
- Perbaikan query ringkasan `Register Aset (KIB & KIR)` agar unit kerja yang memiliki KIR tetap muncul walau data gudang unit belum lengkap.
- Penanganan mode unit virtual (`unit-{id_unit_kerja}`) agar tetap bisa membuka detail.
- Perbaikan crash view akibat pemanggilan method Eloquent pada object non-Eloquent (`stdClass`).

### D. Transformasi Menu KIR Menjadi Dokumen
- **Status: Selesai**
- Menu `Kartu Inventaris Ruangan (KIR)` diubah ke model **daftar dokumen per unit kerja**.
- Penambahan aksi dokumen:
  - `Download Dokumen`
  - `Cetak`
- Penambahan route dokumen unit kerja.
- Penambahan halaman dokumen KIR khusus (`document-unit`).
- Tombol `Tambah KIR` di halaman dokumen sudah dihapus agar fokus pada output dokumen.

### E. Template Dokumen KIR
- **Status: Selesai**
- Template dokumen dibuat meniru format resmi:
  - Header instansi
  - Tabel kolom inventaris lengkap
  - Blok tanda tangan
- Dokumen disusun per ruangan dalam unit kerja.
- Mode output dipisah:
  - **Cetak**: layout dinamis mengikuti pengaturan printer.
  - **Download**: ukuran tetap Officio/F4.
- Metadata pejabat/NIP sudah ditarik dari data master (tidak lagi placeholder).
- Penyesuaian pixel layout sudah dilakukan (header center, kepadatan tabel, blok tanda tangan) dan telah difinalisasi 1:1 sesuai template institusi.

### F. Searchable Select & UI Form
- **Status: Selesai (transisi ke Select2 + hardening event)**
- Migrasi pendekatan field select ter-enhance dari `Choices.js` ke `Select2` agar perilaku placeholder, clear, dan event change lebih stabil lintas form.
- Standardisasi inisialisasi global select dengan placeholder + search + allow clear.
- Perbaikan tampilan selected value (clipping, alignment vertikal, spacing) pada field yang sebelumnya terpotong/terdorong.
- Hardening kompatibilitas script lama:
  - helper lama `initChoicesForSelect` tetap dipertahankan sebagai facade agar halaman existing tidak langsung regress,
  - event tambahan `select2:select` / `select2:clear` dipasang di form yang memiliki toggle field dinamis.
- Perbaikan kasus dinamis yang sempat terdampak:
  - `Master Gudang`: `Kategori Gudang` langsung tampil saat memilih `PUSAT` (tanpa submit),
  - `Data Inventory`: pemilihan `FARMASI/PERSEDIAAN` langsung menampilkan field `No Batch` dan `Tanggal Kedaluwarsa`.

### G. QR Code & Storage
- **Status: Selesai**
- Perbaikan alur QR agar lebih aman pada server tanpa `imagick`.
- Penyesuaian fallback format QR (`svg`) saat `png` tidak memungkinkan.
- Pemastian storage link dan direktori QR siap tulis.

### H. Master Data Tambahan (Sesi Lanjutan)
- **Status: Selesai**
- Unit Kerja:
  - penambahan field wilayah kerja (`kota_kabupaten`, `kecamatan`) khusus cakupan DKI Jakarta,
  - validasi backend + dropdown dinamis create/edit.
- Sumber Anggaran:
  - sinkronisasi field `keterangan` via migration guard untuk environment yang belum memiliki kolom.
- Satuan:
  - penambahan field `keterangan` end-to-end (migration, model, controller, create/edit/index/show).
- Gudang:
  - penambahan `MasterGudangSeeder` untuk membentuk gudang sesuai unit kerja secara otomatis.
- Struktur referensi barang:
  - penggabungan data `bmd_aset_lancar_lengkap.xlsx` ke workbook import `kemendagri_import_sheet6_objek_filtered.xlsx` sesuai format sheet target (`aset` s.d `permendagri_108`) untuk memperkaya referensi aset lancar.

---

## 2) Pekerjaan Yang Belum Diselesaikan

### A. Finalisasi Dokumen KIR (Pixel-Perfect)
- **Status: Selesai**
- Penyetelan detail layout agar 100% sesuai format institusi:
  - ukuran font per kolom
  - tinggi baris
  - posisi header dan blok tanda tangan
  - kepadatan tabel saat banyak data
- Pengisian metadata pejabat/NIP dari data master (bukan placeholder). ✅

### B. Penegasan Domain Alur KIR vs Register Aset
- **Status: Selesai**
- Penyelarasan istilah UI agar user tidak bingung:
  - Dokumen KIR (ringkasan/cetak)
  - Rincian KIR (item-level)
  - Register Aset (master/detail + penanggung jawab)
- Konsolidasi naming menu/sidebar.

### C. Audit Konsistensi Data Lama
- **Status: Selesai**
- Pemeriksaan dan backfill data historis untuk relasi:
  - `register_aset.id_item`
  - `register_aset.id_unit_kerja`
  - `register_aset.id_ruangan`
- Validasi 1:1 antara Register Aset, Inventory Item, dan KIR.

### D. Hardening Pengujian
- **Status: Parsial Tinggi**
- Penambahan test fitur untuk:
  - simpan register aset + redirect ke input KIR + validasi pemisahan nomor register
  - mode download/cetak dokumen KIR
- Penambahan test otomatis maintenance (`MaintenanceFlowTest`) untuk:
  - generate maintenance rutin dari jadwal aktif,
  - service report selesai (close request + update kondisi aset + create riwayat),
  - proteksi auth/role untuk route maintenance,
  - akses report maintenance summary,
  - export CSV maintenance summary,
  - kalibrasi valid (close request + create riwayat),
  - kalibrasi non-valid (tidak menambah riwayat baru).
- UAT per role (admin, admin gudang, kepala unit, pegawai) masih perlu penutupan checklist manual lintas modul.

### E. Penyempurnaan Searchable Select (Regresi Menyeluruh)
- **Status: Parsial Tinggi**
- Audit lintas modul setelah transisi ke Select2 sedang berjalan untuk memastikan seluruh event interaktif form tetap normal.
- Titik kritis yang sudah ditutup:
  - toggle field dinamis pada `Master Gudang`,
  - toggle field dinamis pada `Data Inventory`,
  - pencegahan retry loop tidak perlu pada halaman `Permintaan Barang` yang sebelumnya menunggu `Choices` terus-menerus.
- Sisa: pembersihan penuh blok fallback legacy `new Choices(...)` yang masih tersisa di beberapa blade (mis. jalur fallback pada `permintaan-barang/create`) agar codebase lebih bersih dan risiko konflik jangka panjang makin kecil; baris dinamis untuk Satuan/Select2 sudah ditambahkan memakai `initChoicesForSelect` selaras `app-layout.js`.

### F. Integrasi TTE untuk Dokumen (Cetak/Download)
- **Status: Parsial — tahap 1 internal lanjutan (KIR)**
- Menambahkan jejak tanda tangan elektronik (TTE) pada dokumen yang dicetak/diunduh (awal fokus: Dokumen KIR).
- **Segel dokumen:** tabel `tte_document_seals`, hash snapshot kanonik, kode verifikasi, QR ke `/verifikasi-dokumen/{token}`; aktif saat unduhan (`download=1`) atau **`tte=1`** (cetak dari daftar dokumen KIR menyertakan `tte=1`).
- **TTE per penandatangan:** tabel `tte_document_signatures` — tiga peran (`kepala_pusat`, `pengurus_barang`, `kepala_unit`) terikat pegawai terpetakan (`resolveKirSignatories`); masing-masing penandatangan memproses **Tandatangani** terpisah (POST `dokumen-sign`); footer cetak dan halaman verifikasi menampilkan status/waktu per peran.
- Halaman verifikasi berbasis token; verifikasi hanya dengan **input kode** (tanpa membuka URL) dapat ditambahkan sebagai pelengkap.
- Menyiapkan opsi tahap lanjut integrasi PSrE resmi (untuk kebutuhan legal formal lintas instansi).

### G. Fitur Pemeliharaan (Permintaan -> Jadwal -> Service -> Riwayat)
- **Status: Parsial Tinggi (alur inti sudah berjalan)**
- **Tujuan:**
  - Menjadikan modul pemeliharaan sebagai alur end-to-end yang konsisten dari pengajuan sampai histori aset.
- **Ruang Lingkup Fungsional:**
  - Maintenance Rutin (Preventive) oleh Admin:
    - jadwal rutin dapat dibuat langsung oleh admin tanpa menunggu permintaan unit,
    - dukungan frekuensi berkala (mingguan/bulanan/triwulan/tahunan) per aset/kelompok aset,
    - reminder dan daftar kerja periodik untuk tim pelaksana.
  - Permintaan Pemeliharaan:
    - pengajuan oleh unit kerja/penanggung jawab aset,
    - validasi aset aktif dan keterkaitan dengan `register_aset`,
    - status lifecycle yang jelas (draft, diajukan, diproses, selesai, ditolak).
  - Jadwal Maintenance:
    - pembuatan jadwal dari permintaan yang disetujui,
    - support preventive/corrective,
    - reminder jatuh tempo.
  - Service Report:
    - pencatatan teknisi, tindakan, hasil, komponen/biaya, dan rekomendasi lanjutan,
    - update kondisi aset pasca service.
  - Kalibrasi Aset:
    - pencatatan jadwal dan pelaksanaan kalibrasi per aset,
    - penyimpanan hasil/status kalibrasi dan masa berlaku,
    - integrasi histori kalibrasi ke riwayat pemeliharaan aset.
  - Riwayat Pemeliharaan:
    - histori kronologis per aset,
    - keterkaitan 1 aset ke banyak aktivitas maintenance.
- **Integritas Data yang Wajib Dijaga:**
  - relasi wajib ke `register_aset.id_register_aset`,
  - konsistensi dengan `kartu_inventaris_ruangan` (ruangan dan penanggung jawab saat proses berjalan),
  - tidak boleh ada service report tanpa sumber jadwal/permintaan yang valid.
- **Output & Laporan:**
  - daftar aset jatuh tempo maintenance,
  - rekap pemeliharaan per unit kerja/periode,
  - histori detail pemeliharaan per aset untuk audit.
- **Pengujian (target):**
  - test pembuatan jadwal maintenance rutin oleh admin (tanpa permintaan),
  - test eksekusi maintenance rutin menjadi service report/riwayat,
  - test fitur alur penuh permintaan -> jadwal -> service -> riwayat,
  - test validasi role (admin, admin gudang, kepala unit, pegawai),
  - test regresi relasi terhadap modul register aset dan KIR.
- **Catatan Implementasi:**
  - utamakan status flow tunggal yang konsisten antar tabel,
  - gunakan event/log untuk jejak perubahan status agar mudah diaudit.
- **Progress Implementasi Saat Ini (sudah dikerjakan):**
  - modul `service-report` dan `kalibrasi-aset` sudah punya halaman index/create/edit/show (sebelumnya belum lengkap),
  - jadwal maintenance sudah mendukung indikator jatuh tempo (overdue dan 7 hari),
  - admin bisa generate permintaan rutin langsung dari jadwal aktif (tanpa input permintaan manual dari unit),
  - saat service selesai: status permintaan ditutup, kondisi aset diperbarui, dan riwayat pemeliharaan otomatis terbentuk,
  - saat kalibrasi valid: status permintaan ditutup (jika ada), riwayat kalibrasi masuk ke histori pemeliharaan,
  - validasi integritas permintaan diperketat (konsistensi unit kerja pemohon/aset + aset harus sudah terpasang di KIR).
  - test fitur terarah `MaintenanceFlowTest` sudah dibuat dan lulus:
    - generate permintaan rutin dari jadwal aktif,
    - service report selesai -> update kondisi aset + create riwayat,
    - proteksi akses auth/role pada route maintenance,
    - alur kalibrasi valid -> permintaan selesai + riwayat terbentuk,
    - alur kalibrasi non-valid -> tidak menambah riwayat selesai.
  - laporan baru `Rekap Pemeliharaan per Unit/Periode` sudah ditambahkan pada menu Reports:
    - filter unit kerja + rentang tanggal,
    - ringkasan status (total/selesai/gagal/dibatalkan),
    - tabel agregasi per unit termasuk total biaya service + kalibrasi.
  - export CSV untuk rekap pemeliharaan sudah tersedia (mengikuti filter aktif).
- **Sisa Penyelesaian Menuju Status Selesai:**
  - hardening pengujian otomatis tambahan (skenario data ekstrem & concurrency),
  - penyempurnaan format output rekap (opsi export dokumen selain CSV bila diperlukan),
  - final review UX minor pada form dan filter maintenance.

### H. Fitur Peminjaman Alat/Barang Antar Unit
- **Status: Selesai (alur prioritas tinggi sudah berjalan end-to-end)**
- **Tujuan:**
  - Menyediakan alur resmi peminjaman alat/barang antar unit dengan jejak approval berjenjang dan histori serah-terima/pengembalian.
- **Alur Proses (target implementasi terbaru):**
  - `Unit Kerja (Peminjam)` -> Ajukan
  - Verifikasi `Unit Kerja`
  - `Pengurus Barang` -> Approval + Disposisi
  - `Unit yang Dipinjam (Unit Pemilik)` -> Approval/Penolakan *(khusus antar unit)*
  - `Kasubag TU` -> Mengetahui
  - Serah Terima
  - Pengembalian oleh Unit Kerja
  - Finalisasi Pengembalian oleh Pengurus Barang
  - Selesai
- **Ruang Lingkup Fungsional:**
  - Form permintaan peminjaman dengan data barang, unit peminjam, **tujuan peminjaman** (`Unit Kerja lain` atau `Gudang Pusat`), unit pemilik (jika lintas unit), tanggal pinjam-rencana kembali, dan alasan.
  - Workflow status berjenjang sesuai alur di atas (dengan catatan persetujuan/penolakan di tiap tahap).
  - Pencatatan serah terima antar unit (waktu, petugas, kondisi barang saat serah).
  - Pencatatan pengembalian (waktu, kondisi akhir, catatan kerusakan/selisih bila ada).
  - Histori peminjaman per barang/aset dan per unit untuk audit.
- **Integritas Data yang Wajib Dijaga:**
  - Barang yang dipinjam harus valid dan terdaftar pada unit pemilik.
  - Selama status dipinjam, barang tidak boleh diproses pada transaksi yang bentrok (guard status).
  - Wajib ada jejak status dan user actor di setiap tahap approval/serah-terima/pengembalian.
- **Pengujian (target):**
  - Test alur penuh peminjaman dari pengajuan sampai selesai.
  - Test skenario penolakan di Unit B (untuk mode lintas unit).
  - Test skenario peminjaman langsung ke Gudang Pusat (tanpa tahap Unit B).
  - Test validasi role per tahap (Ka Unit A, Unit B, Pengurus Barang, Kasubag TU).
  - Test guard bentrok transaksi saat barang berstatus dipinjam.
- **Progress Implementasi Saat Ini (sudah dikerjakan):**
  - Modul baru `transaction/peminjaman-barang` sudah tersedia dengan halaman:
    - daftar/index peminjaman,
    - form pengajuan,
    - detail peminjaman + aksi workflow per status.
  - Alur status inti sudah disesuaikan ulang agar lebih jelas hirarki actor:
    - `DIAJUKAN (UNIT KERJA)` -> `DIVERIFIKASI (UNIT KERJA)`,
    - `DIAPPROVAL + DISPOSISI (PENGURUS BARANG)`,
    - `DIAPPROVAL (UNIT YANG DIPINJAM)` *(khusus antar unit)*,
    - `MENGETAHUI (KASUBAG TU)` -> `SERAH_TERIMA` -> `PENGEMBALIAN` -> `SELESAI`.
  - Form peminjaman sudah mendukung **multi-item** dalam satu dokumen:
    - add row dinamis barang/satuan/qty/keterangan,
    - validasi array item di backend,
    - guard duplikasi barang dalam satu dokumen,
    - guard bentrok untuk item yang masih berada di peminjaman aktif.
  - UI peminjaman sudah dirapikan:
    - konsistensi card filter index,
    - penyederhanaan label status/tujuan agar tidak ambigu,
    - penyempurnaan proporsi kolom item dan tombol aksi pada form multi-item.
  - Monitoring status lintas role pada index peminjaman sudah ditambahkan (kartu ringkas total, menunggu verifikasi unit, menunggu pengurus, menunggu unit dipinjam, menunggu pengembalian, selesai).
  - Test otomatis end-to-end sudah ditambahkan (`PeminjamanBarangFlowTest`) dan lulus:
    - alur antar unit dari pengajuan sampai selesai,
    - alur gudang pusat (tanpa tahap unit dipinjam),
    - validasi multi-item pada satu dokumen.
  - Penyimpanan riwayat status sudah tersedia pada tabel log khusus untuk audit proses.
  - Guard bentrok dasar sudah diterapkan: barang yang masih berada di peminjaman aktif tidak dapat diajukan ulang.
- **Sisa Penyelesaian Menuju Status Selesai:**
  - finalisasi UAT lintas role untuk alur baru (unit kerja -> pengurus -> unit pemilik -> kasubag TU -> pengembalian).

### I. Fitur Retur Barang Rusak (Terpisah dari Peminjaman)
- **Status: Selesai (implementasi inti + sinkronisasi data)**
- Fitur retur barang rusak dipisahkan dari modul `Peminjaman Barang` agar domain proses lebih jelas.
- Alur retur kini berdiri sendiri melalui modul `Retur Barang Rusak`:
  - pengajuan retur oleh unit kerja,
  - approval/tolak oleh pengurus/admin gudang,
  - penerimaan retur + update stok/inventory.
- Form retur sudah mendukung multi-item `add row` (1 dokumen bisa banyak item retur).
- Form retur tidak lagi bergantung pada transaksi `penerimaan`; sumber item diambil langsung dari inventory unit kerja (barang lama tetap dapat diretur).
- Jenis retur ditambahkan agar klasifikasi data lebih jelas:
  - `RUSAK`,
  - `SISA`,
  - `LAINNYA`.
- Tabel database sudah disesuaikan dengan domain baru:
  - `retur_barang`: hapus `id_penerimaan`, `id_distribusi`, `keterangan`,
  - `detail_retur_barang`: hapus `keterangan`,
  - migration penyesuaian sudah ditambahkan dan sinkron dengan backend.
- Seeder dummy dan query lama yang masih memakai kolom lama sudah disesuaikan.

---

## 3) Rekomendasi Prioritas Lanjutan

### Prioritas Tinggi
1. Finalisasi format dokumen KIR (layout final institusi). ✅
2. Audit data relasi KIR/Register Aset untuk memastikan akurasi laporan. ✅ (tervalidasi via `register-aset:audit-conflicts`)
3. Validasi end-to-end alur dokumen (download/cetak) per unit kerja. ✅ (tervalidasi lewat `KirDokumenFlowTest` + `RegisterAsetKirFlowTest`)
4. Penguatan fitur pemeliharaan. ✅ (tervalidasi lewat `MaintenanceFlowTest` + guard relasi KIR pada generate rutin)
   - finalisasi alur permintaan pemeliharaan -> jadwal -> service report -> riwayat,
   - validasi relasi ke register aset/KIR,
   - verifikasi output laporan pemeliharaan per unit kerja.
5. Hardening pengujian fitur pemeliharaan end-to-end + role-based access. ✅ (tervalidasi lewat `MaintenanceFlowTest`)
6. Finalisasi fitur peminjaman antar unit/gudang pusat (alur actor + multi-item + hardening test). ✅
   - status alur actor sudah dirapikan dan diimplementasikan,
   - multi-item pengajuan sudah berjalan,
   - monitoring status lintas role di index tersedia,
   - test end-to-end lintas cabang sudah tersedia dan lulus.
7. Pemisahan fitur retur barang rusak dari peminjaman + sinkronisasi data database. ✅
   - modul retur berdiri sendiri, tidak lagi menumpang status peminjaman,
   - form retur multi-item berbasis inventory unit (tanpa ketergantungan penerimaan),
   - skema database dan seeder telah diselaraskan.
8. Koreksi logika stock untuk jenis inventory ASET. ✅
   - gejala lama: kuantitas ASET ikut tercermin di `data_stock` padahal domain ASET mengikuti `inventory_item`/register, bukan stok gudang persediaan/farmasi,
   - implementasi saat ini: **`DataInventoryController`** hanya memanggil `updateStock()` / sinkron `DataStock` untuk **`PERSEDIAAN`/`FARMASI`**; untuk **`ASET`** dibuat **`InventoryItem`** lewat **`DataInventoryObserver`** (tanpa menulis baris kuantitas ke `data_stock`),
   - catatan: narasi “observer memanggil updateStock untuk ASET” mengacu ke bug historis; di kode sekarang pemisahan tanggung jawab observer vs kontroller sudah jelas.
9. Pembersihan menyeluruh konfirmasi bawaan browser (`confirm()`) ke modal konfirmasi custom. ✅
   - migrasi menyeluruh ke atribut `data-confirm` pada form/aksi modul utama telah selesai,
   - pemindaian `resources/views/**/*.blade.php` tidak lagi menemukan pemanggilan `confirm(`.
10. Hardening pengujian add-row multi-item untuk modul operasional baru. ✅
   - cakupan target: `Stock Adjustment` multi-row dan `Permintaan Pemeliharaan` multi-row,
   - target: validasi duplikasi row, empty row, nilai negatif, dan stabilitas submit multi-item.
11. Penguatan uji concurrency generator nomor dokumen. ✅
   - fokus pada generator nomor dokumen transaksi (mis. `PMJ`, `PMH`) saat submit paralel,
   - target: hindari bentrok nomor dan pastikan fallback retry berjalan stabil.
12. Audit konsistensi data stok vs inventory pasca perubahan logika eligible stok. ✅
   - fokus pada aturan baru: stok menampilkan `PERSEDIAAN/FARMASI` + `ASET` tertentu sesuai kriteria,
   - target: pastikan data historis tidak nyangkut/overcount setelah sinkronisasi aturan terbaru.
13. Distribusi barang → pengurangan stok gudang asal. ✅ *(sesuai kode terkini)*
   - pengurangan **`DataStock`** (gudang asal, `qty_keluar`/`qty_akhir`) dilakukan pada saat aksi **`kirim`** distribusi (`DistribusiService::kirim`), untuk baris inventaris **`PERSEDIAAN`/`FARMASI`** dengan guard `StockGuardService`,
   - tahap **`proses`** hanya mengubah status dokumen ke *Diproses*, belum mengurangi stok,
   - setelah kirim: status distribusi menyelesai, pembuatan **`PenerimaanBarang`** otomatis jika memenuhi syarat unit/pegawai penerima.

### Prioritas Menengah
1. Rapikan istilah menu dan deskripsi halaman (mengurangi ambiguitas user). ✅ (istilah maintenance/service distandarkan ke pemeliharaan/laporan servis pada menu + halaman utama)
2. Tambahkan test coverage untuk alur utama KIR dan register aset. ✅
   - `KirDokumenFlowTest`: tambah skenario akses ditolak untuk pegawai yang mencoba membuka dokumen KIR unit lain.
   - `RegisterAsetKirFlowTest`: tambah skenario mode route virtual unit (`unit-{id}`) dan proteksi akses lintas unit.
3. Implementasi TTE tahap 1 pada dokumen cetak/download (kode verifikasi + QR + verifikasi). **Parsial** — segel KIR + QR/token + **TTE multi-peran** (tiga penandatangan) + halaman verifikasi per peran; **sisa:** verifikasi lewat **input kode** saja, log audit akses, integrasi PSrE bila diperlukan.
4. Finalisasi konsistensi UI modul retur (`index/show/edit`) agar seluruh label lama berbasis penerimaan/distribusi sudah bersih total. ✅ **(2026-05-02)**
   - kolom/link **No Penerimaan** dan referensi **penerimaan/distribusi** dihapus dari index & show; form **edit** diselaraskan dengan **create** (detail dari inventory unit, tanpa dropdown penerimaan).
5. Tambahkan test terarah untuk alur retur terpisah (create multi-item, approve/tolak, update stok pusat/unit). ✅ **`ReturBarangFlowTest`** — create dari inventory unit + **`terima`** (status **DITERIMA**); guest tidak akses index.
6. Koreksi/penegasan definisi `Qty Awal` pada laporan `Kartu Stok` (saat ini masih bernilai 0 pasca import/adjustment) agar sesuai domain bisnis yang diinginkan. ✅ **(2026-05-02)**
   - di `ReportController::kartuStok`, untuk baris dengan `qty_awal` ~0 namun ada mutasi, ditampilkan **saldo awal implisit** \(Qty akhir − Qty masuk + Qty keluar\) dengan penanda **implisit** di view `kartu-stok`.

### Prioritas Rendah
1. Penyempurnaan visual minor pada selectable/search fields.
2. Optimasi UX tambahan (mis. auto-close tab print mode, badge status dokumen).

---

## 4) Update Perubahan Terbaru (Patch Terkini)

### A. Backend
- Penambahan flow generate permintaan maintenance rutin dari jadwal aktif.
- Penguatan validasi integritas permintaan maintenance:
  - unit pemohon = unit aset,
  - aset wajib sudah punya penempatan KIR.
- Otomasi sinkron status:
  - service selesai -> request selesai + kondisi aset update + riwayat upsert,
  - kalibrasi valid -> request selesai + riwayat upsert.
- Penambahan laporan `reports.maintenance-summary` + export `reports.maintenance-summary.export` (CSV).
- Penyesuaian validasi file maintenance:
  - `service-report` dan `kalibrasi-aset` menerima `pdf/doc/docx/jpg/jpeg/png`,
  - file dari mode kamera diprioritaskan saat tersimpan jika tersedia.
- Master Jabatan:
  - field `urutan` pada create diubah auto-generate (`max + 1`),
  - edit jabatan tidak lagi mengubah `urutan` secara manual.
- Peminjaman Barang:
  - alur transisi status disesuaikan ke hirarki actor terbaru (unit kerja -> pengurus -> unit pemilik -> kasubag -> serah terima -> pengembalian -> selesai),
  - validasi `store` diubah ke skema `items[]` (multi-item per dokumen),
  - penambahan guard duplikasi item pada satu dokumen dan guard bentrok item aktif lintas transaksi.

### B. Frontend (Blade/UI)
- Standarisasi istilah menu/deskripsi agar lebih konsisten untuk pengguna:
  - `Permintaan Unit` -> `Permintaan & Peminjaman`,
  - `Peminjaman Barang Antar Unit` -> `Peminjaman Barang`,
  - `Jadwal Maintenance` -> `Jadwal Pemeliharaan`,
  - `Service Report` -> `Laporan Servis`.
- Halaman maintenance dilengkapi menjadi end-to-end:
  - `jadwal-maintenance` (index/create/edit/show),
  - `service-report` (index/create/edit/show),
  - `kalibrasi-aset` (index/create/edit/show).
- Dashboard laporan (`report/index`) ditambah kartu `Rekap Pemeliharaan`.
- Halaman `report/maintenance-summary`:
  - filter unit kerja + periode,
  - kartu ringkasan status,
  - tabel agregasi per unit + total biaya,
  - tombol export CSV.
- Form `service-report`:
  - field `teknisi` menjadi dropdown dari master pegawai dengan jabatan yang mengandung kata "teknisi",
  - upload file dibedakan dengan tombol + badge (`UPLOAD`/`KAMERA`).
- Form `kalibrasi-aset`:
  - upload file sertifikat dibedakan dengan tombol + badge (`UPLOAD`/`KAMERA`).
- Preview file maintenance:
  - di form create/edit: foto ditampilkan langsung, PDF/DOC/DOCX ditampilkan sebagai link,
  - di halaman detail (`show`): foto ditampilkan langsung, PDF/DOC/DOCX ditampilkan sebagai link.
- Modul Peminjaman Barang:
  - halaman detail (`show`) dirapikan dengan ringkas alur actor yang lebih eksplisit,
  - tombol aksi workflow disesuaikan mengikuti istilah actor yang lebih jelas,
  - form create mendukung add-row multi-item,
  - tabel item create dirapikan (proporsi kolom barang/satuan/qty/keterangan + kolom aksi ikon yang konsisten),
  - card filter index dirapikan agar konsisten dengan halaman transaksi lain.

### C. Pengujian Otomatis
- File test yang aktif dipakai hardening: `tests/Feature/MaintenanceFlowTest.php`.
- Cakupan saat ini sudah meliputi alur rutin, service, kalibrasi, akses role/auth, report summary, export, serta guard kegagalan generate rutin jika aset belum memiliki penempatan KIR.
- Status terakhir eksekusi test terarah: lulus.

### D. Ringkasan Yang Belum
- Skenario test ekstrem/concurrency untuk maintenance.
- Opsi export non-CSV (jika dibutuhkan pemangku kepentingan).
- **TTE dokumen:** tahap internal (segel + multi-penandatangan KIR) sudah berjalan; **belum** BSrE/PSrE resmi; pelengkap: verifikasi kode-only, audit log.
- Hardening notifikasi + dashboard monitoring status lintas role untuk peminjaman barang.
- Finalisasi UAT manual lintas role untuk alur peminjaman barang (antar unit vs gudang pusat).
- *(UI retur + test retur + Kartu Stok Qty awal: terselesaikan 2026-05-02 — lihat Prioritas Menengah #4–#6.)*

---

## 5) Update Sesi Pemulihan Perubahan (2026-04-28)

### A. Pemulihan Kode Setelah Working Tree Rusak
- **Status: Selesai**
- Dilakukan pemulihan environment saat `artisan` sempat tidak terbaca (`Could not open input file: artisan`).
- Dilakukan backup perubahan lokal ke stash sebelum recovery untuk mencegah kehilangan data.
- Working tree dipulihkan agar file inti Laravel kembali normal dan server dapat berjalan.
- Verifikasi berhasil:
  - `php artisan --version` berjalan normal,
  - `php artisan serve` kembali aktif.

### B. Re-implement Fitur Yang Sempat Hilang
- **Status: Selesai**
- Seluruh perubahan prioritas dari awal percakapan di-apply ulang ke source code:
  - menu + halaman **Pengembalian Barang** terpisah dari daftar Peminjaman,
  - route index pengembalian khusus (`transaction.pengembalian-barang.index`),
  - form pengembalian per-item (`items[].kondisi_kembali`) berdasarkan detail pinjaman,
  - akses form pengembalian untuk `admin` dan `pegawai` (pegawai tetap dibatasi unit peminjam),
  - tombol di halaman detail peminjaman diarahkan ke halaman form pengembalian khusus,
  - modul `Pemakaian Barang` dinonaktifkan (route dinonaktifkan + controller hard abort 404),
  - tampilan nama file terpilih di halaman import struktur barang,
  - pembersihan `confirm()` native pada form delete gudang,
  - penguatan migrasi confirm legacy (`onsubmit/onclick`) ke modal konfirmasi custom,
  - penyesuaian overlay loading agar tidak menutup interaksi sidebar,
  - penyesuaian field dinamis peminjaman berdasarkan tujuan (`UNIT` vs `GUDANG_PUSAT`),
  - penyesuaian field `Data Barang` pada Data Inventory:
    - tampil & wajib untuk `ASET`,
    - disembunyikan untuk `PERSEDIAAN/FARMASI` dengan fallback backend aman.

### C. Verifikasi Teknis Setelah Pemulihan
- **Status: Selesai**
- Validasi sintaks controller utama: lulus (`php -l`).
- Validasi route baru pengembalian: terdaftar di route list.
- Test otomatis alur peminjaman: lulus (`PeminjamanBarangFlowTest`).
- Build aset frontend: lulus (`npm run build`).
- Pembersihan cache Laravel: selesai (`php artisan optimize:clear`).

### D. Update Repository GitHub
- **Status: Selesai**
- Commit pemulihan berhasil dibuat:
  - `625828c`
  - `Restore loan return workflow, inventory form rules, and custom confirm modal behavior.`
- Perubahan berhasil di-push ke remote branch:
  - `origin/sistem-ppkp-terintegrasi`
- Catatan distribusi:
  - perubahan sudah ada di branch kerja,
  - belum masuk ke `main` sebelum proses PR/merge.

### E. Catatan Sisa Kecil
- Masih ada 1 file untracked lokal yang belum dimasukkan commit:
  - `database/seeders/data/tes kir.pdf`

---

## 6) Update Sesi Lanjutan (2026-04-29)

### A. Hardening Inventory, Stok, dan Konsistensi Data
- **Status: Selesai**
- Pemisahan jelas: **`DataInventoryController`** mengurus sinkron **`DataStock`** hanya untuk **`PERSEDIAAN`/`FARMASI`**; **`DataInventoryObserver`** mengurus pembuatan/penyesuaian **`InventoryItem`** untuk **`ASET`** (bukan penulisan kuantitas ke `data_stock`).
- Perbaikan deteksi perubahan observer (`isDirty` -> `wasChanged`) pada event update.
- Penambahan penanganan penurunan qty `ASET`:
  - item berlebih yang belum terikat register dinonaktifkan (`NONAKTIF`) agar sinkron dengan qty terbaru.
- Perbaikan pengurangan stok lama saat perubahan jenis inventory agar menggunakan nilai relasi lama (barang + gudang lama), bukan nilai sesudah update.

### B. Hardening Nomor Dokumen & Error SQL
- **Status: Selesai**
- Penguatan generator `no_peminjaman`:
  - lock saat baca nomor terakhir,
  - validasi kandidat nomor unik,
  - retry saat terjadi duplikasi pada kondisi paralel.
- Perbaikan error SQL ambigu pada peminjaman:
  - qualifier kolom `register_aset.id_inventory` pada query join untuk mencegah `Column 'id_inventory' is ambiguous`.

### C. UI/UX Form & Konfirmasi Aksi
- **Status: Selesai**
- Perbaikan route yang dinonaktifkan di dashboard agar tidak memicu `RouteNotFoundException`.
- Perbaikan form import struktur barang:
  - mode tombol file dipastikan tampil stabil,
  - mode compact: hanya tombol `Choose File` + nama file terpilih.
- Perbaikan konfirmasi hapus di Data Inventory:
  - migrasi dari `confirm()` native browser ke modal konfirmasi custom (`data-confirm`).
- Penyesuaian form Data Inventory:
  - field `Data Barang` tetap tampil untuk `ASET/PERSEDIAAN/FARMASI`.
- Perbaikan auto-isi gudang pada create Data Inventory agar fallback lebih robust saat kategori tidak match persis.

### D. Penyempurnaan Laporan Kartu Stok
- **Status: Selesai**
- Penambahan kolom baru pada tabel Kartu Stok:
  - `Merek`,
  - `No Batch`,
  - `Expired Date`.
- Data kolom tambahan diambil dari inventory terbaru per kombinasi barang + gudang.

### E. Add-Row Multi Item (Form Operasional)
- **Status: Selesai**
- Stock Adjustment:
  - form create mendukung add-row multi stock dalam satu submit,
  - aksi baris menggunakan icon hapus,
  - backend store dipisah per row dalam satu transaksi.
- Permintaan Pemeliharaan:
  - form create mendukung add-row multi aset dalam satu submit,
  - setiap row menyimpan jenis pemeliharaan, prioritas, dan deskripsi kerusakan per aset,
  - backend store membuat beberapa dokumen permintaan sekaligus dengan nomor berurutan.

### F. Catatan Verifikasi
- Validasi sintaks file yang diubah telah dijalankan (`php -l`) dan lulus.
- Linter untuk file utama yang diubah: tidak ada error baru.

### G. Finalisasi Prioritas Tinggi Tambahan (2026-04-29)
- **Status: Selesai**
- Konfirmasi aksi lintas modul:
  - migrasi massal `onsubmit/onclick` legacy berbasis `confirm()` ke `data-confirm`,
  - seluruh view utama kini konsisten memakai modal konfirmasi global.
- Hardening dokumen nomor `PMH`:
  - generator nomor permintaan pemeliharaan dibuat aman untuk submit paralel (lock + unique candidate scan).
- Hardening pengujian multi-row:
  - test validasi duplikasi + nilai negatif pada `Stock Adjustment`,
  - test validasi duplikasi register + pembuatan multi dokumen pada `Permintaan Pemeliharaan`.
- Audit stok vs inventory:
  - command `inventory:reconcile-stock` kini juga mendeteksi dan membentuk row `data_stock` yang hilang (mode `--fix`).
- Catatan eksekusi test:
  - eksekusi test fitur terblokir koneksi MySQL lokal (`127.0.0.1:3306` tidak aktif) sehingga verifikasi runtime penuh menunggu DB aktif.

### H. Update Lanjutan UI + Sumber Data Form (2026-04-29)
- **Status: Selesai**
- Penyelarasan UI form operasional:
  - layout posisi form `Permintaan Pemeliharaan` disamakan dengan pola form `Peminjaman Barang` (blok informasi, detail item, keterangan, dan area tombol aksi).
- Sumber data form berbasis inventory aktif:
  - `Permintaan Barang` (opsi "Dari master"): daftar kandidat dibentuk dari **`data_inventory` aktif** (`PERSEDIAAN`/`FARMASI`) → dipetakan ke **`master_data_barang`** (helper `getBarangMasterFromInventory()` di `PermintaanBarangController`), **bukan** seluruh master murni; angka stok untuk validasi/UI tetap mengacu ke **`DataStock`** / helper terpusat sesuai sub jenis.
  - `Peminjaman Barang` daftar barang form create disesuaikan agar kandidat berasal dari `data_inventory` aktif.
  - `Permintaan Pemeliharaan` daftar register aset pada create/edit dibatasi ke register yang memiliki relasi inventory aktif.
- Hardening UX Permintaan/Approval Barang:
  - Dropdown "Data Barang" tidak lagi menyembunyikan seluruh opsi saat checkbox `jenis_permintaan[]` belum ter-check (agar tidak tampil kosong).
  - Kolom/field "Stock Tersedia" disembunyikan di form create/edit Permintaan Barang (wrapper dengan kelas `hidden` pada blok stok; markup kolom tetap ada untuk logika JS bila diperlukan).
  - Di halaman approval Permintaan Barang, "Stock Tersedia" tampil hanya saat step >= 3 (Kepala Subbag/Kasubbag TU).
- Hardening UX Stock Adjustment:
  - Label dropdown `Data Stock` tidak lagi menampilkan angka stock (dipindahkan ke kolom `Qty Saat Ini` terpisah) dan `Qty Saat Ini` dibaca dari atribut `data-qty-akhir`.
- Hardening UX Data Inventory:
  - perbaikan auto-fill `Gudang` pada form create agar tidak terisi saat `Jenis Inventory` masih kosong.
- Dashboard ringkasan:
  - kartu `Stok Gudang` diganti menjadi metrik nilai,
  - ditambahkan pemisahan `Nilai Persediaan` dan `Nilai Farmasi` agar ringkasan lebih representatif dibanding qty stok gabungan.

---

## 7) Penyesuaian Dokumen dengan Kode (audit 2026-05-02)

Bagian ini mencatat keselarasan dokumen dengan implementasi terbaru tanpa mengubah riwayat sesi di atas.

### A. Permintaan Barang (master & UI)
- Sumber opsi "Dari master": **`data_inventory` aktif** (lihat §6.H yang telah dikoreksi); validasi kuantitas terhadap stok pusat memakai **`DataStock`** / `PermintaanBarangStock`.
- Form create/edit: kolom **Stock Tersedia** tidak ditampilkan user (blok `permintaan-detail-stock hidden`); script tetap dapat mengisi `.stock-display` untuk keperluan internal/approval.
- Baris detail tambahan (**Tambah Item**): kolom **Satuan** diinisialisasi dengan **`window.initChoicesForSelect`** (Select2 via facade layout), konsisten dengan baris pertama; helper **`setPermintaanSatuanValue`** menyelaraskan nilai saat barang dipilih jika Select2 aktif (create & edit).

### B. Distribusi & penerimaan
- **`DistribusiService::kirim`**: mengurangi stok **`PERSEDIAAN`/`FARMASI`** di gudang asal, menyelesaikan distribusi, memperbarui status permintaan terkait, dan membuat **`PenerimaanBarang`** otomatis bila kriteria terpenuhi.
- Resource **`penerimaan-barang`** dan endpoint **`distribusi.detail`** mendukung alur lanjutan dari dokumen distribusi.

### C. Yang tetap backlog / parsial
- **TTE** dokumen: **parsial** — segel + multi-penandatangan internal untuk KIR (§2.F); integrasi **PSrE/BSrE** dan fitur pelengkap (verifikasi kode-only, audit) masih roadmap.
- **Sisa `new Choices(...)`** di beberapa blade: masih dapat dibersihkan bertahap (sesuai §2.E).
- **UAT manual** lintas role: masih terbuka untuk beberapa modul (peminjaman, dll.).
- **Prioritas Menengah #4–#6 (2026-05-02):** UI retur dibersihkan dari referensi penerimaan/distribusi; **`ReturBarangFlowTest`** menambah cakupan retur; **Kartu Stok** menampilkan qty awal implisit bila `qty_awal` di DB nol (lihat §3 Prioritas Menengah).
- **UX & akses Inventory (2026-05-03):** tombol **Import data** pada halaman Data Inventory; permission `inventory.data-inventory.import.*` di `PermissionHelper` / `PermissionSeeder`; perbaikan highlight sidebar **Data Inventory** vs **Import Data Inventory** (`layouts/app.blade.php`).

---

## 8) Pembaruan dokumentasi & sinkron status (2026-05-03)

### A. Tujuan blok ini
- Menyatukan narasi dokumen dengan fitur terbaru (TTE multi-peran, import inventory, sidebar) tanpa mengubah riwayat sesi historis di §5–§6.

### B. Referensi teknis cepat (TTE)
- Migrasi: `tte_document_seals`, `tte_document_signatures`.
- Layanan: `App\Services\Tte\TteSealService`.
- Desain: `docs/TTE_DESAIN_TAHAP_1.md`.
- Route: halaman verifikasi publik `verifikasi-dokumen.show`; aksi tanda tangan `asset.kartu-inventaris-ruangan.dokumen-sign` (POST).

### C. Ringkasan tiga jalur *(sinkron dengan tabel di pembuka dokumen)*
- **Sudah selesai:** lihat baris pertama tabel **Status ringkas** di atas.
- **Sedang dikerjakan / parsial:** TTE pelengkap (kode-only, audit), pembersihan Choices, UAT, hardening maintenance.
- **Akan dikerjakan:** PSrE/BSrE, opsi akses role untuk penandatangan KIR, prioritas rendah UX/notifikasi.
