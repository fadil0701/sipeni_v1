<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\DetailPeminjamanBarang;
use App\Models\MasterDataBarang;
use App\Models\MasterGudang;
use App\Models\MasterPegawai;
use App\Models\RegisterAset;
use App\Models\MasterSatuan;
use App\Models\MasterUnitKerja;
use App\Models\PeminjamanBarang;
use App\Models\PeminjamanBarangLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class PeminjamanBarangController extends Controller
{
    public function index(Request $request)
    {
        $query = PeminjamanBarang::with(['unitPeminjam', 'unitPemilik', 'gudangPusat', 'pemohon'])
            ->latest('id_peminjaman');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('tujuan_peminjaman')) {
            $query->where('tujuan_peminjaman', $request->string('tujuan_peminjaman'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->string('search'));
            $query->where(function ($q) use ($search) {
                $q->where('no_peminjaman', 'like', '%' . $search . '%')
                    ->orWhereHas('unitPeminjam', function ($sub) use ($search) {
                        $sub->where('nama_unit_kerja', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('pemohon', function ($sub) use ($search) {
                        $sub->where('nama_pegawai', 'like', '%' . $search . '%');
                    });
            });
        }

        $peminjamanList = $query->paginate(10)->appends($request->query());
        $statusOrder = PeminjamanBarang::orderedStatuses();
        $existingStatuses = PeminjamanBarang::query()
            ->select('status')
            ->distinct()
            ->pluck('status')
            ->filter()
            ->values()
            ->all();
        $statusOptions = array_values(array_filter(
            $statusOrder,
            static fn (string $status): bool => in_array($status, $existingStatuses, true)
        ));

        if ($request->filled('status') && !in_array((string) $request->string('status'), $statusOptions, true)) {
            array_unshift($statusOptions, (string) $request->string('status'));
        }

        $statusLabels = PeminjamanBarang::statusLabels();
        $tujuanLabels = PeminjamanBarang::tujuanLabels();
        $summary = [
            'total' => PeminjamanBarang::query()->count(),
            'menunggu_verifikasi_unit' => PeminjamanBarang::query()
                ->where('status', PeminjamanBarang::STATUS_DIAJUKAN)
                ->count(),
            'menunggu_pengurus' => PeminjamanBarang::query()
                ->whereIn('status', [
                    PeminjamanBarang::STATUS_DIVERIFIKASI_UNIT_A,
                    PeminjamanBarang::STATUS_MENUNGGU_APPROVAL_PENGURUS,
                ])
                ->count(),
            'menunggu_unit_pemilik' => PeminjamanBarang::query()
                ->where('status', PeminjamanBarang::STATUS_MENUNGGU_PERSETUJUAN_UNIT_B)
                ->count(),
            'menunggu_pengembalian' => PeminjamanBarang::query()
                ->where('status', PeminjamanBarang::STATUS_SERAH_TERIMA)
                ->count(),
            'selesai' => PeminjamanBarang::query()
                ->where('status', PeminjamanBarang::STATUS_SELESAI)
                ->count(),
        ];

        return view('transaction.peminjaman-barang.index', compact('peminjamanList', 'statusOptions', 'statusLabels', 'tujuanLabels', 'summary'));
    }

    public function create()
    {
        $user = Auth::user();
        $pegawai = MasterPegawai::query()->where('user_id', Auth::id())->first();
        $isAdminWithoutPegawai = (!$pegawai && $user && $user->hasRole('admin'));

        if (!$pegawai && !$isAdminWithoutPegawai) {
            abort(422, 'Data pegawai yang terhubung dengan user tidak ditemukan. Hubungkan user ke master pegawai atau login dengan akun yang sudah terhubung.');
        }

        $currentUnitId = $pegawai?->id_unit_kerja;

        $unitPemilikList = MasterUnitKerja::query()
            ->when($currentUnitId, fn ($q) => $q->where('id_unit_kerja', '!=', $currentUnitId))
            ->orderBy('nama_unit_kerja')
            ->get();

        $gudangPusatList = MasterGudang::query()
            ->where('jenis_gudang', 'PUSAT')
            ->orderBy('nama_gudang')
            ->get();

        $registeredBarangIds = RegisterAset::query()
            ->whereNotNull('register_aset.id_inventory')
            ->join('data_inventory', 'register_aset.id_inventory', '=', 'data_inventory.id_inventory')
            ->pluck('data_inventory.id_data_barang')
            ->filter()
            ->unique()
            ->values();

        $dataBarangs = MasterDataBarang::query()
            ->with('satuan')
            ->whereIn('id_data_barang', $registeredBarangIds)
            ->orderBy('nama_barang')
            ->limit(1500)
            ->get();

        $satuans = MasterSatuan::query()->orderBy('nama_satuan')->get();
        $unitPeminjamOptions = MasterUnitKerja::query()->orderBy('nama_unit_kerja')->get();
        $pemohonPegawaiOptions = MasterPegawai::query()
            ->with('unitKerja')
            ->orderBy('nama_pegawai')
            ->get();

        return view('transaction.peminjaman-barang.create', compact(
            'pegawai',
            'isAdminWithoutPegawai',
            'unitPemilikList',
            'gudangPusatList',
            'dataBarangs',
            'satuans',
            'unitPeminjamOptions',
            'pemohonPegawaiOptions'
        ));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $pegawai = MasterPegawai::query()->where('user_id', Auth::id())->first();
        $isAdminWithoutPegawai = (!$pegawai && $user && $user->hasRole('admin'));

        if (!$pegawai && !$isAdminWithoutPegawai) {
            abort(422, 'Data pegawai yang terhubung dengan user tidak ditemukan. Hubungkan user ke master pegawai atau login dengan akun yang sudah terhubung.');
        }

        $validated = $request->validate([
            'tujuan_peminjaman' => 'required|in:UNIT,GUDANG_PUSAT',
            'id_unit_pemilik' => 'nullable|exists:master_unit_kerja,id_unit_kerja',
            'id_gudang_pusat' => 'nullable|exists:master_gudang,id_gudang',
            'tanggal_pinjam' => 'required|date',
            'tanggal_rencana_kembali' => 'required|date|after_or_equal:tanggal_pinjam',
            'alasan' => 'required|string|min:10|max:2000',
            'items' => 'required|array|min:1',
            'items.*.id_data_barang' => 'required|exists:master_data_barang,id_data_barang',
            'items.*.id_satuan' => 'required|exists:master_satuan,id_satuan',
            'items.*.qty_pinjam' => 'required|numeric|min:0.01',
            'items.*.keterangan_detail' => 'nullable|string|max:1000',
            'id_pemohon_manual' => 'nullable|exists:master_pegawai,id',
            'id_unit_peminjam' => 'nullable|exists:master_unit_kerja,id_unit_kerja',
        ]);

        if ($isAdminWithoutPegawai) {
            if (empty($validated['id_pemohon_manual'])) {
                return back()->withInput()->withErrors(['id_pemohon_manual' => 'Pemohon pegawai wajib dipilih untuk akun admin tanpa relasi pegawai.']);
            }
            if (empty($validated['id_unit_peminjam'])) {
                return back()->withInput()->withErrors(['id_unit_peminjam' => 'Unit peminjam wajib dipilih untuk akun admin tanpa relasi pegawai.']);
            }
            $pegawai = MasterPegawai::query()->findOrFail($validated['id_pemohon_manual']);
        } else {
            $validated['id_unit_peminjam'] = $pegawai->id_unit_kerja;
        }

        if ($validated['tujuan_peminjaman'] === 'UNIT' && empty($validated['id_unit_pemilik'])) {
            return back()->withInput()->withErrors(['id_unit_pemilik' => 'Unit pemilik wajib dipilih untuk tujuan lintas unit.']);
        }

        if ($validated['tujuan_peminjaman'] === 'GUDANG_PUSAT' && empty($validated['id_gudang_pusat'])) {
            return back()->withInput()->withErrors(['id_gudang_pusat' => 'Gudang pusat wajib dipilih untuk tujuan gudang pusat.']);
        }

        if (!empty($validated['id_unit_pemilik']) && (int) $validated['id_unit_pemilik'] === (int) $pegawai->id_unit_kerja) {
            return back()->withInput()->withErrors(['id_unit_pemilik' => 'Unit pemilik tidak boleh sama dengan unit peminjam.']);
        }

        $itemBarangIds = collect($validated['items'])
            ->pluck('id_data_barang')
            ->map(fn ($id) => (int) $id)
            ->values();
        $duplicateItemIds = $itemBarangIds
            ->duplicates()
            ->unique()
            ->values()
            ->all();
        if (!empty($duplicateItemIds)) {
            return back()->withInput()->withErrors([
                'items' => 'Satu barang hanya boleh muncul sekali dalam satu dokumen peminjaman.',
            ]);
        }

        $activeLoanItemIds = DetailPeminjamanBarang::query()
            ->whereIn('id_data_barang', $itemBarangIds->all())
            ->whereHas('peminjaman', function ($q) {
                $q->whereIn('status', PeminjamanBarang::activeLoanStatuses());
            })
            ->pluck('id_data_barang')
            ->unique()
            ->values()
            ->all();

        if (!empty($activeLoanItemIds)) {
            return back()->withInput()->withErrors([
                'items' => 'Ada barang yang masih dalam proses peminjaman aktif dan belum selesai.',
            ]);
        }

        $created = false;
        for ($attempt = 1; $attempt <= 3; $attempt++) {
            try {
                DB::transaction(function () use ($validated, $pegawai) {
                    $peminjaman = PeminjamanBarang::create([
                        'no_peminjaman' => $this->generateNoPeminjaman(),
                        'id_unit_peminjam' => $validated['id_unit_peminjam'],
                        'id_pemohon' => $pegawai->id,
                        'tujuan_peminjaman' => $validated['tujuan_peminjaman'],
                        'id_unit_pemilik' => $validated['tujuan_peminjaman'] === 'UNIT' ? ($validated['id_unit_pemilik'] ?? null) : null,
                        'id_gudang_pusat' => $validated['tujuan_peminjaman'] === 'GUDANG_PUSAT' ? ($validated['id_gudang_pusat'] ?? null) : null,
                        'tanggal_pinjam' => $validated['tanggal_pinjam'],
                        'tanggal_rencana_kembali' => $validated['tanggal_rencana_kembali'],
                        'status' => PeminjamanBarang::STATUS_DIAJUKAN,
                        'alasan' => $validated['alasan'],
                    ]);

                    foreach ($validated['items'] as $item) {
                        DetailPeminjamanBarang::create([
                            'id_peminjaman' => $peminjaman->id_peminjaman,
                            'id_data_barang' => $item['id_data_barang'],
                            'qty_pinjam' => $item['qty_pinjam'],
                            'id_satuan' => $item['id_satuan'],
                            'keterangan' => $item['keterangan_detail'] ?? null,
                        ]);
                    }

                    $this->logStatusChange(
                        $peminjaman->id_peminjaman,
                        'ajukan_peminjaman',
                        null,
                        PeminjamanBarang::STATUS_DIAJUKAN,
                        'Permintaan peminjaman diajukan oleh unit peminjam.'
                    );
                });

                $created = true;
                break;
            } catch (QueryException $e) {
                if (!$this->isDuplicateNoPeminjamanError($e) || $attempt === 3) {
                    throw $e;
                }
            }
        }

        abort_unless($created, 500, 'Gagal membuat nomor peminjaman unik.');

        return redirect()->route('transaction.peminjaman-barang.index')
            ->with('success', 'Permintaan peminjaman berhasil diajukan.');
    }

    public function show($id)
    {
        $peminjaman = PeminjamanBarang::with([
            'unitPeminjam',
            'unitPemilik',
            'gudangPusat.unitKerja',
            'pemohon.unitKerja',
            'details.dataBarang',
            'details.satuan',
            'logs.user',
        ])->findOrFail($id);

        return view('transaction.peminjaman-barang.show', compact('peminjaman'));
    }

    public function indexPengembalian(Request $request)
    {
        $user = Auth::user();
        $pegawai = $user ? MasterPegawai::query()->where('user_id', $user->id)->first() : null;

        $allowedStatuses = [
            PeminjamanBarang::STATUS_SERAH_TERIMA,
            PeminjamanBarang::STATUS_PENGEMBALIAN,
            PeminjamanBarang::STATUS_SELESAI,
        ];

        $query = PeminjamanBarang::query()
            ->with(['unitPeminjam', 'pemohon'])
            ->whereIn('status', $allowedStatuses)
            ->latest('id_peminjaman');

        if ($request->filled('status') && in_array((string) $request->string('status'), $allowedStatuses, true)) {
            $query->where('status', (string) $request->string('status'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->string('search'));
            $query->where(function ($q) use ($search) {
                $q->where('no_peminjaman', 'like', '%' . $search . '%')
                    ->orWhereHas('unitPeminjam', function ($sub) use ($search) {
                        $sub->where('nama_unit_kerja', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('pemohon', function ($sub) use ($search) {
                        $sub->where('nama_pegawai', 'like', '%' . $search . '%');
                    });
            });
        }

        $list = $query->paginate(10)->appends($request->query());
        $statusLabels = PeminjamanBarang::statusLabels();
        $summary = [
            'siap_dikembalikan' => PeminjamanBarang::query()->where('status', PeminjamanBarang::STATUS_SERAH_TERIMA)->count(),
            'menunggu_finalisasi' => PeminjamanBarang::query()->where('status', PeminjamanBarang::STATUS_PENGEMBALIAN)->count(),
            'selesai' => PeminjamanBarang::query()->where('status', PeminjamanBarang::STATUS_SELESAI)->count(),
        ];

        return view('transaction.pengembalian-barang.index', compact(
            'list',
            'allowedStatuses',
            'statusLabels',
            'summary',
            'user',
            'pegawai'
        ));
    }

    public function createPengembalian($id)
    {
        $this->authorizeRole(['admin', 'pegawai']);
        $peminjaman = $this->findAndGuardStatus($id, PeminjamanBarang::STATUS_SERAH_TERIMA);
        $this->ensureSameUnitForRole($peminjaman->id_unit_peminjam, 'pegawai');

        return view('transaction.peminjaman-barang.pengembalian', compact('peminjaman'));
    }

    public function verifikasiUnitA(Request $request, $id)
    {
        $this->authorizeRole(['admin', 'kepala_unit']);
        $peminjaman = $this->findAndGuardStatus($id, PeminjamanBarang::STATUS_DIAJUKAN);
        $this->ensureSameUnitForRole($peminjaman->id_unit_peminjam, 'kepala_unit');

        $catatan = $request->string('catatan')->toString();
        $this->updateStatus($peminjaman, 'verifikasi_unit_a', PeminjamanBarang::STATUS_DIVERIFIKASI_UNIT_A, $catatan);

        return back()->with('success', 'Permintaan berhasil diverifikasi Unit Kerja peminjam.');
    }

    public function approveUnitB(Request $request, $id)
    {
        $this->authorizeRole(['admin', 'kepala_unit']);
        $peminjaman = $this->findAndGuardStatus($id, PeminjamanBarang::STATUS_MENUNGGU_PERSETUJUAN_UNIT_B);
        $this->ensureSameUnitForRole((int) $peminjaman->id_unit_pemilik, 'kepala_unit');

        $catatan = $request->string('catatan')->toString();
        $this->updateStatus($peminjaman, 'approve_unit_b', PeminjamanBarang::STATUS_DISETUJUI_PENGURUS, $catatan);

        return back()->with('success', 'Permintaan disetujui Unit yang Dipinjam dan diteruskan ke Kasubag TU.');
    }

    public function rejectUnitB(Request $request, $id)
    {
        $this->authorizeRole(['admin', 'kepala_unit']);
        $peminjaman = $this->findAndGuardStatus($id, PeminjamanBarang::STATUS_MENUNGGU_PERSETUJUAN_UNIT_B);
        $this->ensureSameUnitForRole((int) $peminjaman->id_unit_pemilik, 'kepala_unit');

        $validated = $request->validate(['catatan' => 'required|string|min:5']);
        $this->updateStatus($peminjaman, 'reject_unit_b', PeminjamanBarang::STATUS_DITOLAK_UNIT_B, $validated['catatan']);

        return back()->with('success', 'Permintaan ditolak oleh Unit B.');
    }

    public function approvePengurus(Request $request, $id)
    {
        $this->authorizeRole(['admin', 'admin_gudang']);
        $peminjaman = $this->findAndGuardStatuses($id, [
            PeminjamanBarang::STATUS_DIVERIFIKASI_UNIT_A,
            // fallback kompatibilitas data lama
            PeminjamanBarang::STATUS_MENUNGGU_APPROVAL_PENGURUS,
        ]);
        $catatan = $request->string('catatan')->toString();
        $nextStatus = $peminjaman->tujuan_peminjaman === PeminjamanBarang::TUJUAN_ANTAR_UNIT_KERJA
            ? PeminjamanBarang::STATUS_MENUNGGU_PERSETUJUAN_UNIT_B
            : PeminjamanBarang::STATUS_DISETUJUI_PENGURUS;
        $this->updateStatus($peminjaman, 'approve_pengurus_barang', $nextStatus, $catatan);

        return back()->with('success', 'Approval + disposisi pengurus barang berhasil diproses.');
    }

    public function rejectPengurus(Request $request, $id)
    {
        $this->authorizeRole(['admin', 'admin_gudang']);
        $peminjaman = $this->findAndGuardStatuses($id, [
            PeminjamanBarang::STATUS_DIVERIFIKASI_UNIT_A,
            // fallback kompatibilitas data lama
            PeminjamanBarang::STATUS_MENUNGGU_APPROVAL_PENGURUS,
        ]);
        $validated = $request->validate(['catatan' => 'required|string|min:5']);
        $this->updateStatus($peminjaman, 'reject_pengurus_barang', PeminjamanBarang::STATUS_DITOLAK_PENGURUS, $validated['catatan']);

        return back()->with('success', 'Permintaan ditolak Pengurus Barang.');
    }

    public function mengetahuiKasubagTu(Request $request, $id)
    {
        $this->authorizeRole(['admin', 'kasubbag_tu']);
        $peminjaman = $this->findAndGuardStatus($id, PeminjamanBarang::STATUS_DISETUJUI_PENGURUS);
        $catatan = $request->string('catatan')->toString();
        $this->updateStatus($peminjaman, 'mengetahui_kasubag_tu', PeminjamanBarang::STATUS_DIKETAHUI_KASUBAG_TU, $catatan);

        return back()->with('success', 'Peminjaman sudah diketahui Kasubag TU.');
    }

    public function serahTerima(Request $request, $id)
    {
        $this->authorizeRole(['admin', 'admin_gudang', 'admin_gudang_unit']);
        $peminjaman = $this->findAndGuardStatus($id, PeminjamanBarang::STATUS_DIKETAHUI_KASUBAG_TU);
        $validated = $request->validate(['kondisi_serah' => 'required|string|max:100']);

        DB::transaction(function () use ($peminjaman, $validated) {
            foreach ($peminjaman->details as $detail) {
                $detail->update(['kondisi_serah' => $validated['kondisi_serah']]);
            }

            $before = $peminjaman->status;
            $peminjaman->update([
                'status' => PeminjamanBarang::STATUS_SERAH_TERIMA,
                'tanggal_serah_terima' => now(),
            ]);

            $this->logStatusChange(
                $peminjaman->id_peminjaman,
                'serah_terima',
                $before,
                PeminjamanBarang::STATUS_SERAH_TERIMA,
                'Serah terima antar unit dilakukan. Kondisi serah: '.$validated['kondisi_serah']
            );
        });

        return back()->with('success', 'Serah terima berhasil dicatat.');
    }

    public function pengembalian(Request $request, $id)
    {
        $this->authorizeRole(['admin', 'pegawai']);
        $peminjaman = $this->findAndGuardStatus($id, PeminjamanBarang::STATUS_SERAH_TERIMA);
        $this->ensureSameUnitForRole($peminjaman->id_unit_peminjam, 'pegawai');
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id_detail_peminjaman' => 'required|exists:detail_peminjaman_barang,id_detail_peminjaman',
            'items.*.kondisi_kembali' => 'required|string|max:100',
        ]);

        DB::transaction(function () use ($peminjaman, $validated) {
            $detailMap = $peminjaman->details->keyBy('id_detail_peminjaman');
            foreach ($validated['items'] as $item) {
                $detail = $detailMap->get((int) $item['id_detail_peminjaman']);
                abort_if(!$detail, 422, 'Detail peminjaman tidak valid untuk dokumen ini.');
                $detail->update(['kondisi_kembali' => $item['kondisi_kembali']]);
            }
            $before = $peminjaman->status;
            $peminjaman->update([
                'status' => PeminjamanBarang::STATUS_PENGEMBALIAN,
                'tanggal_pengembalian' => now(),
            ]);
            $this->logStatusChange(
                $peminjaman->id_peminjaman,
                'pengembalian',
                $before,
                PeminjamanBarang::STATUS_PENGEMBALIAN,
                'Pengembalian dicatat per item.'
            );
        });

        return back()->with('success', 'Pengembalian berhasil dicatat.');
    }

    public function selesai(Request $request, $id)
    {
        $this->authorizeRole(['admin', 'admin_gudang']);
        $peminjaman = $this->findAndGuardStatus($id, PeminjamanBarang::STATUS_PENGEMBALIAN);
        $catatan = $request->string('catatan')->toString();
        $this->updateStatus($peminjaman, 'selesai', PeminjamanBarang::STATUS_SELESAI, $catatan ?: 'Alur peminjaman selesai.');

        return back()->with('success', 'Peminjaman ditandai selesai.');
    }

    private function updateStatus(PeminjamanBarang $peminjaman, string $aksi, string $nextStatus, ?string $catatan): void
    {
        $before = $peminjaman->status;
        $peminjaman->update(['status' => $nextStatus]);
        $this->logStatusChange($peminjaman->id_peminjaman, $aksi, $before, $nextStatus, $catatan);
    }

    private function findAndGuardStatus(int $id, string $expectedStatus): PeminjamanBarang
    {
        $peminjaman = PeminjamanBarang::with('details')->findOrFail($id);
        if ($peminjaman->status !== $expectedStatus) {
            abort(422, 'Status peminjaman tidak sesuai untuk aksi ini.');
        }

        return $peminjaman;
    }

    private function findAndGuardStatuses(int $id, array $expectedStatuses): PeminjamanBarang
    {
        $peminjaman = PeminjamanBarang::with('details')->findOrFail($id);
        if (!in_array($peminjaman->status, $expectedStatuses, true)) {
            abort(422, 'Status peminjaman tidak sesuai untuk aksi ini.');
        }

        return $peminjaman;
    }

    private function currentPegawaiOrFail(): MasterPegawai
    {
        $pegawai = MasterPegawai::query()->where('user_id', Auth::id())->first();
        abort_if(!$pegawai, 422, 'Data pegawai yang terhubung dengan user tidak ditemukan.');

        return $pegawai;
    }

    private function authorizeRole(array $allowedRoles): void
    {
        $user = Auth::user();
        abort_if(!$user || !$user->hasAnyRole($allowedRoles), 403, 'Anda tidak memiliki akses untuk aksi ini.');
    }

    private function ensureSameUnitForRole(int $expectedUnitId, string ...$roleNames): void
    {
        $user = Auth::user();
        if (!$user || $user->hasRole('admin')) {
            return;
        }

        $hasScopedRole = collect($roleNames)->contains(fn ($role) => $user->hasRole($role));
        if (!$hasScopedRole) {
            return;
        }

        $pegawai = $this->currentPegawaiOrFail();
        abort_if((int) $pegawai->id_unit_kerja !== (int) $expectedUnitId, 403, 'Aksi ini hanya bisa dilakukan oleh unit terkait.');
    }

    private function generateNoPeminjaman(): string
    {
        $prefix = 'PMJ-'.now()->format('Ymd').'-';
        // Lock baris terakhir pada prefix harian agar aman dari request paralel.
        $last = PeminjamanBarang::query()
            ->where('no_peminjaman', 'like', $prefix.'%')
            ->orderByDesc('id_peminjaman')
            ->lockForUpdate()
            ->value('no_peminjaman');

        $next = 1;
        if ($last && preg_match('/(\d{4})$/', $last, $m)) {
            $next = ((int) $m[1]) + 1;
        }

        do {
            $candidate = $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
            $exists = PeminjamanBarang::query()
                ->where('no_peminjaman', $candidate)
                ->exists();
            $next++;
        } while ($exists);

        return $candidate;
    }

    private function logStatusChange(int $idPeminjaman, string $aksi, ?string $fromStatus, ?string $toStatus, ?string $catatan): void
    {
        PeminjamanBarangLog::create([
            'id_peminjaman' => $idPeminjaman,
            'user_id' => Auth::id(),
            'aksi' => $aksi,
            'status_sebelum' => $fromStatus,
            'status_sesudah' => $toStatus,
            'catatan' => $catatan,
        ]);
    }

    private function isDuplicateNoPeminjamanError(QueryException $e): bool
    {
        $message = strtolower($e->getMessage());
        return str_contains($message, 'duplicate') && str_contains($message, 'no_peminjaman');
    }
}

