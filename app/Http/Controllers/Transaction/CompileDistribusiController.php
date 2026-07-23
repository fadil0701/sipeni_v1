<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Legacy compatibility: tahap "Compile SBBK" sudah digabung ke Distribusi Barang.
 * Semua aksi diarahkan ke menu/form Distribusi tanpa mengubah alur bisnis.
 */
class CompileDistribusiController extends Controller
{
    public function index(Request $request)
    {
        return redirect()
            ->route('transaction.distribusi.index', $request->query())
            ->with('info', 'Menu SBBK telah disatukan ke menu Distribusi Barang. Buat SBBK dari Daftar Permintaan → Proses, atau menu Distribusi (SBBK).');
    }

    public function create($permintaanId)
    {
        return redirect()->route('transaction.distribusi.create', ['permintaan_id' => $permintaanId]);
    }

    public function store(Request $request)
    {
        return redirect()->route('transaction.distribusi.index')
            ->with('error', 'Gunakan modul Distribusi untuk membuat SBBK.');
    }
}
