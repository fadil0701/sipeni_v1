<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ComprehensiveDummySeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $now = now();

            // Master dasar
            $satuanId = DB::table('master_satuan')->where('nama_satuan', 'Unit')->value('id_satuan')
                ?? DB::table('master_satuan')->insertGetId([
                    'nama_satuan' => 'Unit',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            $anggaranId = DB::table('master_sumber_anggaran')->where('nama_anggaran', 'APBD Dummy')->value('id_anggaran')
                ?? DB::table('master_sumber_anggaran')->insertGetId([
                    'nama_anggaran' => 'APBD Dummy',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            $programId = DB::table('master_program')->where('nama_program', 'Program Dummy SIMANTIK')->value('id_program')
                ?? DB::table('master_program')->insertGetId([
                    'nama_program' => 'Program Dummy SIMANTIK',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            $kegiatanId = DB::table('master_kegiatan')->where('nama_kegiatan', 'Kegiatan Dummy')->value('id_kegiatan')
                ?? DB::table('master_kegiatan')->insertGetId([
                    'id_program' => $programId,
                    'nama_kegiatan' => 'Kegiatan Dummy',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            $subKegiatanId = DB::table('master_sub_kegiatan')->where('kode_sub_kegiatan', 'SUB-DUMMY-001')->value('id_sub_kegiatan')
                ?? DB::table('master_sub_kegiatan')->insertGetId([
                    'id_kegiatan' => $kegiatanId,
                    'nama_sub_kegiatan' => 'Sub Kegiatan Dummy',
                    'kode_sub_kegiatan' => 'SUB-DUMMY-001',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            // Unit kerja + gudang + ruangan
            $ukPusatId = DB::table('master_unit_kerja')->where('kode_unit_kerja', 'UK-PUSAT')->value('id_unit_kerja')
                ?? DB::table('master_unit_kerja')->insertGetId([
                    'kode_unit_kerja' => 'UK-PUSAT',
                    'nama_unit_kerja' => 'Unit Kerja Pusat Dummy',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            $ukAId = DB::table('master_unit_kerja')->where('kode_unit_kerja', 'UK-A')->value('id_unit_kerja')
                ?? DB::table('master_unit_kerja')->insertGetId([
                    'kode_unit_kerja' => 'UK-A',
                    'nama_unit_kerja' => 'Unit Kerja A Dummy',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            $ukBId = DB::table('master_unit_kerja')->where('kode_unit_kerja', 'UK-B')->value('id_unit_kerja')
                ?? DB::table('master_unit_kerja')->insertGetId([
                    'kode_unit_kerja' => 'UK-B',
                    'nama_unit_kerja' => 'Unit Kerja B Dummy',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            $gudangPusatId = DB::table('master_gudang')->where('nama_gudang', 'Gudang Pusat Dummy')->value('id_gudang')
                ?? DB::table('master_gudang')->insertGetId([
                    'id_unit_kerja' => $ukPusatId,
                    'nama_gudang' => 'Gudang Pusat Dummy',
                    'jenis_gudang' => 'PUSAT',
                    'kategori_gudang' => 'PERSEDIAAN',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            $gudangUnitAId = DB::table('master_gudang')->where('nama_gudang', 'Gudang Unit A Dummy')->value('id_gudang')
                ?? DB::table('master_gudang')->insertGetId([
                    'id_unit_kerja' => $ukAId,
                    'nama_gudang' => 'Gudang Unit A Dummy',
                    'jenis_gudang' => 'UNIT',
                    'kategori_gudang' => 'PERSEDIAAN',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            $gudangUnitBId = DB::table('master_gudang')->where('nama_gudang', 'Gudang Unit B Dummy')->value('id_gudang')
                ?? DB::table('master_gudang')->insertGetId([
                    'id_unit_kerja' => $ukBId,
                    'nama_gudang' => 'Gudang Unit B Dummy',
                    'jenis_gudang' => 'UNIT',
                    'kategori_gudang' => 'PERSEDIAAN',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            $ruangAId = DB::table('master_ruangan')->where('kode_ruangan', 'R-A-001')->value('id_ruangan')
                ?? DB::table('master_ruangan')->insertGetId([
                    'id_unit_kerja' => $ukAId,
                    'kode_ruangan' => 'R-A-001',
                    'nama_ruangan' => 'Ruang Unit A Dummy',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            $ruangBId = DB::table('master_ruangan')->where('kode_ruangan', 'R-B-001')->value('id_ruangan')
                ?? DB::table('master_ruangan')->insertGetId([
                    'id_unit_kerja' => $ukBId,
                    'kode_ruangan' => 'R-B-001',
                    'nama_ruangan' => 'Ruang Unit B Dummy',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            // Jabatan
            $jabatanPegawaiId = DB::table('master_jabatan')->where('nama_jabatan', 'Pegawai Dummy')->value('id_jabatan')
                ?? DB::table('master_jabatan')->insertGetId([
                    'nama_jabatan' => 'Pegawai Dummy',
                    'urutan' => 999,
                    'role_id' => DB::table('roles')->where('name', 'pegawai')->value('id'),
                    'deskripsi' => 'Jabatan dummy untuk uji coba',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            // Pegawai dummy (tanpa membuat user)
            $this->createPegawaiDummy('NIPDUMMY0001', 'Admin Gudang Dummy', 'dummy.admin.gudang@simantik.test', $ukPusatId, $jabatanPegawaiId);
            $pegawaiAId = $this->createPegawaiDummy('NIPDUMMY0002', 'Pegawai Unit A Dummy', 'dummy.pegawai.a@simantik.test', $ukAId, $jabatanPegawaiId);
            $pegawaiBId = $this->createPegawaiDummy('NIPDUMMY0003', 'Pegawai Unit B Dummy', 'dummy.pegawai.b@simantik.test', $ukBId, $jabatanPegawaiId);

            // Hierarki barang dummy
            $asetId = DB::table('master_aset')->where('nama_aset', 'Aset Dummy')->value('id_aset')
                ?? DB::table('master_aset')->insertGetId([
                    'nama_aset' => 'Aset Dummy',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            $kodeBarangId = DB::table('master_kode_barang')->where('kode_barang', 'KB-DUMMY-001')->value('id_kode_barang')
                ?? DB::table('master_kode_barang')->insertGetId([
                    'id_aset' => $asetId,
                    'kode_barang' => 'KB-DUMMY-001',
                    'nama_kode_barang' => 'Kode Barang Dummy',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            $kategoriId = DB::table('master_kategori_barang')->where('kode_kategori_barang', 'KT-DMY')->value('id_kategori_barang')
                ?? DB::table('master_kategori_barang')->insertGetId([
                    'id_kode_barang' => $kodeBarangId,
                    'kode_kategori_barang' => 'KT-DMY',
                    'nama_kategori_barang' => 'Kategori Dummy',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            $jenisId = DB::table('master_jenis_barang')->where('kode_jenis_barang', 'JN-DMY')->value('id_jenis_barang')
                ?? DB::table('master_jenis_barang')->insertGetId([
                    'id_kategori_barang' => $kategoriId,
                    'kode_jenis_barang' => 'JN-DMY',
                    'nama_jenis_barang' => 'Jenis Dummy',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            $subJenisId = DB::table('master_subjenis_barang')->where('kode_subjenis_barang', 'SJ-DMY')->value('id_subjenis_barang')
                ?? DB::table('master_subjenis_barang')->insertGetId([
                    'id_jenis_barang' => $jenisId,
                    'kode_subjenis_barang' => 'SJ-DMY',
                    'nama_subjenis_barang' => 'Sub Jenis Dummy',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            $barangPersediaanId = DB::table('master_data_barang')->where('kode_data_barang', 'BRG-DMY-001')->value('id_data_barang')
                ?? DB::table('master_data_barang')->insertGetId([
                    'id_subjenis_barang' => $subJenisId,
                    'id_satuan' => $satuanId,
                    'kode_data_barang' => 'BRG-DMY-001',
                    'nama_barang' => 'Barang Persediaan Dummy',
                    'deskripsi' => 'Dummy untuk uji transaksi persediaan',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            $barangAsetId = DB::table('master_data_barang')->where('kode_data_barang', 'BRG-DMY-002')->value('id_data_barang')
                ?? DB::table('master_data_barang')->insertGetId([
                    'id_subjenis_barang' => $subJenisId,
                    'id_satuan' => $satuanId,
                    'kode_data_barang' => 'BRG-DMY-002',
                    'nama_barang' => 'Barang Aset Dummy',
                    'deskripsi' => 'Dummy untuk uji register aset',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            // Stock pusat
            DB::table('data_stock')->updateOrInsert(
                ['id_data_barang' => $barangPersediaanId, 'id_gudang' => $gudangPusatId],
                [
                    'qty_awal' => 100,
                    'qty_masuk' => 20,
                    'qty_keluar' => 10,
                    'qty_akhir' => 110,
                    'id_satuan' => $satuanId,
                    'last_updated' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            // Inventory dummy (created_by pakai user existing jika ada, tanpa membuat user baru)
            $adminId = DB::table('users')->where('email', 'pusdatinppkp@gmail.com')->value('id')
                ?? DB::table('users')->value('id')
                ?? null;

            $inventoryPersediaanId = DB::table('data_inventory')->where('auto_qr_code', 'INV-DMY-001')->value('id_inventory')
                ?? DB::table('data_inventory')->insertGetId([
                    'id_data_barang' => $barangPersediaanId,
                    'id_gudang' => $gudangPusatId,
                    'id_anggaran' => $anggaranId,
                    'id_sub_kegiatan' => $subKegiatanId,
                    'jenis_inventory' => 'PERSEDIAAN',
                    'jenis_barang' => 'PERSEDIAAN',
                    'tahun_anggaran' => (int) date('Y'),
                    'qty_input' => 60,
                    'id_satuan' => $satuanId,
                    'harga_satuan' => 25000,
                    'total_harga' => 1500000,
                    'status_inventory' => 'AKTIF',
                    'auto_qr_code' => 'INV-DMY-001',
                    'created_by' => $adminId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            $inventoryAsetId = DB::table('data_inventory')->where('auto_qr_code', 'INV-DMY-002')->value('id_inventory')
                ?? DB::table('data_inventory')->insertGetId([
                    'id_data_barang' => $barangAsetId,
                    'id_gudang' => $gudangPusatId,
                    'id_anggaran' => $anggaranId,
                    'id_sub_kegiatan' => $subKegiatanId,
                    'jenis_inventory' => 'ASET',
                    'jenis_barang' => 'ASET',
                    'tahun_anggaran' => (int) date('Y'),
                    'qty_input' => 3,
                    'id_satuan' => $satuanId,
                    'harga_satuan' => 1500000,
                    'total_harga' => 4500000,
                    'merk' => 'DummyBrand',
                    'tipe' => 'DummyType',
                    'spesifikasi' => 'Spesifikasi dummy aset',
                    'tahun_produksi' => (int) date('Y'),
                    'no_seri' => 'SERI-DMY-001',
                    'status_inventory' => 'AKTIF',
                    'auto_qr_code' => 'INV-DMY-002',
                    'created_by' => $adminId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            // Inventory item aset
            $itemId = DB::table('inventory_item')->where('kode_register', 'ITM-DMY-001')->value('id_item')
                ?? DB::table('inventory_item')->insertGetId([
                    'id_inventory' => $inventoryAsetId,
                    'kode_register' => 'ITM-DMY-001',
                    'no_seri' => 'SERI-ITEM-DMY-001',
                    'kondisi_item' => 'BAIK',
                    'status_item' => 'AKTIF',
                    'id_gudang' => $gudangPusatId,
                    'id_ruangan' => $ruangAId,
                    'qr_code' => 'QR-ITEM-DMY-001',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            // Register aset + KIR + mutasi
            $registerAsetId = DB::table('register_aset')->where('nomor_register', 'REG-DMY-001')->value('id_register_aset')
                ?? DB::table('register_aset')->insertGetId([
                    'id_inventory' => $inventoryAsetId,
                    'id_item' => $itemId,
                    'id_unit_kerja' => $ukAId,
                    'id_ruangan' => $ruangAId,
                    'nomor_register' => 'REG-DMY-001',
                    'kondisi_aset' => 'BAIK',
                    'tanggal_perolehan' => Carbon::today()->subMonths(2)->toDateString(),
                    'status_aset' => 'AKTIF',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            DB::table('kartu_inventaris_ruangan')->updateOrInsert(
                ['id_register_aset' => $registerAsetId],
                [
                    'id_ruangan' => $ruangAId,
                    'id_penanggung_jawab' => $pegawaiAId,
                    'tanggal_penempatan' => Carbon::today()->subMonth()->toDateString(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            DB::table('mutasi_aset')->updateOrInsert(
                ['id_register_aset' => $registerAsetId, 'id_ruangan_tujuan' => $ruangBId],
                [
                    'id_ruangan_asal' => $ruangAId,
                    'tanggal_mutasi' => Carbon::today()->subDays(10)->toDateString(),
                    'keterangan' => 'Mutasi dummy antar unit',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            // Permintaan + detail
            $permintaanId = DB::table('permintaan_barang')->where('no_permintaan', 'PRM-DMY-001')->value('id_permintaan')
                ?? DB::table('permintaan_barang')->insertGetId([
                    'no_permintaan' => 'PRM-DMY-001',
                    'id_unit_kerja' => $ukAId,
                    'id_pemohon' => $pegawaiAId,
                    'tanggal_permintaan' => Carbon::today()->subDays(20)->toDateString(),
                    'tipe_permintaan' => 'RUTIN',
                    'jenis_permintaan' => json_encode(['PERSEDIAAN']),
                    'keterangan' => 'Permintaan dummy untuk pengujian',
                    'status' => 'diajukan',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            DB::table('detail_permintaan_barang')->updateOrInsert(
                ['id_permintaan' => $permintaanId, 'id_data_barang' => $barangPersediaanId],
                [
                    'deskripsi_barang' => null,
                    'qty_diminta' => 10,
                    'id_satuan' => $satuanId,
                    'keterangan' => 'Detail permintaan dummy',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            // Distribusi + detail
            $distribusiId = DB::table('transaksi_distribusi')->where('no_sbbk', 'SBBK-DMY-001')->value('id_distribusi')
                ?? DB::table('transaksi_distribusi')->insertGetId([
                    'no_sbbk' => 'SBBK-DMY-001',
                    'id_permintaan' => $permintaanId,
                    'tanggal_distribusi' => Carbon::today()->subDays(15)->toDateTimeString(),
                    'id_gudang_asal' => $gudangPusatId,
                    'id_gudang_tujuan' => $gudangUnitAId,
                    'id_pegawai_pengirim' => $pegawaiAId,
                    'status_distribusi' => 'selesai',
                    'keterangan' => 'Distribusi dummy selesai',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            DB::table('detail_distribusi')->updateOrInsert(
                ['id_distribusi' => $distribusiId, 'id_inventory' => $inventoryPersediaanId],
                [
                    'qty_distribusi' => 8,
                    'id_satuan' => $satuanId,
                    'harga_satuan' => 25000,
                    'subtotal' => 200000,
                    'keterangan' => 'Detail distribusi dummy',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            // Penerimaan + detail
            $penerimaanId = DB::table('penerimaan_barang')->where('no_penerimaan', 'TRM-DMY-001')->value('id_penerimaan')
                ?? DB::table('penerimaan_barang')->insertGetId([
                    'no_penerimaan' => 'TRM-DMY-001',
                    'id_distribusi' => $distribusiId,
                    'id_unit_kerja' => $ukAId,
                    'id_pegawai_penerima' => $pegawaiAId,
                    'tanggal_penerimaan' => Carbon::today()->subDays(13)->toDateString(),
                    'status_penerimaan' => 'DITERIMA',
                    'keterangan' => 'Penerimaan dummy',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            DB::table('detail_penerimaan_barang')->updateOrInsert(
                ['id_penerimaan' => $penerimaanId, 'id_inventory' => $inventoryPersediaanId],
                [
                    'qty_diterima' => 8,
                    'id_satuan' => $satuanId,
                    'keterangan' => 'Detail penerimaan dummy',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            // Retur + detail
            $returId = DB::table('retur_barang')->where('no_retur', 'RTR-DMY-001')->value('id_retur')
                ?? DB::table('retur_barang')->insertGetId([
                    'no_retur' => 'RTR-DMY-001',
                    'id_penerimaan' => $penerimaanId,
                    'id_distribusi' => $distribusiId,
                    'id_unit_kerja' => $ukAId,
                    'id_gudang_asal' => $gudangUnitAId,
                    'id_gudang_tujuan' => $gudangPusatId,
                    'id_pegawai_pengirim' => $pegawaiAId,
                    'tanggal_retur' => Carbon::today()->subDays(7)->toDateString(),
                    'status_retur' => 'DIAJUKAN',
                    'keterangan' => 'Retur dummy',
                    'alasan_retur' => 'Barang berlebih',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            DB::table('detail_retur_barang')->updateOrInsert(
                ['id_retur' => $returId, 'id_inventory' => $inventoryPersediaanId],
                [
                    'qty_retur' => 2,
                    'id_satuan' => $satuanId,
                    'alasan_retur_item' => 'Dummy item retur',
                    'keterangan' => 'Detail retur dummy',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            // Pemakaian + detail
            $pemakaianId = DB::table('pemakaian_barang')->where('no_pemakaian', 'PMK-DMY-001')->value('id_pemakaian')
                ?? DB::table('pemakaian_barang')->insertGetId([
                    'no_pemakaian' => 'PMK-DMY-001',
                    'id_unit_kerja' => $ukAId,
                    'id_gudang' => $gudangUnitAId,
                    'id_pegawai_pemakai' => $pegawaiAId,
                    'tanggal_pemakaian' => Carbon::today()->subDays(5)->toDateString(),
                    'status_pemakaian' => 'DRAFT',
                    'keterangan' => 'Pemakaian dummy',
                    'alasan_pemakaian' => 'Uji alur pemakaian',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            DB::table('detail_pemakaian_barang')->updateOrInsert(
                ['id_pemakaian' => $pemakaianId, 'id_inventory' => $inventoryPersediaanId],
                [
                    'qty_pemakaian' => 1,
                    'id_satuan' => $satuanId,
                    'alasan_pemakaian_item' => 'Pemakaian item dummy',
                    'keterangan' => 'Detail pemakaian dummy',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        });

        $this->command->info('Dummy data komprehensif berhasil dibuat.');
        $this->command->info('Data user tidak dibuat oleh seeder ini.');
    }

    private function createPegawaiDummy(string $nip, string $name, string $email, int $unitKerjaId, int $jabatanId): int
    {
        $now = now();

        $pegawaiId = DB::table('master_pegawai')->where('nip_pegawai', $nip)->value('id');
        if ($pegawaiId) {
            DB::table('master_pegawai')
                ->where('id', $pegawaiId)
                ->update([
                    'nama_pegawai' => $name,
                    'id_unit_kerja' => $unitKerjaId,
                    'id_jabatan' => $jabatanId,
                    'email_pegawai' => $email,
                    'updated_at' => $now,
                ]);

            return (int) $pegawaiId;
        }

        return (int) DB::table('master_pegawai')->insertGetId([
            'user_id' => null,
            'nip_pegawai' => $nip,
            'nama_pegawai' => $name,
            'id_unit_kerja' => $unitKerjaId,
            'id_jabatan' => $jabatanId,
            'email_pegawai' => $email,
            'no_telp' => '081200000000',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}

