<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CompileDistribusiController extends Controller
{
    public function index(Request $request)
    {
        return redirect()->route('transaction.distribusi.index')
            ->with('info', 'Compile Distribusi sudah disederhanakan ke modul Distribusi.');
    }

    public function create($permintaanId)
    {
        return redirect()->route('transaction.distribusi.create', ['permintaan_id' => $permintaanId]);
    }

    public function store(Request $request)
    {
        return redirect()->route('transaction.distribusi.index')
            ->with('error', 'Gunakan modul Distribusi untuk membuat SPPB.');
    }
}
