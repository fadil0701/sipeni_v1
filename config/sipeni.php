<?php

/**
 * Konfigurasi domain aplikasi SIPENI (boleh di-override lewat .env).
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Fitur template cetak dinamis (Admin + cetak SBBK dari template)
    |--------------------------------------------------------------------------
    |
    | Matikan untuk menyembunyikan menu "Template Cetak", tombol cetak SBBK,
    | dan menolak akses HTTP ke rute terkait. KIR / dokumen aset tidak
    | bergantung pada flag ini. Setel true saat modul siap dipakai lagi.
    |
    */
    'feature_print_templates' => env('FEATURE_PRINT_TEMPLATES', false),

    /** Modul Pemakaian Barang (default nonaktif — controller abort 404). */
    'feature_pemakaian_barang' => env('FEATURE_PEMAKAIAN_BARANG', false),

    /*
    |--------------------------------------------------------------------------
    | RBAC Tahap 1 — authorization & sidebar
    |--------------------------------------------------------------------------
    */
    'rbac' => [
        'bypass_roles' => ['super_administrator'],
        /**
         * Fallback legacy: filter sidebar via user_modules jika tidak ada permission submenu.
         * Matikan setelah semua role punya permission DB lengkap.
         */
        'legacy_user_modules_fallback' => env('RBAC_LEGACY_USER_MODULES_FALLBACK', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Keamanan HTTP
    |--------------------------------------------------------------------------
    */
    'security' => [
        'csp_enabled' => env('SECURITY_CSP_ENABLED', false),
        'csp_report_only' => env('SECURITY_CSP_REPORT_ONLY', true),
        'csp_policy' => env('SECURITY_CSP_POLICY', "default-src 'self'; script-src 'self' 'unsafe-inline' https://code.jquery.com https://cdn.jsdelivr.net https://unpkg.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.bunny.net; font-src 'self' data: https://fonts.bunny.net; img-src 'self' data: blob:; media-src 'self' blob:; connect-src 'self'; frame-ancestors 'self'; base-uri 'self'; form-action 'self'"),
        /** Izinkan kamera + geolocation same-origin (bukti sampai GPS). Microphone tetap off. */
        'permissions_policy' => env('SECURITY_PERMISSIONS_POLICY', 'camera=(self), microphone=(), geolocation=(self)'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Outbound HTTP (proxy korporat) — dipakai GeocodeService dll.
    | PHP-FPM sering tidak meneruskan getenv HTTP_PROXY, jadi baca via config.
    |--------------------------------------------------------------------------
    */
    'http' => [
        'http_proxy' => env('HTTP_PROXY'),
        'https_proxy' => env('HTTPS_PROXY'),
        'no_proxy' => env('NO_PROXY', 'localhost,127.0.0.1,mysql,.local'),
        /** false jika proxy MITM memutus SSL ke Nominatim/Photon */
        'geocode_ssl_verify' => filter_var(env('GEOCODE_SSL_VERIFY', true), FILTER_VALIDATE_BOOLEAN),
    ],

    /*
    |--------------------------------------------------------------------------
    | Two-factor authentication (TOTP)
    |--------------------------------------------------------------------------
    */
    'two_factor' => [
        'enabled' => env('TWO_FACTOR_ENABLED', true),
        'required_roles' => array_filter(array_map('trim', explode(',', env('TWO_FACTOR_REQUIRED_ROLES', 'super_administrator,admin')))),
    ],

    /*
    |--------------------------------------------------------------------------
    | Scope otorisasi unit kerja
    |--------------------------------------------------------------------------
    */
    'auth' => [
        'superadmin_bypass_scope' => env('SUPERADMIN_BYPASS_SCOPE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Kop surat cetak SBBK (mengikuti baris contoh PDF resmi)
    |--------------------------------------------------------------------------
    |
    | Empat baris tebal instansi + alamat + baris kota. Path logo opsional
    | (file di dalam proyek, mis. docs/Logo DKI.png) untuk tampilan kiri kop.
    |
    */
    'sbbk_kop_baris1' => env('SBBK_KOP_BARIS1', 'PEMERINTAH PROVINSI DAERAH KHUSUS IBUKOTA'),
    'sbbk_kop_baris2' => env('SBBK_KOP_BARIS2', 'JAKARTA'),
    'sbbk_kop_baris3' => env('SBBK_KOP_BARIS3', 'DINAS KESEHATAN'),
    'sbbk_kop_baris4' => env('SBBK_KOP_BARIS4', 'PUSAT PELAYANAN KESEHATAN PEGAWAI'),
    'sbbk_kop_alamat' => env('SBBK_KOP_ALAMAT', 'JL. Medan Merdeka Selatan No. 8-9 Blok E Lantai 2, Jakarta Pusat, Telp. 3823065'),
    'sbbk_kop_kota' => env('SBBK_KOP_KOTA', 'JAKARTA PUSAT'),
    'sbbk_kop_logo_path' => env('SBBK_KOP_LOGO_PATH', 'docs/Logo DKI.png'),

    /*
    |--------------------------------------------------------------------------
    | Nama / NIP opsional pada blok tanda tangan SBBK (isi jika ingin terisi otomatis)
    |--------------------------------------------------------------------------
    */
    'sbbk_ttd_admin_gudang_nama' => env('SBBK_TTD_ADMIN_GUDANG_NAMA', ''),
    'sbbk_ttd_admin_gudang_nip' => env('SBBK_TTD_ADMIN_GUDANG_NIP', ''),
    'sbbk_ttd_mengetahui_nama' => env('SBBK_TTD_MENGETAHUI_NAMA', ''),
    'sbbk_ttd_mengetahui_nip' => env('SBBK_TTD_MENGETAHUI_NIP', ''),
    'sbbk_ttd_pengurus_barang_nama' => env('SBBK_TTD_PENGURUS_BARANG_NAMA', ''),
    'sbbk_ttd_pengurus_barang_nip' => env('SBBK_TTD_PENGURUS_BARANG_NIP', ''),

    /*
    |--------------------------------------------------------------------------
    | Notifikasi UI (flash banner + toast opsional)
    |--------------------------------------------------------------------------
    */
    'notifications' => [
        'toast_mirror_flash' => env('SIPENI_TOAST_MIRROR_FLASH', false),
        'toast_default_ms' => (int) env('SIPENI_TOAST_DEFAULT_MS', 4500),
        'mail_enabled' => env('NOTIFICATIONS_MAIL_ENABLED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Akun awal & demo (dikonfigurasi lewat environment)
    |--------------------------------------------------------------------------
    */
    'users' => [
        'super_admin' => [
            'name' => env('SIPENI_SUPER_ADMIN_NAME', ''),
            'email' => env('SIPENI_SUPER_ADMIN_EMAIL', ''),
            'password' => env('SIPENI_SUPER_ADMIN_PASSWORD', ''),
        ],
        'admin_it' => [
            'name' => env('SIPENI_ADMIN_IT_NAME', 'Admin IT / Pengelola Aplikasi'),
            'email' => env('SIPENI_ADMIN_IT_EMAIL', ''),
            'password' => env('SIPENI_ADMIN_IT_PASSWORD', ''),
        ],
        /** User demo per jabatan (PegawaiUserPerJabatanSeeder). Matikan di production. */
        'seed_demo_users' => filter_var(env('SIPENI_SEED_DEMO_USERS', true), FILTER_VALIDATE_BOOL),
        'demo_pegawai_password' => env('SIPENI_DEMO_PEGAWAI_PASSWORD', ''),
        'demo_emails' => [
            'pemohon' => env('SIPENI_DEMO_EMAIL_PEMOHON', ''),
            'admin_gudang' => env('SIPENI_DEMO_EMAIL_ADMIN_GUDANG', ''),
            'perencana' => env('SIPENI_DEMO_EMAIL_PERENCANA', ''),
        ],
    ],

];
