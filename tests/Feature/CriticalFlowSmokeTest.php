<?php

namespace Tests\Feature;

use Tests\TestCase;

class CriticalFlowSmokeTest extends TestCase
{
    public function test_guest_redirected_from_distribusi_index(): void
    {
        $this->get('/transaction/distribusi')->assertRedirect('/login');
    }

    public function test_guest_redirected_from_penerimaan_index(): void
    {
        $this->get('/transaction/penerimaan-barang')->assertRedirect('/login');
    }

    public function test_guest_redirected_from_retur_index(): void
    {
        $this->get('/transaction/retur-barang')->assertRedirect('/login');
    }

    public function test_guest_redirected_from_pemakaian_index(): void
    {
        $this->get('/transaction/pemakaian-barang')->assertRedirect('/login');
    }

    public function test_guest_redirected_from_permintaan_index(): void
    {
        $this->get('/transaction/permintaan-barang')->assertRedirect('/login');
    }

    public function test_guest_redirected_from_asset_register_index(): void
    {
        $this->get('/asset/register-aset')->assertRedirect('/login');
    }

    public function test_guest_redirected_from_asset_mutasi_index(): void
    {
        $this->get('/asset/mutasi-aset')->assertRedirect('/login');
    }

    public function test_guest_redirected_from_report_stock(): void
    {
        $this->get('/reports/stock-gudang')->assertRedirect('/login');
    }

    public function test_guest_redirected_from_report_kartu_stok(): void
    {
        $this->get('/reports/kartu-stok')->assertRedirect('/login');
    }

    public function test_guest_redirected_from_report_transaksi_summary(): void
    {
        $this->get('/reports/transaksi-summary')->assertRedirect('/login');
    }

    public function test_guest_redirected_from_report_aset_summary(): void
    {
        $this->get('/reports/aset-summary')->assertRedirect('/login');
    }
}
