@push('scripts')
<script>
let inventoryOptions = [];
let detailRowIndex = 0;
const editPrefillDetails = @json($editPrefillDetails ?? []);
const gudangPusatByKategori = @json($gudangPusatByKategori ?? []);
const urlPegawaiByUnit = @json(rtrim(route('api.master.pegawai-by-unit', ['id_unit_kerja' => 0]), '0'));
const urlGudangByUnit = @json(rtrim(route('api.master.gudang-by-unit', ['id_unit_kerja' => 0]), '0'));
const urlInventoryByGudang = @json(rtrim(route('api.master.inventory-by-gudang', ['id_gudang' => 0]), '0'));
const initialGudangAsal = @json($initialGudangAsal ?? null);
const initialPegawai = @json($initialPegawai ?? null);
let inventoryLoadedForGudang = null;

function setGudangTujuanByKategori(kategori) {
    const tujuanSel = document.getElementById('id_gudang_tujuan');
    if (!tujuanSel || !kategori) return;
    const idGudang = gudangPusatByKategori[kategori];
    if (idGudang) {
        tujuanSel.value = String(idGudang);
    }
}

function loadInventoryByGudang(gudangId, keepPrefill = false) {
    const container = document.getElementById('detailContainer');
    if (!gudangId) {
        inventoryOptions = [];
        inventoryLoadedForGudang = null;
        if (container) container.innerHTML = '';
        return;
    }

    inventoryLoadedForGudang = String(gudangId);
    fetch(`${urlInventoryByGudang}/${gudangId}`, {
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
    })
        .then((r) => r.json())
        .then((data) => {
            inventoryOptions = data.data || [];
            if (!container) return;
            container.innerHTML = '';
            detailRowIndex = 0;

            const prefill = keepPrefill && editPrefillDetails.length > 0 ? editPrefillDetails : [];
            if (prefill.length > 0) {
                prefill.forEach((row) => {
                    const el = createDetailRow(row);
                    if (el) container.appendChild(el);
                });
            } else if (inventoryOptions.length > 0) {
                const el = createDetailRow();
                if (el) container.appendChild(el);
            }
        });
}

