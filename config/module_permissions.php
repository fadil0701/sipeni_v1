<?php

/**
 * Single source of truth — module metadata for permission matrix, features, and future workflow/audit.
 * Sidebar remains route-permission based; do not use this file to render sidebar menus directly.
 */
return [

    'actions' => ['view', 'create', 'update', 'approve'],

    'default_features' => [
        'approval' => false,
        'workflow' => false,
        'audit' => false,
        'dashboard' => false,
        'notifications' => false,
        'inventory_effect' => false,
    ],

    'action_actions' => [
        'view' => ['index', 'show', 'detail'],
        'create' => ['create', 'store'],
        'update' => ['edit', 'update', 'destroy'],
        'approve' => [
            'approve', 'reject', 'disposisi', 'verifikasi', 'mengetahui',
            'kembalikan', 'ajukan', 'proses', 'kirim', 'terima', 'tolak',
            'selesai', 'verifikasi-unit-a', 'approve-unit-b', 'reject-unit-b',
            'approve-pengurus', 'reject-pengurus', 'mengetahui-kasubag-tu',
            'serah-terima', 'pengembalian', 'cancel', 'revise', 'submit',
        ],
    ],

    'modules' => [
        'manajemen-user-role' => [
            'label' => 'Manajemen User & Role',
            'display_group' => 'administrasi',
            'actions' => ['view', 'create', 'update'],
            'prefixes' => [
                'user.', 'admin.roles.', 'admin.users.', 'profile.',
            ],
            'permissions' => [
                'view_users', 'create_users', 'update_users',
                'view_roles', 'create_roles', 'update_roles',
            ],
            'features' => [
                'approval' => false,
                'workflow' => false,
                'audit' => true,
                'dashboard' => true,
                'notifications' => false,
                'inventory_effect' => false,
            ],
        ],
        'approval' => [
            'label' => 'Approval',
            'display_group' => 'administrasi',
            'actions' => ['view', 'approve'],
            'prefixes' => [
                'transaction.approval.',
            ],
            'permissions' => [
                'view_approval', 'approve_approval',
            ],
            'features' => [
                'approval' => true,
                'workflow' => true,
                'audit' => true,
                'dashboard' => false,
                'notifications' => true,
                'inventory_effect' => false,
            ],
        ],
        'aset-tetap-kir' => [
            'label' => 'Aset Tetap & KIR',
            'display_group' => 'inventori',
            'actions' => ['view', 'create', 'update'],
            'prefixes' => [
                'asset.',
            ],
            'permissions' => [
                'view_register_aset',
                'create_register_aset',
                'update_register_aset',
                'view_mutasi_aset',
                'view_kir',
                'view_monitoring_aset',
            ],
            'permission_map' => [
                'view_register_aset' => ['register-aset'],
                'create_register_aset' => ['register-aset.create', 'register-aset.store'],
                'update_register_aset' => ['register-aset.edit', 'register-aset.update', 'register-aset.destroy'],
                'view_mutasi_aset' => ['mutasi-aset'],
                'view_kir' => ['kartu-inventaris-ruangan'],
                'view_monitoring_aset' => ['register-aset.index', 'register-aset.show'],
            ],
            'features' => [
                'approval' => false,
                'workflow' => true,
                'audit' => true,
                'dashboard' => true,
                'notifications' => false,
                'inventory_effect' => true,
            ],
        ],
        'persediaan' => [
            'label' => 'Persediaan',
            'display_group' => 'inventori',
            'actions' => ['view', 'create', 'update'],
            'prefixes' => [
                'inventory.data-stock.', 'inventory.data-inventory.',
                'inventory.stock-adjustment.', 'inventory.inventory-item.',
            ],
            'permissions' => ['view_stock', 'create_stock', 'update_stock'],
            'features' => [
                'approval' => false,
                'workflow' => false,
                'audit' => true,
                'dashboard' => true,
                'notifications' => false,
                'inventory_effect' => true,
            ],
        ],
        'farmasi' => [
            'label' => 'Farmasi',
            'display_group' => 'inventori',
            'actions' => ['view', 'create', 'update'],
            'prefixes' => [
                'inventory.farmasi-kedaluwarsa.',
                'inventory.data-stock.', 'inventory.data-inventory.',
            ],
            'permissions' => ['view_farmasi', 'create_farmasi', 'update_farmasi'],
            'features' => [
                'approval' => false,
                'workflow' => false,
                'audit' => true,
                'dashboard' => true,
                'notifications' => true,
                'inventory_effect' => true,
            ],
        ],
        'distribusi' => [
            'label' => 'Distribusi',
            'display_group' => 'inventori',
            'actions' => ['view', 'create', 'update', 'approve'],
            'prefixes' => [
                'transaction.draft-distribusi.', 'transaction.compile-distribusi.',
                'transaction.distribusi.', 'transaction.penerimaan-barang.',
                'transaction.retur-barang.', 'transaction.pengembalian-barang.',
            ],
            'permissions' => ['view_distribusi', 'create_distribusi', 'update_distribusi', 'approve_distribusi'],
            'features' => [
                'approval' => true,
                'workflow' => true,
                'audit' => true,
                'dashboard' => true,
                'notifications' => true,
                'inventory_effect' => true,
            ],
        ],
        'permintaan-barang' => [
            'label' => 'Permintaan Barang',
            'display_group' => 'transaksi-unit',
            'actions' => ['view', 'create', 'update', 'approve'],
            'prefixes' => [
                'transaction.permintaan-barang.',
            ],
            'permissions' => ['view_permintaan', 'create_permintaan', 'update_permintaan', 'approve_permintaan'],
            'features' => [
                'approval' => true,
                'workflow' => true,
                'audit' => true,
                'dashboard' => true,
                'notifications' => true,
                'inventory_effect' => true,
            ],
        ],
        'peminjaman-barang' => [
            'label' => 'Peminjaman Barang',
            'display_group' => 'transaksi-unit',
            'actions' => ['view', 'create', 'update', 'approve'],
            'prefixes' => [
                'transaction.peminjaman-barang.',
            ],
            'permissions' => ['view_peminjaman', 'create_peminjaman', 'update_peminjaman', 'approve_peminjaman'],
            'features' => [
                'approval' => true,
                'workflow' => true,
                'audit' => true,
                'dashboard' => false,
                'notifications' => true,
                'inventory_effect' => true,
            ],
        ],
        'pemeliharaan-aset' => [
            'label' => 'Pemeliharaan Aset',
            'display_group' => 'transaksi-unit',
            'actions' => ['view', 'create', 'update'],
            'prefixes' => [
                'maintenance.permintaan-pemeliharaan.',
                'maintenance.jadwal-maintenance.',
                'maintenance.service-report.',
            ],
            'permissions' => ['view_pemeliharaan', 'create_pemeliharaan', 'update_pemeliharaan'],
            'features' => [
                'approval' => false,
                'workflow' => true,
                'audit' => true,
                'dashboard' => false,
                'notifications' => true,
                'inventory_effect' => false,
            ],
        ],
        'kalibrasi' => [
            'label' => 'Kalibrasi',
            'display_group' => 'transaksi-unit',
            'actions' => ['view', 'create', 'update'],
            'prefixes' => [
                'maintenance.kalibrasi-aset.',
            ],
            'permissions' => ['view_kalibrasi', 'create_kalibrasi', 'update_kalibrasi'],
            'features' => [
                'approval' => false,
                'workflow' => false,
                'audit' => true,
                'dashboard' => false,
                'notifications' => false,
                'inventory_effect' => false,
            ],
        ],
        'pengadaan-rku' => [
            'label' => 'Pengadaan / RKU',
            'display_group' => 'perencanaan-laporan',
            'actions' => ['view', 'create', 'update', 'approve'],
            'prefixes' => [
                'planning.', 'procurement.', 'finance.',
            ],
            'permissions' => ['view_rku', 'create_rku', 'update_rku', 'approve_rku'],
            'features' => [
                'approval' => true,
                'workflow' => true,
                'audit' => true,
                'dashboard' => true,
                'notifications' => true,
                'inventory_effect' => false,
            ],
        ],
        'monitoring-dashboard' => [
            'label' => 'Monitoring & Dashboard',
            'display_group' => 'perencanaan-laporan',
            'actions' => ['view'],
            'prefixes' => [
                'user.dashboard',
            ],
            'permissions' => ['view_dashboard'],
            'features' => [
                'approval' => false,
                'workflow' => false,
                'audit' => false,
                'dashboard' => true,
                'notifications' => false,
                'inventory_effect' => false,
            ],
        ],
        'laporan' => [
            'label' => 'Laporan',
            'display_group' => 'perencanaan-laporan',
            'actions' => ['view'],
            'prefixes' => [
                'reports.',
            ],
            'permissions' => ['view_laporan'],
            'features' => [
                'approval' => false,
                'workflow' => false,
                'audit' => true,
                'dashboard' => false,
                'notifications' => false,
                'inventory_effect' => false,
            ],
        ],
        'template-dokumen' => [
            'label' => 'Template Dokumen',
            'display_group' => 'perencanaan-laporan',
            'actions' => ['view', 'create', 'update'],
            'prefixes' => [
                'admin.print-templates.',
            ],
            'permissions' => ['view_template', 'create_template', 'update_template'],
            'features' => [
                'approval' => false,
                'workflow' => false,
                'audit' => true,
                'dashboard' => false,
                'notifications' => false,
                'inventory_effect' => false,
            ],
        ],
        'master-data' => [
            'label' => 'Master Data',
            'display_group' => 'perencanaan-laporan',
            'actions' => ['view', 'create', 'update'],
            'prefixes' => [
                'master.', 'master-data.', 'master-manajemen.',
            ],
            'permissions' => ['view_master', 'create_master', 'update_master'],
            'features' => [
                'approval' => false,
                'workflow' => false,
                'audit' => true,
                'dashboard' => false,
                'notifications' => false,
                'inventory_effect' => false,
            ],
        ],
        'workflow' => [
            'label' => 'Workflow',
            'display_group' => 'administrasi',
            'actions' => ['view', 'update'],
            'prefixes' => [
                'admin.roles.workflow-permissions.',
            ],
            'permissions' => ['view_workflow', 'update_workflow'],
            'features' => [
                'approval' => false,
                'workflow' => true,
                'audit' => true,
                'dashboard' => false,
                'notifications' => false,
                'inventory_effect' => false,
            ],
        ],
        'audit-trail' => [
            'label' => 'Audit Trail',
            'display_group' => 'administrasi',
            'actions' => ['view'],
            'prefixes' => [
                'admin.audit-trail.',
            ],
            'permissions' => ['view_audit'],
            'features' => [
                'approval' => false,
                'workflow' => false,
                'audit' => true,
                'dashboard' => false,
                'notifications' => false,
                'inventory_effect' => false,
            ],
        ],
    ],

    'display_groups' => [
        'administrasi' => [
            'label' => 'Administrasi',
            'modules' => ['manajemen-user-role', 'approval', 'workflow', 'audit-trail'],
        ],
        'inventori' => [
            'label' => 'Inventori',
            'modules' => ['aset-tetap-kir', 'persediaan', 'farmasi', 'distribusi'],
        ],
        'transaksi-unit' => [
            'label' => 'Transaksi Unit',
            'modules' => ['permintaan-barang', 'peminjaman-barang', 'pemeliharaan-aset', 'kalibrasi'],
        ],
        'perencanaan-laporan' => [
            'label' => 'Perencanaan & Laporan',
            'modules' => ['pengadaan-rku', 'monitoring-dashboard', 'laporan', 'template-dokumen', 'master-data'],
        ],
    ],
];
