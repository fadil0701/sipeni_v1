<?php

namespace App\Http\Controllers\Transaction;

use App\Enums\DistribusiStatus;
use App\Enums\PermintaanBarangStatus;
use App\Helpers\PaginationHelper;
use App\Http\Controllers\Controller;
use App\Models\ApprovalLog;
use App\Models\DataInventory;
use App\Models\DetailDistribusi;
use App\Models\MasterGudang;
use App\Models\MasterPegawai;
use App\Models\MasterSatuan;
use App\Models\PermintaanBarang;
use App\Models\PrintTemplate;
use App\Models\TransaksiDistribusi;
use App\Models\User;
use App\Services\DistribusiService;
use App\Support\PermintaanBarangStock;
use App\Services\PrintTemplateRenderer;
use App\Services\SbbkPrintTemplateData;
use App\Support\Http\SafeUserMessage;
use App\Support\Rbac\UserScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class DistribusiController extends Controller
{
    public function __construct(
        private readonly DistribusiService $distribusiService
    ) {}

    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $query = TransaksiDistribusi::with(['gudangAsal', 'gudangTujuan', 'permintaan', 'pegawaiPengirim']);

        if ($user->hasRole('admin_gudang_aset')) {
            $query->whereIn('id_gudang_asal', MasterGudang::where('kategori_gudang', 'ASET')->pluck('id_gudang'));
        } elseif ($user->hasRole('admin_gudang_persediaan')) {
            $query->whereIn('id_gudang_asal', MasterGudang::where('kategori_gudang', 'PERSEDIAAN')->pluck('id_gudang'));
        } elseif ($user->hasRole('admin_gudang_farmasi')) {
            $query->whereIn('id_gudang_asal', MasterGudang::where('kategori_gudang', 'FARMASI')->pluck('id_gudang'));
        }

        if ($request->filled('gudang')) {
            $query->where(fn ($q) => $q->where('id_gudang_asal', $request->gudang)->orWhere('id_gudang_tujuan', $request->gudang));
        }
        if ($request->filled('status')) {
            $query->where('status_distribusi', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('no_sbbk', 'like', "%{$search}%")
                    ->orWhereHas('permintaan', fn ($qq) => $qq->where('no_permintaan', 'like', "%{$search}%"));
            });
        }

        $perPage = PaginationHelper::getPerPage($request, 10);
        $distribusis = $query->latest('tanggal_distribusi')->paginate($perPage)->appends($request->query());
        $gudangs = MasterGudang::all();

        return view('transaction.distribusi.index', compact('distribusis', 'gudangs'));
    }

    public function create(Request $request)
    {
        // Jika dibuka dari "Proses Disposisi", resolve permintaan dari approval_log.
        if ($request->filled('approval_log') && ! $request->filled('permintaan_id')) {
            $approvalLog = ApprovalLog::query()
                ->where('modul_approval', 'PERMINTAAN_BARANG')
                ->find($request->approval_log);

            if ($approvalLog) {
                $request->merge(['permintaan_id' => $approvalLog->id_referensi]);
            }
        }

        $permintaans = PermintaanBarang::whereIn('status', $this->permintaanStatusesEligibleForDistribusi())
            ->with(['unitKerja', 'pemohon', 'detailPermintaan.dataBarang'])->get();

        $gudangs = MasterGudang::all();
        $pegawais = MasterPegawai::all();
        $satuans = MasterSatuan::all();
        $selectedPermintaan = $request->filled('permintaan_id')
            ? PermintaanBarang::with(['detailPermintaan.dataBarang', 'detailPermintaan.satuan'])->find($request->permintaan_id)
            : null;
        $flowMode = $request->filled('approval_log') ? 'proses' : 'distribusi';
        $approvalLogId = $request->input('approval_log');

        return view('transaction.distribusi.create', compact('permintaans', 'gudangs', 'pegawais', 'satuans', 'selectedPermintaan', 'flowMode', 'approvalLogId'));
    }

    public function store(Request $request)
    {
        $isProsesMode = $request->input('flow_mode') === 'proses';

        // Mode proses harus menghasilkan SBBK terpisah per gudang pusat (kategori disposisi).
        // Jadi gudang asal/tujuan diset paksa dari approval log + unit permintaan.
        if ($isProsesMode && $request->filled('approval_log_id')) {
            $approvalLog = ApprovalLog::with('approvalFlow.role')->find($request->approval_log_id);

            if ($approvalLog && $request->filled('id_permintaan')) {
                $roleKategoriMap = [
                    'admin_gudang_aset' => 'ASET',
                    'admin_gudang_persediaan' => 'PERSEDIAAN',
                    'admin_gudang_farmasi' => 'FARMASI',
                ];

                $roleName = $approvalLog->approvalFlow?->role?->name;
                $kategori = $roleKategoriMap[$roleName] ?? null;

                if ($kategori) {
                    $gudangAsal = MasterGudang::query()
                        ->where('jenis_gudang', 'PUSAT')
                        ->where('kategori_gudang', $kategori)
                        ->orderBy('id_gudang')
                        ->first();

                    if ($gudangAsal) {
                        $request->merge(['id_gudang_asal' => $gudangAsal->id_gudang]);
                    }
                }

                $permintaan = PermintaanBarang::query()->find($request->id_permintaan);
                if ($permintaan) {
                    $gudangTujuan = MasterGudang::query()
                        ->where('id_unit_kerja', $permintaan->id_unit_kerja)
                        ->where('jenis_gudang', 'UNIT')
                        ->orderBy('id_gudang')
                        ->first();

                    if ($gudangTujuan) {
                        $request->merge(['id_gudang_tujuan' => $gudangTujuan->id_gudang]);
                    }
                }
            }
        }

        // Mode distribusi langsung (tanpa permintaan) tidak wajib id_permintaan.
        // Mode proses dari approval wajib mengaitkan permintaan yang sudah disetujui.
        $validated = $this->validatePayload($request, $isProsesMode, ! $isProsesMode);
        $this->distribusiService->createDraft($validated);

        if ($isProsesMode && $request->filled('approval_log_id')) {
            ApprovalLog::query()
                ->where('id', $request->approval_log_id)
                ->where('modul_approval', 'PERMINTAAN_BARANG')
                ->update([
                    'status' => 'DIPROSES',
                    'user_id' => Auth::id(),
                    'approved_at' => now(),
                ]);
        }

        return redirect()->route('transaction.distribusi.index')->with('success', 'Distribusi draft berhasil dibuat.');
    }

    public function show(int|string $id)
    {
        $distribusi = TransaksiDistribusi::with([
            'permintaan.unitKerja', 'permintaan.pemohon', 'gudangAsal', 'gudangTujuan.unitKerja',
            'pegawaiPengirim', 'detailDistribusi.inventory.dataBarang', 'detailDistribusi.inventory.gudang', 'detailDistribusi.satuan',
            'penerimaanBarang',
        ])->findOrFail($id);

        UserScope::assertCanAccessDistribusi(Auth::user(), $distribusi);

        $penerimaanAktif = $distribusi->penerimaanBarang()->latest('id_penerimaan')->first();

        // Pegawai penerima: dari unit kerja klinik/pemohon, fallback unit gudang tujuan.
        $idUnitTujuan = $distribusi->permintaan?->id_unit_kerja
            ?? $distribusi->gudangTujuan?->id_unit_kerja;
        $namaUnitTujuan = $distribusi->permintaan?->unitKerja?->nama_unit_kerja
            ?? $distribusi->gudangTujuan?->unitKerja?->nama_unit_kerja;
        $pegawaiPenerimaOptions = $idUnitTujuan
            ? MasterPegawai::query()
                ->where('id_unit_kerja', $idUnitTujuan)
                ->orderBy('nama_pegawai')
                ->get()
            : collect();

        return view('transaction.distribusi.show', compact(
            'distribusi',
            'penerimaanAktif',
            'pegawaiPenerimaOptions',
            'namaUnitTujuan'
        ));
    }

    /**
     * Cetak SBBK sebagai HTML dari template cetak aktif (key: distribusi.sbbk).
     */
    public function printSbbk(int|string $id): Response|RedirectResponse
    {
        $distribusi = TransaksiDistribusi::query()->findOrFail($id);

        $template = PrintTemplate::query()
            ->where('key', 'distribusi.sbbk')
            ->where('is_active', true)
            ->first();

        if (! $template) {
            return redirect()
                ->route('transaction.distribusi.show', $id)
                ->with('info', 'Template cetak SBBK belum tersedia atau nonaktif. Buat template dengan key distribusi.sbbk di Admin → Template Cetak, atau jalankan --class=SbbkPrintTemplateSeeder.');
        }

        $html = PrintTemplateRenderer::render($template, SbbkPrintTemplateData::payload($distribusi));

        return response()
            ->view('admin.print-templates.preview-frame', [
                'title' => 'SBBK '.$distribusi->no_sbbk,
                'html' => $html,
                'printTemplate' => $template,
                'allowPdfExport' => false,
            ])
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }

    public function edit(Request $request, int|string $id)
    {
        $distribusi = TransaksiDistribusi::with([
            'detailDistribusi',
            'permintaan.unitKerja',
            'permintaan.detailPermintaan.dataBarang',
            'permintaan.detailPermintaan.satuan',
        ])->findOrFail($id);
        if (! in_array($distribusi->status_distribusi, [DistribusiStatus::Draft, DistribusiStatus::Diproses], true)) {
            return redirect()->route('transaction.distribusi.show', $id)->with('error', 'Hanya distribusi berstatus draft atau diproses yang bisa diedit.');
        }

        $permintaans = PermintaanBarang::whereIn('status', $this->permintaanStatusesEligibleForDistribusi())->get();
        $gudangs = MasterGudang::all();
        $pegawais = MasterPegawai::all();
        $satuans = MasterSatuan::all();
        $inventories = DataInventory::where('id_gudang', $distribusi->id_gudang_asal)->with('dataBarang')->get();
        $selectedPermintaan = $distribusi->permintaan;
        $intentProses = $request->query('intent') === 'proses';

        return view('transaction.distribusi.edit', compact(
            'distribusi',
            'permintaans',
            'gudangs',
            'pegawais',
            'satuans',
            'inventories',
            'selectedPermintaan',
            'intentProses'
        ));
    }

    public function update(Request $request, int|string $id)
    {
        $distribusi = TransaksiDistribusi::findOrFail($id);
        if (! in_array($distribusi->status_distribusi, [DistribusiStatus::Draft, DistribusiStatus::Diproses], true)) {
            return redirect()->route('transaction.distribusi.show', $id)->with('error', 'Hanya distribusi berstatus draft atau diproses yang bisa diubah.');
        }

        $intentProses = $request->input('intent') === 'proses';
        // Saat proses dari daftar, pegawai pengirim wajib diisi.
        $validated = $this->validatePayload($request, false, true);
        $this->distribusiService->updateDraft($distribusi, $validated);

        if ($intentProses && $distribusi->fresh()->status_distribusi === DistribusiStatus::Draft) {
            $this->distribusiService->markDiproses($distribusi->fresh());

            return redirect()->route('transaction.distribusi.show', $id)
                ->with('success', 'Pegawai pengirim disimpan dan distribusi berhasil diproses. Selanjutnya Anda dapat mengirim SBBK.');
        }

        return redirect()->route('transaction.distribusi.show', $id)->with('success', 'Distribusi berhasil diperbarui.');
    }

    public function destroy(int|string $id)
    {
        $distribusi = TransaksiDistribusi::findOrFail($id);
        if ($distribusi->status_distribusi !== DistribusiStatus::Draft) {
            return redirect()->route('transaction.distribusi.index')->with('error', 'Hanya distribusi draft yang bisa dihapus.');
        }

        $this->distribusiService->deleteDraft($distribusi);

        return redirect()->route('transaction.distribusi.index')->with('success', 'Distribusi dihapus.');
    }

    public function proses(int|string $id)
    {
        $distribusi = TransaksiDistribusi::findOrFail($id);
        if ($distribusi->status_distribusi !== DistribusiStatus::Draft) {
            return redirect()->route('transaction.distribusi.show', $id)->with('error', 'Status tidak valid untuk diproses.');
        }

        // Arahkan ke form edit agar pegawai pengirim diisi sebelum status menjadi diproses.
        return redirect()->route('transaction.distribusi.edit', ['id' => $id, 'intent' => 'proses']);
    }

    public function kirim(Request $request, int|string $id)
    {
        $distribusi = TransaksiDistribusi::with('detailDistribusi')->findOrFail($id);
        $fromIndex = $request->input('kirim_from') === 'index';

        if (! in_array($distribusi->status_distribusi?->value, [DistribusiStatus::Draft->value, DistribusiStatus::Diproses->value], true)) {
            return $fromIndex
                ? redirect()->route('transaction.distribusi.index')->with('error', 'Status tidak valid untuk dikirim.')
                : redirect()->route('transaction.distribusi.show', $id)->with('error', 'Status tidak valid untuk dikirim.');
        }
        if (! $distribusi->id_pegawai_pengirim) {
            return redirect()->route('transaction.distribusi.edit', $id)
                ->with('error', 'Pilih pegawai pengirim terlebih dahulu sebelum mengirim distribusi.');
        }

        $this->distribusiService->kirim($distribusi);

        if ($fromIndex) {
            return redirect()->route('transaction.distribusi.index')->with('kirim_popup', 'Distribusi berhasil dikirim.');
        }

        return redirect()->route('transaction.distribusi.show', $id)->with('success', 'Distribusi dikirim.');
    }

    /**
     * Pengirim melaporkan barang sudah sampai (foto + nama penerima di lokasi).
     */
    public function buktiSampai(Request $request, int|string $id)
    {
        $distribusi = TransaksiDistribusi::with('penerimaanBarang')->findOrFail($id);
        UserScope::assertCanAccessDistribusi(Auth::user(), $distribusi);

        if ($distribusi->status_distribusi?->value !== DistribusiStatus::Dikirim->value) {
            return redirect()->route('transaction.distribusi.show', $id)
                ->with('error', 'Bukti sampai hanya dapat diisi setelah distribusi dikirim.');
        }

        $distribusi->loadMissing(['permintaan', 'gudangTujuan']);
        $idUnitTujuan = $distribusi->permintaan?->id_unit_kerja
            ?? $distribusi->gudangTujuan?->id_unit_kerja;

        $validated = $request->validate([
            'id_pegawai_penerima' => [
                'required',
                'exists:master_pegawai,id',
                function (string $attribute, mixed $value, \Closure $fail) use ($idUnitTujuan): void {
                    if (! $idUnitTujuan) {
                        $fail('Unit kerja tujuan tidak ditemukan untuk validasi penerima.');

                        return;
                    }
                    $belongs = MasterPegawai::query()
                        ->where('id', $value)
                        ->where('id_unit_kerja', $idUnitTujuan)
                        ->exists();
                    if (! $belongs) {
                        $fail('Pegawai penerima harus berasal dari unit kerja klinik tujuan.');
                    }
                },
            ],
            // Nama field generik — menghindari signature WAF pada gps_*/latitude/longitude
            'sumber' => 'required|in:upload,kamera',
            'foto' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
            'loc_a' => 'nullable|numeric|between:-90,90',
            'loc_b' => 'nullable|numeric|between:-180,180',
            'loc_c' => 'nullable|numeric|min:0|max:99999',
            'loc_d' => 'nullable|string|max:500',
            'catatan_pengirim' => 'nullable|string|max:2000',
        ], [
            'id_pegawai_penerima.required' => 'Pilih pegawai penerima di lokasi.',
            'id_pegawai_penerima.exists' => 'Pegawai penerima tidak valid.',
            'sumber.required' => 'Pilih cara pengambilan bukti (unggah atau kamera).',
            'foto.required' => 'Foto bukti pengiriman sampai wajib diisi.',
            'foto.image' => 'File harus berupa gambar.',
            'foto.max' => 'Ukuran foto maksimal 5 MB.',
        ]);

        $pegawaiPenerima = MasterPegawai::query()->findOrFail($validated['id_pegawai_penerima']);
        $namaPenerima = $pegawaiPenerima->nama_pegawai
            ?: ($pegawaiPenerima->nama ?? 'Pegawai #'.$pegawaiPenerima->id);

        try {
            $fotoPath = \App\Support\Storage\PrivateStorage::storeUploadedFile(
                $request->file('foto'),
                'laporan-kedatangan'
            );

            $gpsLat = isset($validated['loc_a']) ? (float) $validated['loc_a'] : null;
            $gpsLng = isset($validated['loc_b']) ? (float) $validated['loc_b'] : null;
            $gpsAlamat = isset($validated['loc_d']) ? trim((string) $validated['loc_d']) : null;
            if (($gpsAlamat === null || $gpsAlamat === '') && $gpsLat !== null && $gpsLng !== null) {
                $gpsAlamat = app(\App\Services\GeocodeService::class)->reverse($gpsLat, $gpsLng);
            }

            $this->distribusiService->laporkanBuktiSampai($distribusi, [
                'nama_penerima_lokasi' => $namaPenerima,
                'foto_bukti_sampai' => $fotoPath,
                'sumber_bukti_sampai' => $validated['sumber'],
                'gps_latitude' => $gpsLat,
                'gps_longitude' => $gpsLng,
                'gps_akurasi' => isset($validated['loc_c']) ? (float) $validated['loc_c'] : null,
                'gps_alamat' => $gpsAlamat !== '' ? $gpsAlamat : null,
                'catatan_pengirim' => $validated['catatan_pengirim'] ?? null,
                'dilapor_oleh' => Auth::id(),
            ]);
        } catch (\Throwable $e) {
            if (! empty($fotoPath ?? null)) {
                \App\Support\Storage\PrivateStorage::delete($fotoPath);
            }

            return redirect()->route('transaction.distribusi.show', $id)
                ->with('error', SafeUserMessage::fromThrowable($e, 'menyimpan bukti sampai'));
        }

        return redirect()->route('transaction.distribusi.show', $id)
            ->with('success', 'Bukti sampai berhasil disimpan. Status distribusi menjadi selesai. Klinik dapat melakukan verifikasi penerimaan.');
    }

    public function getGudangTujuanByPermintaan(int|string $permintaanId)
    {
        $permintaan = PermintaanBarang::with('unitKerja')->findOrFail($permintaanId);
        $gudangTujuan = MasterGudang::where('id_unit_kerja', $permintaan->id_unit_kerja)
            ->where('jenis_gudang', 'UNIT')
            ->get();

        $roleKategoriMap = [
            'admin_gudang_aset' => 'ASET',
            'admin_gudang_persediaan' => 'PERSEDIAAN',
            'admin_gudang_farmasi' => 'FARMASI',
        ];

        $selectedKategori = null;
        $approvalLogId = request()->query('approval_log');
        if ($approvalLogId) {
            $approvalLog = ApprovalLog::with('approvalFlow.role')->find($approvalLogId);
            $roleName = $approvalLog?->approvalFlow?->role?->name;
            $selectedKategori = $roleKategoriMap[$roleName] ?? null;
        }

        if (! $selectedKategori) {
            $jenisPermintaan = is_array($permintaan->jenis_permintaan)
                ? $permintaan->jenis_permintaan
                : (json_decode($permintaan->jenis_permintaan, true) ?? []);

            foreach (['ASET', 'PERSEDIAAN', 'FARMASI'] as $kategori) {
                if (in_array($kategori, $jenisPermintaan, true)) {
                    $selectedKategori = $kategori;
                    break;
                }
            }
        }

        $gudangAsalQuery = MasterGudang::query()->where('jenis_gudang', 'PUSAT');
        if ($selectedKategori) {
            $gudangAsalQuery->where('kategori_gudang', $selectedKategori);
        }
        $gudangAsal = $gudangAsalQuery->orderBy('nama_gudang')->get();

        return response()->json([
            'success' => true,
            'unit_kerja' => [
                'id_unit_kerja' => $permintaan->id_unit_kerja,
                'nama_unit_kerja' => $permintaan->unitKerja->nama_unit_kerja ?? null,
            ],
            'kategori_permintaan' => $selectedKategori,
            'gudang' => $gudangTujuan->map(fn ($gudang) => [
                'id_gudang' => $gudang->id_gudang,
                'nama_gudang' => $gudang->nama_gudang,
                'jenis_gudang' => $gudang->jenis_gudang,
                'kategori_gudang' => $gudang->kategori_gudang,
            ]),
            'gudang_asal' => $gudangAsal->map(fn ($gudang) => [
                'id_gudang' => $gudang->id_gudang,
                'nama_gudang' => $gudang->nama_gudang,
                'jenis_gudang' => $gudang->jenis_gudang,
                'kategori_gudang' => $gudang->kategori_gudang,
            ]),
        ]);
    }

    public function getInventoryByGudang(int|string $gudangId)
    {
        $gudang = MasterGudang::findOrFail($gudangId);
        UserScope::assertCanAccessGudang(Auth::user(), $gudang);
        $includeIds = collect((array) request()->input('include_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->values()
            ->all();

        $query = DataInventory::query()
            ->where('id_gudang', $gudangId)
            ->where('status_inventory', 'AKTIF');

        // Pastikan inventory yang ditampilkan konsisten dengan kategori gudang asal.
        $allowedKategori = ['ASET', 'PERSEDIAAN', 'FARMASI'];
        if (in_array($gudang->kategori_gudang, $allowedKategori, true)) {
            $query->where('jenis_inventory', $gudang->kategori_gudang);
        }

        $inventories = $query->with(['dataBarang', 'satuan'])->get();

        $result = $inventories->map(function ($inv) {
            $qtyDistributed = DetailDistribusi::where('id_inventory', $inv->id_inventory)
                ->whereHas('distribusi', fn ($q) => $q->whereIn('status_distribusi', ['draft', 'diproses', 'dikirim', 'selesai']))
                ->sum('qty_distribusi');

            $resolvedSatuanId = $inv->id_satuan ?? $inv->dataBarang?->id_satuan;

            return [
                'id_inventory' => $inv->id_inventory,
                'nama_barang' => $inv->dataBarang->nama_barang ?? '-',
                'kode_barang' => $inv->dataBarang->kode_data_barang ?? '-',
                'jenis_inventory' => $inv->jenis_inventory,
                'jenis_barang' => $inv->jenis_barang,
                'harga_satuan' => $inv->harga_satuan,
                'id_satuan' => $resolvedSatuanId,
                'qty_available' => max(0, $inv->qty_input - $qtyDistributed),
            ];
        })->filter(fn ($inv) => $inv['qty_available'] > 0 || in_array((int) $inv['id_inventory'], $includeIds, true));

        return response()->json(['inventory' => $result->values()]);
    }

    public function getPermintaanDetail(int|string $id)
    {
        $permintaan = PermintaanBarang::with(['detailPermintaan.dataBarang', 'detailPermintaan.satuan'])->findOrFail($id);
        UserScope::assertCanAccessPermintaan(Auth::user(), $permintaan);

        $stockData = PermintaanBarangStock::stockDataForDetails($permintaan);
        $eligibleDetails = $permintaan->detailPermintaan->filter(
            fn ($detail) => PermintaanBarangStock::detailReadyForDistribusi($detail, $stockData)
        );

        $details = $eligibleDetails->map(fn ($detail) => [
            'nama_barang' => $detail->dataBarang->nama_barang ?? '-',
            'qty_diminta' => number_format((float) ($detail->qty_diminta_awal ?? $detail->qty_diminta), 2),
            'qty_disetujui' => number_format((float) ($detail->qty_disetujui ?? $detail->qty_diminta), 2),
            'satuan' => $detail->satuan->nama_satuan ?? '-',
        ])->values();

        return response()->json(['success' => true, 'details' => $details]);
    }

    private function validatePayload(Request $request, bool $requirePermintaan = true, bool $requirePegawaiPengirim = true): array
    {
        $rules = [
            'tanggal_distribusi' => 'required|date',
            'id_gudang_asal' => 'required|exists:master_gudang,id_gudang',
            'id_gudang_tujuan' => 'required|exists:master_gudang,id_gudang|different:id_gudang_asal',
            'id_pegawai_pengirim' => $requirePegawaiPengirim ? 'required|exists:master_pegawai,id' : 'nullable|exists:master_pegawai,id',
            'keterangan' => 'nullable|string',
            'detail' => 'required|array|min:1',
            'detail.*.id_inventory' => 'required|exists:data_inventory,id_inventory',
            'detail.*.qty_distribusi' => 'required|numeric|min:0.01',
            'detail.*.id_satuan' => 'required|exists:master_satuan,id_satuan',
            'detail.*.harga_satuan' => 'required|numeric|min:0',
            'detail.*.keterangan' => 'nullable|string',
        ];

        if ($requirePermintaan) {
            $rules['id_permintaan'] = 'required|exists:permintaan_barang,id_permintaan';
        } else {
            $rules['id_permintaan'] = 'nullable|exists:permintaan_barang,id_permintaan';
        }

        return $request->validate($rules);
    }

    /**
     * @return list<string>
     */
    private function permintaanStatusesEligibleForDistribusi(): array
    {
        return [
            PermintaanBarangStatus::Diverifikasi->value,
            PermintaanBarangStatus::BarangTersedia->value,
            PermintaanBarangStatus::ProsesDistribusi->value,
            PermintaanBarangStatus::Dikirim->value,
        ];
    }
}
