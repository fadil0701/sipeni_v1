<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DraftDistribusiController extends Controller
{
    public function index(Request $request)
    {
        return redirect()->route('transaction.distribusi.index')
            ->with('info', 'Draft Distribusi sudah disederhanakan ke modul Distribusi.');
    }

    public function create($approvalLogId)
    {
        return redirect()->route('transaction.distribusi.create', ['approval_log' => $approvalLogId]);
    }

    public function store(Request $request)
    {
        return redirect()->route('transaction.distribusi.index')
            ->with('error', 'Gunakan modul Distribusi untuk membuat SPPB.');
    }

    public function show($id)
    {
        return redirect()->route('transaction.distribusi.show', $id);
    }
}
