@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('master-manajemen.master-jabatan.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar Jabatan
    </a>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200 flex justify-between items-center">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Detail Master Jabatan</h2>
            <p class="text-sm text-gray-600 mt-1">Urutan: <span class="font-semibold">{{ $jabatan->urutan }}</span></p>
        </div>
        <a 
            href="{{ route('master-manajemen.master-jabatan.edit', $jabatan->id_jabatan) }}" 
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
        >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Edit
        </a>
    </div>
    
    <div class="p-6">
        <div class="grid grid-cols-1 gap-6">
            <!-- Informasi Jabatan -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Jabatan</h3>
                <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Nama Jabatan</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $jabatan->nama_jabatan }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Urutan</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $jabatan->urutan }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Role User</dt>
                        <dd class="text-sm font-semibold text-gray-900">
                            @if($jabatan->role)
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                    {{ $jabatan->role->display_name }} ({{ $jabatan->role->name }})
                                </span>
                            @else
                                <span class="text-gray-500">-</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Jumlah Pegawai</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $jabatan->pegawai->count() }} pegawai</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 mb-1">Deskripsi</dt>
                        <dd class="text-sm text-gray-900">{{ $jabatan->deskripsi ?? '-' }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Daftar Pegawai dengan Jabatan Ini -->
            @if($jabatan->pegawai->count() > 0)
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Pegawai dengan Jabatan Ini</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIP</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Pegawai</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Kerja</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($jabatan->pegawai as $pegawai)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $pegawai->nip_pegawai }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $pegawai->nama_pegawai }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $pegawai->unitKerja->nama_unit_kerja ?? '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $pegawai->email_pegawai ?? '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('master-manajemen.master-pegawai.show', $pegawai->id) }}" class="text-blue-600 hover:text-blue-900 transition-colors">Detail</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection






