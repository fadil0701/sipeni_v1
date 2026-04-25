<?php

namespace Database\Seeders;

use App\Models\MasterJabatan;
use App\Models\MasterPegawai;
use App\Models\MasterUnitKerja;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PegawaiUserPerJabatanSeeder extends Seeder
{
    /**
     * Membuat 1 data pegawai + 1 user untuk setiap jabatan
     * kecuali jabatan/role administrator.
     */
    public function run(): void
    {
        $unitKerjaDefault = MasterUnitKerja::orderBy('id_unit_kerja')->first();

        if (! $unitKerjaDefault) {
            $this->command?->warn('MasterUnitKerja belum tersedia. Seeder pegawai/user jabatan dilewati.');
            return;
        }

        $jabatans = MasterJabatan::with('role')
            ->where(function ($query) {
                $query->whereDoesntHave('role', function ($q) {
                    $q->where('name', 'admin');
                })->orWhereNull('role_id');
            })
            ->orderBy('urutan')
            ->orderBy('id_jabatan')
            ->get();

        if ($jabatans->isEmpty()) {
            $this->command?->warn('Tidak ada jabatan non-administrator yang dapat dibuatkan user.');
            return;
        }

        $createdUsers = 0;
        $createdPegawai = 0;

        foreach ($jabatans as $jabatan) {
            $jabatanId = (int) $jabatan->id_jabatan;
            $slug = Str::slug($jabatan->nama_jabatan ?: 'jabatan-' . $jabatanId);
            if ($slug === '') {
                $slug = 'jabatan-' . $jabatanId;
            }

            $email = $slug . '.' . $jabatanId . '@sipeni.local';
            $namaUser = 'User ' . $jabatan->nama_jabatan;
            $nip = 'PEG' . str_pad((string) $jabatanId, 6, '0', STR_PAD_LEFT);

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $namaUser,
                    'password' => Hash::make('Pegawai@123'),
                ]
            );

            if ($user->wasRecentlyCreated) {
                $createdUsers++;
            } else {
                if ($user->name !== $namaUser) {
                    $user->name = $namaUser;
                    $user->save();
                }
            }

            if ($jabatan->role) {
                $user->roles()->syncWithoutDetaching([$jabatan->role->id]);
            }

            $pegawai = MasterPegawai::firstOrCreate(
                ['nip_pegawai' => $nip],
                [
                    'nama_pegawai' => $namaUser,
                    'id_unit_kerja' => $unitKerjaDefault->id_unit_kerja,
                    'id_jabatan' => $jabatanId,
                    'email_pegawai' => $email,
                    'no_telp' => null,
                    'user_id' => $user->id,
                ]
            );

            if ($pegawai->wasRecentlyCreated) {
                $createdPegawai++;
            } else {
                $pegawai->fill([
                    'nama_pegawai' => $namaUser,
                    'id_unit_kerja' => $pegawai->id_unit_kerja ?: $unitKerjaDefault->id_unit_kerja,
                    'id_jabatan' => $jabatanId,
                    'email_pegawai' => $email,
                    'user_id' => $user->id,
                ]);
                if ($pegawai->isDirty()) {
                    $pegawai->save();
                }
            }
        }

        $this->command?->info("Seeder PegawaiUserPerJabatan selesai. User baru: {$createdUsers}, Pegawai baru: {$createdPegawai}");
        $this->command?->info('Password default user pegawai: Pegawai@123');
    }
}

