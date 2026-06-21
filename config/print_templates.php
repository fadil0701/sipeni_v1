<?php

use App\Services\ReturPrintTemplateData;
use App\Services\SbbkPrintTemplateData;

/**
 * Provider variabel (opsional) per **key** template.
 *
 * - Template surat itu **generik**: di HTML Anda tulis {{nama_field}} lalu isi nilainya lewat
 *   **Data contoh (JSON)** untuk pratinjau/PDF contoh, atau lewat array PHP saat `PrintTemplateRenderer::render()`.
 * - Entri di bawah ini **bukan** daftar semua surat yang boleh ada — hanya shortcut supaya chip
 *   variabel + dokumentasi payload otomatis untuk fitur yang sudah di-wire di kode (mis. SBBK).
 * - Surat baru **tanpa** PHP: gunakan key bebas (mis. `surat.pengantar`), placeholder + JSON contoh saja.
 * - Surat baru **dengan** data hidup dari database: tambahkan class dengan `variableGroups()` + panggil
 *   `render($template, YourClass::payload($model))` dari controller (contoh: `DistribusiController::printSbbk`).
 */
return [

    'variable_providers' => [
        'distribusi.sbbk' => SbbkPrintTemplateData::class,
        'retur.pengembalian' => ReturPrintTemplateData::class,
    ],

];
