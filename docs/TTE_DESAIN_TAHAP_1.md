# Desain integrasi TTE — Tahap 1 (internal)

Tujuan selaras dengan `RINGKASAN_PROGRES_SIPENI.md` §2.F: jejak verifikasi pada dokumen cetak/unduh tanpa PSrE pada tahap ini — **kode verifikasi**, **QR**, **hash konten**, dan **metadata/log**.

## Ruang lingkup tahap 1

| Komponen | Deskripsi |
|----------|-----------|
| **Segel dokumen** | Rekaman satu versi konten dokumen (snapshot) dengan hash SHA-256. |
| **Token publik** | String acak tidak ketebak (URL `/verifikasi-dokumen/{token}`). |
| **Kode verifikasi** | Format mudah dibaca (mis. `XXXX-XXXX-XXXX`) untuk input manual jika perlu. |
| **QR** | Mengarah ke URL verifikasi yang sama dengan token (tanpa menyimpan secret server di QR selain token itu sendiri). |
| **Metadata** | JSON: jenis dokumen, referensi (mis. `id_unit_kerja`), ringkasan statistik, user yang memicu segel (jika ada). |

**Bukan** tahap 1: tanda tangan BSrE/PSrE, timestamp authority eksternal, atau pengikatan hukum penuh.

## Model data (`tte_document_seals`)

- **`document_type`**: mis. `kir_unit` untuk Dokumen KIR per unit kerja.
- **`reference_id`**: kunci domain (mis. `id_unit_kerja`).
- **`content_hash_sha256`**: hash dari **snapshot kanonik** (lihat bawah), bukan dari HTML render mentah (menghindari perbedaan whitespace/toolbar).
- **`public_token`**: unik, untuk URL.
- **`verification_code`**: unik, untuk komunikasi lisan/manual.
- **`meta`**: JSON (nama unit, jumlah baris KIR, `issued_by_user_id`, dll.).
- **`issued_at`** / timestamps standar.

**Dedup**: untuk kombinasi `(document_type, content_hash_sha256)` yang sama, sistem dapat mengembalikan segel yang sudah ada agar QR/kode tetap konsisten untuk revisi data yang sama.

## Tanda tangan per penandatangan (KIR)

Satu dokumen KIR memiliki **tiga peran** footer yang masing-masing dapat melakukan **TTE internal** secara terpisah:

| Peran (`signer_role`) | Kolom dokumen |
|----------------------|----------------|
| `kepala_unit` | Kepala Ruangan/Unit Kerja |
| `pengurus_barang` | Pengurus Barang |
| `kepala_pusat` | Mengetahui — Kepala Pusat |

**Tabel `tte_document_signatures`**: satu baris per `(segel, peran)` — `expected_pegawai_id` (dari master pegawai/jabatan), `signed_by_user_id`, `signed_at`, `signature_hash` (ikat ke hash konten + peran + user + waktu).

**Aturan**: hanya user yang `master_pegawai.user_id` sama dengan pegawai yang diharapkan untuk peran tersebut yang dapat menandatangani slot itu; **kepala_unit** harus dari unit kerja dokumen. Tanpa pegawai terpetakan untuk peran tersebut, slot tidak dapat ditandatangani hingga master diisi.

**Urutan**: tidak dipaksakan (parallel); halaman verifikasi menampilkan status per peran.

## Snapshot kanonik (KIR unit)

Hash dihitung dari struktur deterministik, bukan dari HTML:

- `v`: versi skema snapshot (integer).
- `type`: `kir_unit`.
- `unit_id`: `id_unit_kerja`.
- `items`: daftar baris berurut `id_kir`, plus `updated_at` ISO8601 per baris (agar perubahan data mengubah hash).

Perluasan ke dokumen lain: tambah `document_type` + builder snapshot terpisah.

## Alur generate

1. Pengguna membuka dokumen KIR dengan opsi **segel** (mis. query `tte=1` atau unduh dengan segel — dapat dipilih produk).
2. Controller memuat data seperti sekarang, membangun snapshot, hash.
3. `TteSealService::createOrGetSealForKirUnit(...)` mencari atau membuat baris `tte_document_seals`.
4. View menampilkan blok segel (QR SVG + kode + cuplikan hash) pada dokumen.

## Alur verifikasi publik

1. GET `/verifikasi-dokumen/{token}` tanpa login.
2. Jika token dikenal: tampilkan status valid, metadata, hash penuh (atau terpotong dengan salin penuh), **tanpa** membocorkan data sensitif baru di luar yang sudah di-segel.
3. Jika tidak: halaman tidak ditemukan / tidak valid (403/404 sesuai kebijakan).

**Keamanan**: rate limiting disarankan pada route publik (middleware throttle); token panjang 64 hex mengurangi brute force.

## Logging audit (lanjutan)

Tahap 1 menyimpan rekaman segel di DB. Log aplikasi terpisah (`laravel.log`) dapat ditambah pada create/verify jika diperlukan.

## Evolusi ke PSrE / TTE resmi

- Ganti atau tambahkan kolom `external_signature_id`, `certificate_subject`, dll.
- Snapshot hash dapat dipertahankan sebagai **bukti integritas konten** yang sama dengan yang akan ditandatangani.

## Referensi kode

- Controller dokumen: `App\Http\Controllers\Asset\KartuInventarisRuanganController::dokumenUnitKerja`
- View: `resources/views/asset/kartu-inventaris-ruangan/document-unit.blade.php`
- Route dokumen: `asset.kartu-inventaris-ruangan.dokumen-unit`
- Route verifikasi: `verifikasi-dokumen.show` → `/verifikasi-dokumen/{token}`
