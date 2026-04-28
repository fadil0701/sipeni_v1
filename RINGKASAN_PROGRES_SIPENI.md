# Ringkasan Progres Pengembangan SIPENI

Dokumen ini merangkum pekerjaan yang sudah diselesaikan dan daftar pekerjaan lanjutan yang belum dikerjakan pada sesi pengembangan terbaru.

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
- Sisa: pembersihan penuh blok fallback legacy `new Choices(...)` yang sudah tidak diperlukan agar codebase lebih bersih dan risiko konflik jangka panjang makin kecil.

### F. Integrasi TTE untuk Dokumen (Cetak/Download)
- **Status: Backlog (belum mulai implementasi)**
- Menambahkan jejak tanda tangan elektronik (TTE) pada dokumen yang dicetak/diunduh (awal fokus: Dokumen KIR).
- Tahap 1 (internal): kode verifikasi + QR verifikasi + hash dokumen + log metadata penandatangan/waktu.
- Menyediakan halaman verifikasi dokumen berdasarkan token/kode verifikasi.
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
8. Koreksi logika stock untuk jenis inventory ASET (bug sinkronisasi observer). 🔴
   - saat input/update `Data Inventory` dengan `jenis_inventory = ASET`, data masih ikut masuk ke `data_stock`,
   - akar masalah: `DataInventoryObserver` masih memanggil `updateStock()` untuk alur ASET,
   - target perbaikan: batasi update stock hanya untuk `PERSEDIAAN/FARMASI` agar ASET tidak tercatat sebagai stok kuantitas gudang.

### Prioritas Menengah
1. Rapikan istilah menu dan deskripsi halaman (mengurangi ambiguitas user). ✅ (istilah maintenance/service distandarkan ke pemeliharaan/laporan servis pada menu + halaman utama)
2. Tambahkan test coverage untuk alur utama KIR dan register aset.
3. Implementasi TTE tahap 1 pada dokumen cetak/download (kode verifikasi + QR + verifikasi).
4. Finalisasi konsistensi UI modul retur (`index/show/edit`) agar seluruh label lama berbasis penerimaan/distribusi sudah bersih total.
5. Tambahkan test terarah untuk alur retur terpisah (create multi-item, approve/tolak, update stok pusat/unit).

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
- Integrasi TTE (masih backlog, sengaja ditunda sesuai arahan).
- Hardening notifikasi + dashboard monitoring status lintas role untuk peminjaman barang.
- Finalisasi UAT manual lintas role untuk alur peminjaman barang (antar unit vs gudang pusat).
- Penyelarasan akhir UI modul retur agar seluruh wording lama (penerimaan/distribusi) konsisten dengan domain retur terpisah.
- Penambahan test otomatis khusus modul retur terpisah untuk mencegah regresi lintas migrasi skema baru.

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
- Perbaikan observer inventory agar alur `ASET` tidak lagi tersinkron ke `data_stock`.
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
