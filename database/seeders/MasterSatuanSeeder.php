<?php

namespace Database\Seeders;

use App\Models\MasterSatuan;
use Illuminate\Database\Seeder;

/**
 * Satuan referensi untuk inventori rumah sakit / pemerintah:
 * hitungan, kemasan, farmasi, berat, volume, panjang.
 */
class MasterSatuanSeeder extends Seeder
{
    public function run(): void
    {
        $namaSatuan = [
            // Umum / hitungan
            'Unit',
            'PCS',
            'Buah',
            'Pasang',
            'Set',
            'Lusin',
            'Kodi',
            'Gross',
            'Rim',
            'Lembar',
            'Roll',
            'Ikat',
            'Batang',
            'Potong',
            'Ekor',
            'Biji',
            'Helai',

            // Kemasan / logistik
            'Box',
            'Dus',
            'Karton',
            'Pak',
            'Bundle',
            'Pallet',
            'Karung',
            'Kaleng',
            'Tabung',
            'Jerigen',
            'Drum',
            'Bag',

            // Farmasi & sediaan (nama sebagai label satuan stok)
            'Strip',
            'Blister',
            'Sachet',
            'Pouch',
            'Tube',
            'Botol',
            'Ampul',
            'Vial',
            'Flakon',
            'Prefilled Syringe',
            'Tablet',
            'Kaplet',
            'Kapsul',
            'Suppositoria',
            'Ovula',
            'Sirup',
            'Eliksir',
            'Tetes',
            'Drop',
            'Puff',
            'Sachet (bubuk)',
            'Vial (serbuk)',

            // Berat
            'kg',
            'g',
            'mg',
            'mcg',
            'Mikrogram',
            'Ton',

            // Volume
            'L',
            'mL',
            'cc',

            // Panjang / luas
            'm',
            'cm',
            'mm',
            'km',
            'm²',

            // Medis / lain
            'Dosis',
            'IU',
            'Test',
            'Kit',
            'Slide',
            'Panel',
            'Rack',
            'Tray',

            // ATK / umum kantor
            'Pak isi',
            'Book',
            'Pad',
        ];

        foreach ($namaSatuan as $nama) {
            $nama = trim($nama);
            if ($nama === '') {
                continue;
            }
            if (strlen($nama) > 50) {
                $nama = substr($nama, 0, 50);
            }
            MasterSatuan::firstOrCreate(
                ['nama_satuan' => $nama],
                []
            );
        }
    }
}
