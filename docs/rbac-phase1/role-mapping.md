# Pemetaan Role Legacy → Kanonik

## 15 role kanonik

| Grup | Role |
|------|------|
| Super Admin | `super_administrator` |
| Struktural | `kepala_pusat`, `kasubbag_tu` |
| Unit Kerja | `kepala_unit`, `admin_unit` |
| Perencanaan | `perencana` |
| Pengadaan | `pengadaan` |
| Keuangan | `keuangan` |
| PPTK | `pptk_apbd`, `pptk_blud` |
| Gudang Pusat | `pengurus_barang`, `admin_gudang_pusat` |
| Gudang Spesifik | `admin_gudang_aset`, `admin_gudang_persediaan`, `admin_gudang_farmasi` |

## Mapping legacy (tetap ada, `is_deprecated = true`)

| Role lama | Role kanonik | Catatan |
|-----------|--------------|---------|
| `pegawai`, `pegawai_unit` | `admin_unit` | Operator unit kerja |
| `admin_gudang_unit` | `admin_unit` | Stok/KIR unit |
| `admin_perencanaan`, `perencanaan` | `perencana` | |
| `admin_pengadaan_apbd`, `admin_pengadaan_blud` | `pengadaan` | |
| `admin_keuangan` | `keuangan` | |
| `admin_pptk_apbd` | `pptk_apbd` | |
| `admin_pptk_blud` | `pptk_blud` | |
| `admin_gudang` | `admin_gudang_pusat` | |
| `administrator`, `admin` | *(tetap)* | **Tidak bypass** — akses lewat permission DB |

Implementasi: `App\Support\Rbac\RoleCompatibility`.

## admin_unit

Gabungan fungsi `pegawai` + `admin_gudang_unit`: RKU unit, permintaan, penerimaan distribusi, stok/KIR unit. Scope data: `unit_kerja_id` user (middleware `scope.unit`).

## kepala_unit

Verifikasi/approval pengajuan unit; scope `unit_kerja_id`.
