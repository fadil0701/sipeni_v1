# TODO - UI/UX Form Create & Edit RKU

## Step 1
- [ ] Unifikasi layout/spacing container, card header, card body antara `resources/views/planning/rku/create.blade.php` dan `resources/views/planning/rku/edit.blade.php`.

## Step 2
- [ ] Samakan struktur tabel detail (toolbar, empty-state handling, dan konsistensi perhitungan grand total).

## Step 3
- [ ] Perbaiki JavaScript tombol **Tambah Item** agar opsi `<select id_satuan>` muncul dengan benar (hindari Blade loop di template literal/backtick).
- [ ] Pastikan juga hitungan subtotal & grand total tetap berfungsi untuk baris yang ditambahkan saat validasi gagal (old('details')).

## Step 4
- [ ] Lakukan sanity check Blade compile (hindari syntax error) dan pastikan tidak ada perbedaan class/spacing yang mencolok.