function loadByUnitKerja(unitId) {
    const asalSel = document.getElementById('id_gudang_asal');
    const pegawaiSel = document.getElementById('id_pegawai_pengirim');
    if (!unitId) {
        if (asalSel) asalSel.innerHTML = '<option value="">Pilih Gudang Asal</option>';
        if (pegawaiSel) pegawaiSel.innerHTML = '<option value="">Pilih Pegawai Pengirim</option>';
        loadInventoryByGudang(null);
        return;
    }

    if (asalSel) {
        asalSel.innerHTML = '<option value="">Memuat Gudang...</option>';
        fetch(`${urlGudangByUnit}/${unitId}`, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
            .then((r) => r.json())
            .then((data) => {
                asalSel.innerHTML = '<option value="">Pilih Gudang Asal</option>';
                (data.data || []).forEach((g) => {
                    if (g.jenis_gudang !== 'UNIT') return;
                    const opt = document.createElement('option');
                    opt.value = g.id_gudang;
                    opt.textContent = g.label;
                    asalSel.appendChild(opt);
                });
                if (initialGudangAsal) {
                    asalSel.value = String(initialGudangAsal);
                    loadInventoryByGudang(initialGudangAsal, true);
                }
            });
    }

    if (pegawaiSel) {
        pegawaiSel.innerHTML = '<option value="">Memuat Pegawai...</option>';
        fetch(`${urlPegawaiByUnit}/${unitId}`, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
            .then((r) => r.json())
            .then((data) => {
                pegawaiSel.innerHTML = '<option value="">Pilih Pegawai Pengirim</option>';
                (data.data || []).forEach((p) => {
                    const opt = document.createElement('option');
                    opt.value = p.id;
                    opt.textContent = p.label;
                    pegawaiSel.appendChild(opt);
                });
                if (initialPegawai) pegawaiSel.value = String(initialPegawai);
            });
    }
}

function createDetailRow(prefill = {}) {
    const template = document.getElementById('itemTemplate');
    if (!template) return null;

    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = template.innerHTML.replace(/INDEX/g, detailRowIndex++);
    const row = tempDiv.firstElementChild;
    if (!row) return null;

    const barangSelect = row.querySelector('.select-barang');
    const qtyDiterimaInput = row.querySelector('.qty-diterima');
    const qtyReturInput = row.querySelector('.qty-retur-input');
    const satuanSelect = row.querySelector('.select-satuan');

    if (barangSelect) {
        inventoryOptions.forEach((detail) => {
            const opt = document.createElement('option');
            opt.value = String(detail.id_inventory);
            opt.textContent = detail.label || detail.nama_barang;
            barangSelect.appendChild(opt);
        });
    }

    const syncRowByInventory = (idInventory) => {
        const selected = inventoryOptions.find((d) => String(d.id_inventory) === String(idInventory));
        if (!selected) {
            qtyDiterimaInput.value = '';
            qtyReturInput.max = '';
            if (!prefill.id_satuan) satuanSelect.value = '';
            return;
        }

        qtyDiterimaInput.value = selected.qty_tersedia ?? '0';
        qtyReturInput.max = selected.qty_tersedia ?? '0';
        if (!prefill.id_satuan) satuanSelect.value = selected.id_satuan ?? '';
        setGudangTujuanByKategori(selected.kategori_gudang_pusat);
    };

    barangSelect?.addEventListener('change', function () {
        syncRowByInventory(this.value);
    });

    row.querySelector('.remove-row')?.addEventListener('click', function () {
        row.remove();
    });

    if (prefill.id_inventory && barangSelect) {
        barangSelect.value = String(prefill.id_inventory);
        syncRowByInventory(prefill.id_inventory);
    }
    if (prefill.qty_retur != null && prefill.qty_retur !== '' && qtyReturInput) {
        qtyReturInput.value = prefill.qty_retur;
    }
    if (prefill.id_satuan && satuanSelect) satuanSelect.value = String(prefill.id_satuan);
    const alasanInput = row.querySelector('[name*="[alasan_retur_item]"]');
    if (prefill.alasan_retur_item != null && prefill.alasan_retur_item !== '' && alasanInput) {
        alasanInput.value = prefill.alasan_retur_item;
    }

    return row;
}

document.addEventListener('DOMContentLoaded', function () {
    const unitSel = document.getElementById('id_unit_kerja');
    const asalSel = document.getElementById('id_gudang_asal');

    if (unitSel?.value) {
        loadByUnitKerja(unitSel.value);
    }

    asalSel?.addEventListener('change', function () {
        const gudangId = this.value;
        if (String(inventoryLoadedForGudang) === String(gudangId)) return;
        loadInventoryByGudang(gudangId, false);
    });

    document.getElementById('addDetailRowBtn')?.addEventListener('click', function () {
        if (!inventoryOptions.length) {
            alert('Pilih gudang asal terlebih dahulu.');
            return;
        }
        const row = createDetailRow();
        if (row) document.getElementById('detailContainer').appendChild(row);
    });

    const formRetur = document.getElementById('formRetur');
    if (formRetur) {
        formRetur.addEventListener('submit', function (e) {
            const detailContainer = document.getElementById('detailContainer');
            const detailRows = detailContainer.querySelectorAll('.item-row');

            if (detailRows.length === 0) {
                e.preventDefault();
                alert('Detail retur belum diisi. Tambahkan minimal 1 baris item.');
                return false;
            }

            let isValid = true;
            const emptyFields = [];
            detailRows.forEach((row, index) => {
                const idInventory = row.querySelector('[name*="[id_inventory]"]');
                const qtyRetur = row.querySelector('[name*="[qty_retur]"]');
                const idSatuan = row.querySelector('[name*="[id_satuan]"]');
                const qtyDiterima = parseFloat(row.querySelector('.qty-diterima').value || 0);

                if (!idInventory || !idInventory.value) {
                    isValid = false;
                    emptyFields.push(`Item ${index + 1}: Barang`);
                }
                if (!qtyRetur || !qtyRetur.value || parseFloat(qtyRetur.value) <= 0) {
                    isValid = false;
                    emptyFields.push(`Item ${index + 1}: Qty Retur`);
                } else if (parseFloat(qtyRetur.value) > qtyDiterima) {
                    isValid = false;
                    emptyFields.push(`Item ${index + 1}: Qty Retur melebihi stok tersedia (${qtyDiterima})`);
                }
                if (!idSatuan || !idSatuan.value) {
                    isValid = false;
                    emptyFields.push(`Item ${index + 1}: Satuan`);
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Mohon lengkapi semua field yang wajib diisi:\n' + emptyFields.join('\n'));
                return false;
            }
        });
    }
});
</script>
@endpush
