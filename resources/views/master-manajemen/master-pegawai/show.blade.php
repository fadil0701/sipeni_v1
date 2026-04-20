@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('master-manajemen.master-pegawai.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar Pegawai
    </a>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200 flex justify-between items-center">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Detail Master Pegawai</h2>
            <p class="text-sm text-gray-600 mt-1">NIP: <span class="font-semibold">{{ $pegawai->nip_pegawai }}</span></p>
        </div>
        <a 
            href="{{ route('master-manajemen.master-pegawai.edit', $pegawai->id) }}" 
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
            <!-- Informasi Pegawai -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Pegawai</h3>
                <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">NIP</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $pegawai->nip_pegawai }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Nama Pegawai</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $pegawai->nama_pegawai }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Unit Kerja</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $pegawai->unitKerja->nama_unit_kerja ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Jabatan</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $pegawai->jabatan->nama_jabatan ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Email Pegawai</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $pegawai->email_pegawai ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">No. Telepon</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $pegawai->no_telp ?? '-' }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Informasi User -->
            @if($pegawai->user)
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi User</h3>
                <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Email User</dt>
                        <dd class="text-sm font-semibold text-gray-900">
                            <a href="{{ route('admin.users.show', $pegawai->user->id) }}" class="text-blue-600 hover:text-blue-900">
                                {{ $pegawai->user->email }}
                            </a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Nama User</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $pegawai->user->name }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 mb-1">Roles</dt>
                        <dd class="text-sm font-semibold text-gray-900">
                            @if($pegawai->user->roles->count() > 0)
                                <div class="flex flex-wrap gap-2 mt-2">
                                    @foreach($pegawai->user->roles as $role)
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                            {{ $role->display_name }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-gray-500">Belum ada role</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
            @else
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi User</h3>
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-yellow-800">
                                Pegawai ini belum memiliki user account. Anda dapat membuat user account saat edit pegawai.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

