@extends('layouts.app')

@section('title', 'Diagram Flow Approval')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">Diagram Resmi Approval Berjenjang (Multi-Level)</h1>
        <p class="text-gray-600 mb-8">Sistem Manajemen Aset & Inventory</p>

        <!-- Flow Diagram -->
        <div class="mb-12">
            <h2 class="text-xl font-semibold mb-6 text-gray-700">Alur Persetujuan</h2>
            
            <!-- Flow Container -->
            <div class="relative overflow-x-auto pb-8">
                <div class="flex flex-col items-center space-y-8 min-w-max">
                    
                    <!-- Step 1: Pegawai (Admin) -->
                    <div class="flex flex-col items-center">
                        <div class="bg-blue-500 text-white rounded-lg p-6 shadow-lg w-64 text-center">
                            <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                            <h3 class="font-bold text-lg">Pegawai (Admin)</h3>
                            <p class="text-sm mt-1">Pemohon</p>
                        </div>
                        <div class="mt-2">
                            <span class="bg-gray-200 text-gray-700 px-3 py-1 rounded-full text-xs font-semibold">Draft / Diajukan</span>
                        </div>
                    </div>

                    <!-- Arrow Down -->
                    <div class="flex justify-center">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>

                    <!-- Step 2: KEPALA UNIT (MENGETAHUI) -->
                    <div class="flex flex-col items-center">
                        <div class="bg-green-500 text-white rounded-lg p-6 shadow-lg w-64 text-center">
                            <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 class="font-bold text-lg">KEPALA UNIT</h3>
                            <p class="text-sm mt-1">MENGETAHUI</p>
                        </div>
                        <div class="mt-2">
                            <span class="bg-green-200 text-green-700 px-3 py-1 rounded-full text-xs font-semibold">Diketahui Unit</span>
                        </div>
                    </div>

                    <!-- Arrow Down -->
                    <div class="flex justify-center">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>

                    <!-- Step 3: KASUBBAG TU (VERIFIKASI) -->
                    <div class="flex flex-col items-center">
                        <div class="bg-yellow-500 text-white rounded-lg p-6 shadow-lg w-64 text-center">
                            <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            <h3 class="font-bold text-lg">KASUBBAG TU</h3>
                            <p class="text-sm mt-1">VERIFIKASI</p>
                        </div>
                        <div class="mt-2 flex space-x-2">
                            <span class="bg-yellow-200 text-yellow-700 px-3 py-1 rounded-full text-xs font-semibold">Diketahui TU</span>
                            <span class="bg-red-200 text-red-700 px-3 py-1 rounded-full text-xs font-semibold">Ditolak</span>
                        </div>
                    </div>

                    <!-- Arrow Down (with branch) -->
                    <div class="flex justify-center relative">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                        <!-- Branch arrow to Admin Gudang (green) -->
                        <div class="absolute left-1/2 transform -translate-x-1/2 top-8">
                            <div class="flex items-center space-x-2">
                                <svg class="w-6 h-6 text-green-500 rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                                <span class="text-xs text-green-600 font-semibold">Alternatif</span>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: KEPALA PUSAT -->
                    <div class="flex flex-col items-center">
                        <div class="bg-purple-500 text-white rounded-lg p-6 shadow-lg w-64 text-center">
                            <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                            <h3 class="font-bold text-lg">KEPALA PUSAT</h3>
                            <p class="text-sm mt-1">Pimpinan</p>
                        </div>
                        <div class="mt-2 flex space-x-2">
                            <span class="bg-purple-200 text-purple-700 px-3 py-1 rounded-full text-xs font-semibold">Disetujui Pimpinan</span>
                            <span class="bg-red-200 text-red-700 px-3 py-1 rounded-full text-xs font-semibold">Ditolak</span>
                            <span class="bg-blue-200 text-blue-700 px-3 py-1 rounded-full text-xs font-semibold">Didisposisikan</span>
                        </div>
                    </div>

                    <!-- Arrow Down -->
                    <div class="flex justify-center">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>

                    <!-- Step 5: ADMIN GUDANG / UNIT TERKAIT -->
                    <div class="flex flex-col items-center">
                        <div class="bg-indigo-500 text-white rounded-lg p-6 shadow-lg w-64 text-center">
                            <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <h3 class="font-bold text-lg">ADMIN GUDANG</h3>
                            <p class="text-sm mt-1">UNIT TERKAIT</p>
                        </div>
                        <div class="mt-2 flex space-x-2">
                            <span class="bg-indigo-200 text-indigo-700 px-3 py-1 rounded-full text-xs font-semibold">Didisposisikan</span>
                            <span class="bg-orange-200 text-orange-700 px-3 py-1 rounded-full text-xs font-semibold">Diproses</span>
                        </div>
                    </div>

                    <!-- Arrow Down -->
                    <div class="flex justify-center">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>

                    <!-- Step 6: Distribusi oleh Pegawai -->
                    <div class="flex flex-col items-center">
                        <div class="bg-teal-500 text-white rounded-lg p-6 shadow-lg w-64 text-center">
                            <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 class="font-bold text-lg">Distribusi</h3>
                            <p class="text-sm mt-1">oleh Pegawai</p>
                        </div>
                        <div class="mt-2">
                            <span class="bg-teal-200 text-teal-700 px-3 py-1 rounded-full text-xs font-semibold">Selesai</span>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Status Legend -->
        <div class="mt-12 bg-gray-50 rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-700">Daftar Status Approval</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-gray-400 rounded-full"></div>
                    <span class="text-sm text-gray-700">Draft</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-blue-400 rounded-full"></div>
                    <span class="text-sm text-gray-700">Diajukan</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                    <span class="text-sm text-gray-700">Diketahui Unit</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-yellow-400 rounded-full"></div>
                    <span class="text-sm text-gray-700">Diketahui TU</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-purple-400 rounded-full"></div>
                    <span class="text-sm text-gray-700">Disetujui Pimpinan</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-red-400 rounded-full"></div>
                    <span class="text-sm text-gray-700">Ditolak</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-indigo-400 rounded-full"></div>
                    <span class="text-sm text-gray-700">Didisposisikan</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-orange-400 rounded-full"></div>
                    <span class="text-sm text-gray-700">Diproses</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-teal-400 rounded-full"></div>
                    <span class="text-sm text-gray-700">Selesai</span>
                </div>
            </div>
        </div>

        <!-- Back Button -->
        <div class="mt-8">
            <a href="{{ route('transaction.approval.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Daftar Approval
            </a>
        </div>
    </div>
</div>
@endsection





