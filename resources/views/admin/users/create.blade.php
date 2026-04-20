@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.users.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar User
    </a>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Tambah User</h2>
    </div>
    
    <form action="{{ route('admin.users.store') }}" method="POST" class="p-6">
        @csrf
        
        <div class="space-y-6">
            <!-- Pilih Pegawai (Opsional) -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <label for="pegawai_id" class="block text-sm font-medium text-blue-900 mb-2">
                    Ambil Data dari Pegawai (Opsional)
                </label>
                <select 
                    id="pegawai_id" 
                    name="pegawai_id" 
                    class="block w-full px-3 py-2 border border-blue-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    onchange="fillFromPegawai()"
                >
                    <option value="">Pilih Pegawai (untuk auto-fill nama & email)</option>
                    @forelse($pegawais as $pegawai)
                        <option value="{{ $pegawai->id }}" data-nama="{{ e($pegawai->nama_pegawai) }}" data-email="{{ e($pegawai->email_pegawai ?? '') }}">
                            {{ $pegawai->nama_pegawai }} ({{ $pegawai->nip_pegawai }}){{ $pegawai->email_pegawai ? ' - ' . $pegawai->email_pegawai : '' }}
                        </option>
                    @empty
                        <option value="" disabled>Tidak ada data pegawai</option>
                    @endforelse
                </select>
                <p class="mt-2 text-xs text-blue-700">
                    Pilih pegawai untuk mengisi otomatis nama dan email. Jika pegawai tidak memiliki email, email harus diisi manual.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nama <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        required
                        value="{{ old('name') }}"
                        placeholder="Masukkan nama user"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('name') border-red-500 @enderror"
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required
                        value="{{ old('email') }}"
                        placeholder="Masukkan email"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('email') border-red-500 @enderror"
                    >
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        placeholder="Minimal 8 karakter"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('password') border-red-500 @enderror"
                    >
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        Konfirmasi Password <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="password" 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        required
                        placeholder="Ulangi password"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    >
                </div>

                <div class="sm:col-span-2">
                    <label for="role_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Role <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="role_id" 
                        name="role_id" 
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('role_id') border-red-500 @enderror"
                    >
                        <option value="">Pilih Role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                {{ $role->display_name }} ({{ $role->name }})
                            </option>
                        @endforeach
                    </select>
                    @error('role_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Menu yang Dapat Diakses -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <label class="block text-sm font-medium text-gray-700">
                        Menu yang Dapat Diakses
                    </label>
                    <label class="flex items-center text-sm text-blue-600 hover:text-blue-800 cursor-pointer font-medium">
                        <input 
                            type="checkbox" 
                            id="select-all-modules"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        >
                        <span class="ml-2">Pilih Semua</span>
                    </label>
                </div>
                
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($modules as $module)
                        <label class="flex items-center p-3 border border-gray-300 rounded-lg hover:bg-white cursor-pointer transition-colors">
                            <input 
                                type="checkbox" 
                                name="modules[]" 
                                value="{{ $module->name }}"
                                {{ in_array($module->name, old('modules', [])) ? 'checked' : '' }}
                                class="module-checkbox h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            >
                            <div class="ml-3 flex-1">
                                <span class="text-sm font-medium text-gray-900">{{ $module->display_name }}</span>
                                @if($module->description)
                                    <p class="text-xs text-gray-500 mt-0.5">{{ Str::limit($module->description, 50) }}</p>
                                @endif
                            </div>
                        </label>
                    @endforeach
                </div>
                <p class="mt-3 text-xs text-gray-500">
                    Pilih menu yang dapat diakses oleh user ini. Permission detail dapat diatur di <strong>Manajemen Role</strong>.
                </p>
            </div>
        </div>

        <div class="mt-8 flex justify-end space-x-3 border-t border-gray-200 pt-6">
            <a 
                href="{{ route('admin.users.index') }}" 
                class="px-5 py-2.5 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
                Batal
            </a>
            <button 
                type="submit" 
                class="px-5 py-2.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
                Simpan
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function fillFromPegawai() {
    const select = document.getElementById('pegawai_id');
    const selectedOption = select.options[select.selectedIndex];
    
    if (selectedOption.value) {
        const nama = selectedOption.getAttribute('data-nama');
        const email = selectedOption.getAttribute('data-email');
        
        document.getElementById('name').value = nama || '';
        if (email) {
            document.getElementById('email').value = email;
        }
    }
}

// Select all modules
document.getElementById('select-all-modules')?.addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.module-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});
</script>
@endpush
@endsection

