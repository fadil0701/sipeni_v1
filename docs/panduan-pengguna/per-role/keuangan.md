# Panduan: Keuangan

> **Status modul (Juli 2026):** Menu **Keuangan → Pembayaran** **belum tersedia** di aplikasi. Controller historis masih stub; akses HTTP ditolak (`FEATURE_FINANCE_PEMBAYARAN=false`).

Role `keuangan` tetap ada di katalog RBAC untuk persiapan, tetapi **tidak ada workflow pembayaran yang bisa dijalankan di UI** saat ini.

## Yang harus diketahui Administrator

- Jangan menjanjikan user bahwa mereka bisa “input pembayaran” di SI-MANTIK sampai modul diimplementasi.
- Desain alur target (RKU → pengadaan → pembayaran) ada di dokumen teknis historis; bukan panduan operasional aktif.
- Setelah modul siap: set `FEATURE_FINANCE_PEMBAYARAN=true`, kembalikan menu sidebar, dan lengkapi view/controller.

## Sementara

| Kebutuhan | Alternatif |
|-----------|------------|
| Monitoring paket pengadaan | Menu **Pengadaan → Paket Pengadaan** / **Paket Berjalan** |
| Realisasi anggaran | Proses di luar SI-MANTIK / modul nanti |

Lihat juga: [PERBAIKAN_AUDIT_UI_CETAK_2026-07-24.md](../../PERBAIKAN_AUDIT_UI_CETAK_2026-07-24.md)
