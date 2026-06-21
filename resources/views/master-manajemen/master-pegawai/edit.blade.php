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
    <div class="px-6 py-5 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Edit Master Pegawai</h2>
    </div>
    
    <form action="{{ route('master-manajemen.master-pegawai.update', $pegawai->id) }}" method="POST" class="p-6" id="formPegawai">
        @csrf
        @method('PUT')
        
        <div class="space-y-6">
            <!-- Informasi Pegawai -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Pegawai</h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="nip_pegawai" class="block text-sm font-medium text-gray-700 mb-2">
                            NIP <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="nip_pegawai" 
                            name="nip_pegawai" 
                            required
                            value="{{ old('nip_pegawai', $pegawai->nip_pegawai) }}"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('nip_pegawai') border-red-500 @enderror"
                            placeholder="Masukkan NIP"
                        >
                        @error('nip_pegawai')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="nama_pegawai" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Pegawai <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="nama_pegawai" 
                            name="nama_pegawai" 
                            required
                            value="{{ old('nama_pegawai', $pegawai->nama_pegawai) }}"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('nama_pegawai') border-red-500 @enderror"
                            placeholder="Masukkan nama pegawai"
                        >
                        @error('nama_pegawai')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="id_unit_kerja" class="block text-sm font-medium text-gray-700 mb-2">
                            Unit Kerja <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="id_unit_kerja" 
                            name="id_unit_kerja" 
                            required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_unit_kerja') border-red-500 @enderror"
                        >
                            <option value="">Pilih Unit Kerja</option>
                            @foreach($unitKerjas as $unitKerja)
                                <option value="{{ $unitKerja->id_unit_kerja }}" {{ old('id_unit_kerja', $pegawai->id_unit_kerja) == $unitKerja->id_unit_kerja ? 'selected' : '' }}>
                                    {{ $unitKerja->nama_unit_kerja }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_unit_kerja')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="id_jabatan" class="block text-sm font-medium text-gray-700 mb-2">
                            Jabatan <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="id_jabatan" 
                            name="id_jabatan" 
                            required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('id_jabatan') border-red-500 @enderror"
                        >
                            <option value="">Pilih Jabatan</option>
                            @foreach($jabatans as $jabatan)
                                <option value="{{ $jabatan->id_jabatan }}" {{ old('id_jabatan', $pegawai->id_jabatan) == $jabatan->id_jabatan ? 'selected' : '' }}>
                                    {{ $jabatan->nama_jabatan }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_jabatan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email_pegawai" class="block text-sm font-medium text-gray-700 mb-2">Email Pegawai</label>
                        <input 
                            type="email" 
                            id="email_pegawai" 
                            name="email_pegawai" 
                            value="{{ old('email_pegawai', $pegawai->email_pegawai) }}"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('email_pegawai') border-red-500 @enderror"
                            placeholder="email@example.com"
                        >
                        @error('email_pegawai')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="no_telp" class="block text-sm font-medium text-gray-700 mb-2">No. Telepon</label>
                        <input 
                            type="text" 
                            id="no_telp" 
                            name="no_telp" 
                            value="{{ old('no_telp', $pegawai->no_telp) }}"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('no_telp') border-red-500 @enderror"
                            placeholder="081234567890"
                        >
                        @error('no_telp')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Integrasi User -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Integrasi User (Opsional)</h3>
                <p class="mb-3 text-xs text-gray-600">Hubungkan atau perbarui akun login pegawai. <strong>Pengelolaan role</strong> dilakukan melalui User &amp; Account Directory.</p>
                @if($pegawai->user)
                    <p class="mb-3 text-sm">
                        <a href="{{ route('admin.users.edit', $pegawai->user->id) }}" class="font-medium text-blue-600 hover:text-blue-900">Kelola akun &amp; role pegawai ini</a>
                    </p>
                @endif
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <div class="space-y-4">
                        <div>
                            <label class="flex items-center">
                                <input 
                                    type="radio" 
                                    name="user_option" 
                                    value="none" 
                                    {{ old('user_option', $pegawai->user_id ? 'existing' : 'none') === 'none' ? 'checked' : '' }}
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                >
                                <span class="ml-2 text-sm text-gray-700">Tidak membuat user</span>
                            </label>
                        </div>

                        <div>
                            <label class="flex items-center">
                                <input 
                                    type="radio" 
                                    name="user_option" 
                                    value="existing" 
                                    {{ old('user_option', $pegawai->user_id ? 'existing' : 'none') === 'existing' ? 'checked' : '' }}
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                >
                                <span class="ml-2 text-sm text-gray-700">Gunakan user yang sudah ada</span>
                            </label>
                            <div id="existingUserOption" style="display: none;" class="mt-2 ml-6">
                                <select 
                                    id="user_id" 
                                    name="user_id" 
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                >
                                    <option value="">Pilih User</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('user_id', $pegawai->user_id) == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="flex items-center">
                                <input 
                                    type="radio" 
                                    name="user_option" 
                                    value="new" 
                                    {{ old('user_option') === 'new' ? 'checked' : '' }}
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                >
                                <span class="ml-2 text-sm text-gray-700">Buat user baru</span>
                            </label>
                            <div id="newUserOption" style="display: none;" class="mt-2 ml-6 space-y-4">
                                <input type="hidden" name="create_user" id="create_user" value="0">
                                
                                <div>
                                    <label for="user_name" class="block text-sm font-medium text-gray-700 mb-1">Nama User</label>
                                    <input 
                                        type="text" 
                                        id="user_name" 
                                        name="user_name" 
                                        value="{{ old('user_name', $pegawai->user->name ?? '') }}"
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        placeholder="Nama untuk login"
                                    >
                                </div>

                                <div>
                                    <label for="user_email" class="block text-sm font-medium text-gray-700 mb-1">Email User</label>
                                    <input 
                                        type="email" 
                                        id="user_email" 
                                        name="user_email" 
                                        value="{{ old('user_email', $pegawai->user->email ?? '') }}"
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        placeholder="email@example.com"
                                    >
                                </div>

                                <div>
                                    <label for="user_password" class="block text-sm font-medium text-gray-700 mb-1">Password (kosongkan jika tidak ingin mengubah)</label>
                                    <input 
                                        type="password" 
                                        id="user_password" 
                                        name="user_password" 
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        placeholder="Minimal 8 karakter"
                                    >
                                </div>

                                <div>
                                    <label for="user_password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                                    <input 
                                        type="password" 
                                        id="user_password_confirmation" 
                                        name="user_password_confirmation" 
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        placeholder="Ulangi password"
                                    >
                                </div>

                            </div>
                        </div>

                        <div class="rounded-md border border-blue-200 bg-blue-50 p-3 text-sm text-blue-900" id="userRoleNotice" style="display: none;">
                            <p class="font-medium">Pengelolaan role dilakukan melalui User &amp; Account Directory.</p>
                            <p class="mt-1 text-xs text-blue-800">Setelah akun dibuat atau dihubungkan, buka <a href="{{ route('admin.users.index') }}" class="font-semibold underline">User &amp; Account Directory</a> untuk menetapkan role.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 flex justify-end space-x-3 border-t border-gray-200 pt-6">
            <a 
                href="{{ route('master-manajemen.master-pegawai.index') }}" 
                class="px-5 py-2.5 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
                Batal
            </a>
            <button 
                type="submit" 
                class="px-5 py-2.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function refreshUserRoleNotice() {
    const userOption = document.querySelector('input[name="user_option"]:checked')?.value || 'none';
    const section = document.getElementById('userRoleNotice');
    if (section) {
        section.style.display = (userOption === 'existing' || userOption === 'new') ? 'block' : 'none';
    }
}

function toggleUserOptions() {
    const userOption = document.querySelector('input[name="user_option"]:checked').value;
    const existingUserOption = document.getElementById('existingUserOption');
    const newUserOption = document.getElementById('newUserOption');
    const createUserInput = document.getElementById('create_user');
    const userIdInput = document.getElementById('user_id');

    if (userOption === 'existing') {
        existingUserOption.style.display = 'block';
        newUserOption.style.display = 'none';
        createUserInput.value = '0';
        userIdInput.required = true;
        document.getElementById('user_name').required = false;
        document.getElementById('user_email').required = false;
        document.getElementById('user_password').required = false;
    } else if (userOption === 'new') {
        existingUserOption.style.display = 'none';
        newUserOption.style.display = 'block';
        createUserInput.value = '1';
        userIdInput.required = false;
        document.getElementById('user_name').required = true;
        document.getElementById('user_email').required = true;
        document.getElementById('user_password').required = {{ $pegawai->user_id ? 'false' : 'true' }};
    } else {
        existingUserOption.style.display = 'none';
        newUserOption.style.display = 'none';
        createUserInput.value = '0';
        userIdInput.required = false;
        if (userIdInput) userIdInput.value = '';
        document.getElementById('user_name').required = false;
        document.getElementById('user_email').required = false;
        document.getElementById('user_password').required = false;
    }
    refreshUserRoleNotice();
}

document.querySelectorAll('input[name="user_option"]').forEach(el => {
    el.addEventListener('change', toggleUserOptions);
});

toggleUserOptions();
</script>
@endpush
@endsection


