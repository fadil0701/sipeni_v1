<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Modul Pembayaran belum diimplementasi (controller stub historis).
 * Route tetap ada untuk kompatibilitas permission; akses HTTP ditolak.
 *
 * @see docs/PERBAIKAN_AUDIT_UI_CETAK_2026-07-24.md
 */
class PembayaranController extends Controller
{
    public function __construct()
    {
        if (! config('sipeni.feature_finance_pembayaran', false)) {
            abort(404, 'Modul Keuangan / Pembayaran belum tersedia.');
        }
    }

    public function index()
    {
        abort(501, 'Modul Pembayaran belum diimplementasi.');
    }

    public function create()
    {
        abort(501, 'Modul Pembayaran belum diimplementasi.');
    }

    public function store(Request $request)
    {
        abort(501, 'Modul Pembayaran belum diimplementasi.');
    }

    public function show(string $id)
    {
        abort(501, 'Modul Pembayaran belum diimplementasi.');
    }

    public function edit(string $id)
    {
        abort(501, 'Modul Pembayaran belum diimplementasi.');
    }

    public function update(Request $request, string $id)
    {
        abort(501, 'Modul Pembayaran belum diimplementasi.');
    }

    public function destroy(string $id)
    {
        abort(501, 'Modul Pembayaran belum diimplementasi.');
    }
}
