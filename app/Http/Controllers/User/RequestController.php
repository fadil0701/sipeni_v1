<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Portal permintaan legacy — dialihkan ke modul transaksi utama
 * (`transaction.permintaan-barang`) agar satu pintu UI.
 */
class RequestController extends Controller
{
    public function index(Request $request)
    {
        return redirect()->route('transaction.permintaan-barang.index', $request->query());
    }

    public function create()
    {
        return redirect()->route('transaction.permintaan-barang.create');
    }

    public function store()
    {
        return redirect()->route('transaction.permintaan-barang.create')
            ->with('info', 'Gunakan form Permintaan Barang di menu Transaksi.');
    }

    public function show($id)
    {
        return redirect()->route('transaction.permintaan-barang.show', $id);
    }
}
