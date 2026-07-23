<?php

namespace App\Http\Controllers\Maintenance;

use App\Enums\PemeliharaanRekomendasi;
use App\Http\Controllers\Controller;
use App\Models\JadwalMaintenance;
use App\Models\MasterPegawai;
use App\Models\PermintaanPemeliharaan;
use App\Models\RiwayatPemeliharaan;
use App\Models\ServiceReport;
use App\Models\ServiceReportSparepart;
use App\Services\PemeliharaanWorkflowService;
use App\Support\Storage\PrivateStorage;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ServiceReportController extends Controller
{
    public function __construct(
        private readonly PemeliharaanWorkflowService $pemeliharaanWorkflow
    ) {}

    public function index(Request $request)
    {
        $query = ServiceReport::with(['permintaanPemeliharaan', 'registerAset.inventory.dataBarang', 'creator']);

        if ($request->filled('status')) {
            $query->where('status_service', $request->status);
        }

        if ($request->filled('jenis')) {
            $query->where('jenis_service', $request->jenis);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('no_service_report', 'like', "%{$search}%")
                    ->orWhereHas('registerAset', function ($q) use ($search) {
                        $q->where('nomor_register', 'like', "%{$search}%");
                    });
            });
        }

        $perPage = \App\Helpers\PaginationHelper::getPerPage($request, 10);
        $serviceReports = $query->latest('tanggal_service')->paginate($perPage)->appends($request->query());

        return view('maintenance.service-report.index', compact('serviceReports'));
    }

    public function create(Request $request)
    {
        $permintaans = PermintaanPemeliharaan::where('status_permintaan', 'DIPROSES')
            ->whereDoesntHave('serviceReports', function ($q) {
                $q->whereIn('status_service', ['MENUNGGU', 'DIPROSES']);
            })
            ->with([
                'registerAset.inventory.dataBarang',
                'registerAset.inventoryItem',
                'pegawaiPelaksana',
            ])
            ->get();

        $selectedPermintaan = $request->get('permintaan_id')
            ? PermintaanPemeliharaan::with([
                'registerAset.inventory.dataBarang',
                'registerAset.inventoryItem',
                'pegawaiPelaksana',
            ])->find($request->get('permintaan_id'))
            : null;

        $teknisiPegawais = $this->teknisiPegawaiList();

        return view('maintenance.service-report.create', compact('permintaans', 'selectedPermintaan', 'teknisiPegawais'));
    }

    public function store(Request $request)
    {
        $this->validateServiceReportRequest($request, creating: true);
        $this->assertPelaksanaAndSpareparts($request);

        DB::beginTransaction();
        try {
            $permintaan = PermintaanPemeliharaan::findOrFail($request->id_permintaan_pemeliharaan);
            if ((int) $permintaan->id_register_aset <= 0) {
                throw new \RuntimeException('Permintaan tidak memiliki referensi register aset yang valid.');
            }
            if (! $permintaan->canStartServiceReport()) {
                throw new \RuntimeException('Permintaan belum siap untuk Service Report baru (harus status DIPROSES dan tidak ada SR yang masih berjalan).');
            }

            $tahun = date('Y');
            $lastReport = ServiceReport::whereYear('created_at', $tahun)
                ->orderBy('id_service_report', 'desc')
                ->first();

            $urutan = $lastReport ? (int) substr($lastReport->no_service_report, -4) + 1 : 1;
            $noServiceReport = 'SRV/'.$tahun.'/'.str_pad($urutan, 4, '0', STR_PAD_LEFT);

            $filePath = null;
            if ($request->hasFile('file_laporan_kamera')) {
                $filePath = PrivateStorage::storeUploadedFile($request->file('file_laporan_kamera'), 'service-report');
            } elseif ($request->hasFile('file_laporan')) {
                $filePath = PrivateStorage::storeUploadedFile($request->file('file_laporan'), 'service-report');
            }

            [$vendor, $teknisi] = $this->resolvePelaksanaFields($request);
            $catatan = trim((string) $request->input('catatan', ''));
            $totalBiaya = ($request->biaya_service ?? 0) + ($request->biaya_sparepart ?? 0);

            $serviceReport = ServiceReport::create([
                'no_service_report' => $noServiceReport,
                'id_permintaan_pemeliharaan' => $request->id_permintaan_pemeliharaan,
                'id_register_aset' => $permintaan->id_register_aset,
                'tanggal_service' => $request->tanggal_service,
                'tanggal_selesai' => $request->tanggal_selesai,
                'jenis_service' => $request->jenis_service,
                'status_service' => $request->status_service ?? 'MENUNGGU',
                'vendor' => $vendor,
                'teknisi' => $teknisi,
                'deskripsi_kerja' => $request->deskripsi_kerja,
                'tindakan_yang_dilakukan' => $request->tindakan_yang_dilakukan,
                'sparepart_yang_diganti' => null,
                'biaya_service' => $request->biaya_service ?? 0,
                'biaya_sparepart' => $request->biaya_sparepart ?? 0,
                'total_biaya' => $totalBiaya,
                'kondisi_setelah_service' => $request->kondisi_setelah_service,
                'rekomendasi' => $request->rekomendasi,
                'rekomendasi_catatan' => $catatan !== '' ? $catatan : null,
                'file_laporan' => $filePath,
                'keterangan' => $catatan !== '' ? $catatan : null,
                'created_by' => Auth::id(),
            ]);

            $this->syncSpareparts($serviceReport, $request);

            if ($permintaan->status_permintaan !== 'MENUNGGU_DIKETAHUI_SR') {
                $permintaan->update(['status_permintaan' => 'DIPROSES']);
            }

            if (($request->status_service ?? 'MENUNGGU') === 'SELESAI') {
                $this->finalizeServiceWork($serviceReport->fresh(['permintaanPemeliharaan', 'registerAset']), $request);
            }

            DB::commit();

            return redirect()->route('maintenance.service-report.index')
                ->with('success', 'Service report berhasil dibuat.');
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->with('error', 'Gagal membuat service report: '.$e->getMessage());
        }
    }

    public function show($id)
    {
        $serviceReport = ServiceReport::with([
            'permintaanPemeliharaan.registerAset',
            'registerAset.inventory.dataBarang',
            'registerAset.inventoryItem',
            'spareparts',
            'creator',
        ])->findOrFail($id);

        return view('maintenance.service-report.show', compact('serviceReport'));
    }

    public function edit($id)
    {
        $serviceReport = ServiceReport::with([
            'registerAset.inventory.dataBarang',
            'registerAset.inventoryItem',
            'spareparts',
            'permintaanPemeliharaan.pegawaiPelaksana',
        ])->findOrFail($id);

        $teknisiPegawais = $this->teknisiPegawaiList();

        return view('maintenance.service-report.edit', compact('serviceReport', 'teknisiPegawais'));
    }

    public function update(Request $request, $id)
    {
        $this->validateServiceReportRequest($request, creating: false);
        $this->assertPelaksanaAndSpareparts($request);

        $serviceReport = ServiceReport::with('spareparts')->findOrFail($id);

        DB::beginTransaction();
        try {
            $filePath = $serviceReport->file_laporan;
            if ($request->hasFile('file_laporan') || $request->hasFile('file_laporan_kamera')) {
                PrivateStorage::delete($filePath);
                if ($request->hasFile('file_laporan_kamera')) {
                    $filePath = PrivateStorage::storeUploadedFile($request->file('file_laporan_kamera'), 'service-report');
                } else {
                    $filePath = PrivateStorage::storeUploadedFile($request->file('file_laporan'), 'service-report');
                }
            }

            [$vendor, $teknisi] = $this->resolvePelaksanaFields($request);
            $catatan = trim((string) $request->input('catatan', ''));
            $totalBiaya = ($request->biaya_service ?? 0) + ($request->biaya_sparepart ?? 0);

            $serviceReport->update([
                'tanggal_service' => $request->tanggal_service,
                'tanggal_selesai' => $request->tanggal_selesai,
                'jenis_service' => $request->jenis_service,
                'status_service' => $request->status_service,
                'vendor' => $vendor,
                'teknisi' => $teknisi,
                'deskripsi_kerja' => $request->deskripsi_kerja,
                'tindakan_yang_dilakukan' => $request->tindakan_yang_dilakukan,
                'sparepart_yang_diganti' => null,
                'biaya_service' => $request->biaya_service ?? 0,
                'biaya_sparepart' => $request->biaya_sparepart ?? 0,
                'total_biaya' => $totalBiaya,
                'kondisi_setelah_service' => $request->kondisi_setelah_service,
                'rekomendasi' => $request->rekomendasi,
                'rekomendasi_catatan' => $catatan !== '' ? $catatan : null,
                'file_laporan' => $filePath,
                'keterangan' => $catatan !== '' ? $catatan : null,
            ]);

            $this->syncSpareparts($serviceReport, $request);

            if ($request->status_service === 'SELESAI') {
                $this->finalizeServiceWork($serviceReport->fresh(['permintaanPemeliharaan', 'registerAset']), $request);
            }

            DB::commit();

            return redirect()->route('maintenance.service-report.index')
                ->with('success', 'Service report berhasil diperbarui.');
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->with('error', 'Gagal memperbarui service report: '.$e->getMessage());
        }
    }

    public function destroy($id)
    {
        $serviceReport = ServiceReport::with('spareparts')->findOrFail($id);

        PrivateStorage::delete($serviceReport->file_laporan);
        foreach ($serviceReport->spareparts as $sparepart) {
            PrivateStorage::delete($sparepart->foto_path);
        }

        $serviceReport->delete();

        return redirect()->route('maintenance.service-report.index')
            ->with('success', 'Service report berhasil dihapus.');
    }

    private function teknisiPegawaiList()
    {
        return MasterPegawai::query()
            ->teknisiInternal()
            ->with('masterJabatan')
            ->orderBy('nama_pegawai')
            ->get(['id', 'nama_pegawai', 'id_unit_kerja', 'id_jabatan']);
    }

    private function validateServiceReportRequest(Request $request, bool $creating): void
    {
        $rules = [
            'tanggal_service' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_service',
            'jenis_service' => 'required|in:RUTIN,KALIBRASI,PERBAIKAN,PENGGANTIAN_SPAREPART',
            'status_service' => ($creating ? 'nullable' : 'required').'|in:MENUNGGU,DIPROSES,SELESAI,DITOLAK,DIBATALKAN',
            'pelaksana_mode' => 'required|in:INTERNAL,EKSTERNAL',
            'vendor' => 'nullable|string|max:255',
            'teknisi' => 'nullable|string|max:255',
            'deskripsi_kerja' => 'nullable|string',
            'tindakan_yang_dilakukan' => 'nullable|string',
            'biaya_service' => 'nullable|numeric|min:0',
            'biaya_sparepart' => 'nullable|numeric|min:0',
            'kondisi_setelah_service' => 'nullable|in:BAIK,RUSAK_RINGAN,RUSAK_BERAT,TIDAK_BISA_DIPERBAIKI',
            'rekomendasi' => 'nullable|in:'.implode(',', PemeliharaanRekomendasi::values()),
            'catatan' => 'nullable|string',
            'file_laporan' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:4096',
            'file_laporan_kamera' => 'nullable|file|mimes:jpg,jpeg,png|max:4096',
            'spareparts' => 'nullable|array',
            'spareparts.*.id' => 'nullable|integer',
            'spareparts.*.nama_sparepart' => 'nullable|string|max:255',
            'spareparts.*.merk' => 'nullable|string|max:255',
            'spareparts.*.nomor_seri' => 'nullable|string|max:255',
            'spareparts.*.foto' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:4096',
        ];

        if ($creating) {
            $rules['id_permintaan_pemeliharaan'] = 'required|exists:permintaan_pemeliharaan,id_permintaan_pemeliharaan';
        }

        $request->validate($rules);

        if (($request->status_service ?? 'MENUNGGU') === 'SELESAI' && ! $request->filled('rekomendasi')) {
            throw ValidationException::withMessages([
                'rekomendasi' => 'Rekomendasi wajib diisi jika Service Report selesai.',
            ]);
        }
    }

    private function assertPelaksanaAndSpareparts(Request $request): void
    {
        $mode = $request->input('pelaksana_mode');
        if ($mode === 'EKSTERNAL' && ! $request->filled('vendor')) {
            throw ValidationException::withMessages([
                'vendor' => 'Nama vendor wajib diisi untuk pelaksana eksternal.',
            ]);
        }
        if ($mode === 'INTERNAL' && ! $request->filled('teknisi')) {
            throw ValidationException::withMessages([
                'teknisi' => 'Pilih teknisi internal (ATEM / IT Support).',
            ]);
        }

        if ($request->input('rekomendasi') !== PemeliharaanRekomendasi::PendingSparepart->value) {
            return;
        }

        $rows = collect($request->input('spareparts', []))
            ->filter(fn ($row) => filled(trim((string) ($row['nama_sparepart'] ?? ''))));

        if ($rows->isEmpty()) {
            if (($request->status_service ?? 'MENUNGGU') === 'SELESAI' || $request->filled('rekomendasi')) {
                throw ValidationException::withMessages([
                    'spareparts' => 'Isi minimal satu spare part (nama) untuk rekomendasi Pending.',
                ]);
            }
        }
    }

    /**
     * @return array{0: ?string, 1: ?string}
     */
    private function resolvePelaksanaFields(Request $request): array
    {
        if ($request->input('pelaksana_mode') === 'EKSTERNAL') {
            return [
                trim((string) $request->input('vendor')) ?: null,
                trim((string) $request->input('teknisi')) ?: null,
            ];
        }

        return [
            null,
            trim((string) $request->input('teknisi')) ?: null,
        ];
    }

    private function syncSpareparts(ServiceReport $serviceReport, Request $request): void
    {
        $isPending = $request->input('rekomendasi') === PemeliharaanRekomendasi::PendingSparepart->value;
        $existing = $serviceReport->spareparts()->get()->keyBy('id_service_report_sparepart');
        $keptIds = [];

        if (! $isPending) {
            foreach ($existing as $row) {
                PrivateStorage::delete($row->foto_path);
                $row->delete();
            }

            return;
        }

        $rows = $request->input('spareparts', []);

        foreach ($rows as $index => $row) {
            $nama = trim((string) ($row['nama_sparepart'] ?? ''));
            if ($nama === '') {
                continue;
            }

            $id = isset($row['id']) ? (int) $row['id'] : 0;
            $merk = trim((string) ($row['merk'] ?? '')) ?: null;
            $nomorSeri = trim((string) ($row['nomor_seri'] ?? '')) ?: null;
            $fotoFile = $this->resolveSparepartFoto($request, $index);

            if ($id > 0 && $existing->has($id)) {
                $model = $existing->get($id);
                $fotoPath = $model->foto_path;
                if ($fotoFile) {
                    PrivateStorage::delete($fotoPath);
                    $fotoPath = PrivateStorage::storeUploadedFile($fotoFile, 'service-report/sparepart');
                }
                $model->update([
                    'nama_sparepart' => $nama,
                    'merk' => $merk,
                    'nomor_seri' => $nomorSeri,
                    'foto_path' => $fotoPath,
                ]);
                $keptIds[] = $id;
                continue;
            }

            $fotoPath = $fotoFile
                ? PrivateStorage::storeUploadedFile($fotoFile, 'service-report/sparepart')
                : null;

            $created = ServiceReportSparepart::create([
                'id_service_report' => $serviceReport->id_service_report,
                'nama_sparepart' => $nama,
                'merk' => $merk,
                'nomor_seri' => $nomorSeri,
                'foto_path' => $fotoPath,
            ]);
            $keptIds[] = $created->id_service_report_sparepart;
        }

        foreach ($existing as $id => $row) {
            if (! in_array((int) $id, $keptIds, true)) {
                PrivateStorage::delete($row->foto_path);
                $row->delete();
            }
        }

        if ($serviceReport->spareparts()->count() < 1) {
            throw ValidationException::withMessages([
                'spareparts' => 'Isi minimal satu spare part (nama) untuk rekomendasi Pending.',
            ]);
        }
    }

    private function resolveSparepartFoto(Request $request, int|string $index): ?UploadedFile
    {
        $candidates = [
            $request->file("spareparts.{$index}.foto"),
            data_get($request->file('spareparts'), "{$index}.foto"),
            data_get($request->file('spareparts'), ((string) $index).'.foto'),
        ];

        foreach ($candidates as $candidate) {
            if ($candidate instanceof UploadedFile && $candidate->isValid()) {
                return $candidate;
            }
        }

        return null;
    }

    private function finalizeServiceWork(ServiceReport $serviceReport, Request $request): void
    {
        if (! empty($request->kondisi_setelah_service) && $serviceReport->registerAset) {
            $serviceReport->registerAset->update([
                'kondisi_aset' => $request->kondisi_setelah_service,
            ]);
        }

        $catatan = trim((string) $request->input('catatan', $request->keterangan ?? ''));

        RiwayatPemeliharaan::updateOrCreate(
            ['id_service_report' => $serviceReport->id_service_report],
            [
                'id_register_aset' => $serviceReport->id_register_aset,
                'id_permintaan_pemeliharaan' => $serviceReport->id_permintaan_pemeliharaan,
                'id_service_report' => $serviceReport->id_service_report,
                'tanggal_pemeliharaan' => $request->tanggal_selesai ?? $request->tanggal_service,
                'jenis_pemeliharaan' => $request->jenis_service,
                'status' => 'SELESAI',
                'keterangan' => trim($catatan.' | Rekomendasi: '.($request->rekomendasi ?? '-')),
            ]
        );

        $this->rollForwardActiveSchedule(
            (int) $serviceReport->id_register_aset,
            (string) $request->jenis_service,
            $request->tanggal_selesai ?? $request->tanggal_service
        );

        $this->pemeliharaanWorkflow->startServiceReportAcknowledgement($serviceReport->fresh(['permintaanPemeliharaan']));
    }

    private function rollForwardActiveSchedule(int $registerAsetId, string $jenis, string $tanggalAcuan): void
    {
        $jadwal = JadwalMaintenance::query()
            ->where('id_register_aset', $registerAsetId)
            ->where('jenis_maintenance', $jenis)
            ->where('status', 'AKTIF')
            ->orderBy('tanggal_selanjutnya')
            ->first();

        if (! $jadwal) {
            return;
        }

        $nextDate = $this->calculateNextDate($tanggalAcuan, $jadwal->periode, $jadwal->interval_hari);

        $jadwal->update([
            'tanggal_terakhir' => $tanggalAcuan,
            'tanggal_selanjutnya' => $nextDate,
        ]);
    }

    private function calculateNextDate(string $tanggalMulai, string $periode, ?int $intervalHari = null): string
    {
        $date = \Carbon\Carbon::parse($tanggalMulai);

        return match ($periode) {
            'HARIAN' => $date->addDay()->format('Y-m-d'),
            'MINGGUAN' => $date->addWeek()->format('Y-m-d'),
            'BULANAN' => $date->addMonth()->format('Y-m-d'),
            '3_BULAN' => $date->addMonths(3)->format('Y-m-d'),
            '6_BULAN' => $date->addMonths(6)->format('Y-m-d'),
            'TAHUNAN' => $date->addYear()->format('Y-m-d'),
            'CUSTOM' => $date->addDays($intervalHari ?? 30)->format('Y-m-d'),
            default => $date->addMonth()->format('Y-m-d'),
        };
    }
}
