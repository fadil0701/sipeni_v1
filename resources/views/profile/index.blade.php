@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-slate-900">Profil Saya</h1>
        <p class="mt-1 text-sm text-slate-500">Kelola informasi akun dan keamanan</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h2 class="text-lg font-semibold text-slate-800">Informasi Akun</h2>
                </div>
                <div class="p-6">
                    <form action="{{ route('profile.update') }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                @error('name')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                @error('email')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="username" class="block text-sm font-medium text-slate-700 mb-1">Username</label>
                                <input type="text" value="{{ $user->username }}" disabled
                                    class="w-full rounded-lg border border-gray-100 bg-gray-50 px-3 py-2.5 text-sm text-slate-500 cursor-not-allowed">
                                <p class="mt-1 text-xs text-slate-400">Username tidak dapat diubah</p>
                            </div>

                            @if($user->pegawai)
                            <div>
                                <label for="nip" class="block text-sm font-medium text-slate-700 mb-1">NIP</label>
                                <input type="text" value="{{ $user->pegawai->nip ?? '-' }}" disabled
                                    class="w-full rounded-lg border border-gray-100 bg-gray-50 px-3 py-2.5 text-sm text-slate-500 font-mono cursor-not-allowed">
                            </div>
                            @endif

                            @if($user->pegawai?->nama_jabatan)
                            <div>
                                <label for="jabatan" class="block text-sm font-medium text-slate-700 mb-1">Jabatan</label>
                                <input type="text" value="{{ $user->pegawai->nama_jabatan }}" disabled
                                    class="w-full rounded-lg border border-gray-100 bg-gray-50 px-3 py-2.5 text-sm text-slate-500 cursor-not-allowed">
                            </div>
                            @endif

                            @if($user->pegawai && $user->pegawai->unitKerja)
                            <div>
                                <label for="unit_kerja" class="block text-sm font-medium text-slate-700 mb-1">Unit Kerja</label>
                                <input type="text" value="{{ $user->pegawai->unitKerja->nama_unit ?? '-' }}" disabled
                                    class="w-full rounded-lg border border-gray-100 bg-gray-50 px-3 py-2.5 text-sm text-slate-500 cursor-not-allowed">
                            </div>
                            @endif
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-blue-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-800 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @if(!empty($twoFactorSetup) || $user->two_factor_confirmed_at)
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h2 class="text-lg font-semibold text-slate-800">Two-Factor Authentication (2FA)</h2>
                </div>
                <div class="p-6 space-y-4">
                    @if($user->two_factor_confirmed_at)
                        <p class="text-sm text-green-700">2FA aktif sejak {{ $user->two_factor_confirmed_at->format('d M Y H:i') }}.</p>
                        <form action="{{ route('profile.two-factor.disable') }}" method="POST" class="space-y-3">
                            @csrf
                            @method('DELETE')
                            <div>
                                <label for="disable_2fa_password" class="block text-sm font-medium text-slate-700 mb-1">Password untuk nonaktifkan</label>
                                <input type="password" name="password" id="disable_2fa_password" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                            </div>
                            <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Nonaktifkan 2FA</button>
                        </form>
                    @elseif(!empty($twoFactorSetup))
                        <p class="text-sm text-slate-600">Akun Anda wajib menggunakan autentikasi dua faktor. Pindai kode QR berikut dengan aplikasi authenticator (Google Authenticator, Authy, dll).</p>
                        <div class="inline-block rounded-lg border border-slate-200 bg-white p-3">
                            {!! $twoFactorSetup['qr_svg'] !!}
                        </div>
                        <details class="mt-2">
                            <summary class="cursor-pointer text-sm text-slate-600 hover:text-slate-800">Tidak bisa scan? Masukkan kode manual</summary>
                            <p class="mt-2 font-mono text-sm bg-slate-100 p-3 rounded break-all select-all">{{ $twoFactorSetup['secret'] }}</p>
                        </details>
                        <form action="{{ route('profile.two-factor.confirm') }}" method="POST" class="space-y-3">
                            @csrf
                            <div>
                                <label for="two_factor_code" class="block text-sm font-medium text-slate-700 mb-1">Kode OTP</label>
                                <input type="text" name="code" id="two_factor_code" required inputmode="numeric" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                            </div>
                            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Aktifkan 2FA</button>
                        </form>
                    @endif
                </div>
            </div>
            @endif

            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h2 class="text-lg font-semibold text-slate-800">Ganti Password</h2>
                </div>
                <div class="p-6">
                    <form action="{{ route('profile.password') }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label for="current_password" class="block text-sm font-medium text-slate-700 mb-1">Password Lama</label>
                            <input type="password" name="current_password" id="current_password"
                                class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            @error('current_password')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password Baru</label>
                                <input type="password" name="password" id="password"
                                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                <p class="mt-1 text-xs text-slate-400">{{ \App\Support\SipeniPassword::requirementHint() }}</p>
                                @error('password')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">Konfirmasi Password</label>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            </div>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-amber-600 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                </svg>
                                Ubah Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h2 class="text-lg font-semibold text-slate-800">Foto Profil</h2>
                </div>
                <div class="p-6">
                    <div class="flex flex-col items-center">
                        <div class="mb-4">
                            @if($user->avatar)
                                <img src="{{ Storage::url($user->avatar) }}" alt="Avatar" class="h-32 w-32 rounded-full object-cover ring-4 ring-gray-100">
                            @else
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=1e3a8a&color=fff&size=128" 
                                    alt="Avatar" class="h-32 w-32 rounded-full ring-4 ring-gray-100">
                            @endif
                        </div>
                        
                        <form action="{{ route('profile.avatar') }}" method="POST" enctype="multipart/form-data" class="w-full space-y-3">
                            @csrf
                            <input type="file" name="avatar" id="avatar" accept="image/*" 
                                class="block w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <p class="text-xs text-slate-400 text-center">JPG, PNG, GIF. Max 2MB</p>
                            @error('avatar')
                                <p class="text-xs text-red-600 text-center">{{ $message }}</p>
                            @enderror

                            <div class="flex gap-2">
                                <button type="submit" class="flex-1 rounded-lg bg-blue-900 px-3 py-2 text-sm font-medium text-white hover:bg-blue-800 transition-colors">
                                    Upload
                                </button>
                                @if($user->avatar)
                                    <button type="submit" formaction="{{ route('profile.avatar.remove') }}" formmethod="POST"
                                        class="flex-1 rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-slate-600 hover:bg-gray-50 transition-colors">
                                        Hapus
                                    </button>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h2 class="text-lg font-semibold text-slate-800">Status Akun</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-500">Status</span>
                        @if($user->is_active)
                            <span class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                Aktif
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">
                                Nonaktif
                            </span>
                        @endif
                    </div>

                    @if($user->last_login)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-500">Login Terakhir</span>
                        <span class="text-sm text-slate-700">{{ $user->last_login->format('d M Y H:i') }}</span>
                    </div>
                    @endif

                    @if($user->password_changed_at)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-500">Password Diubah</span>
                        <span class="text-sm text-slate-700">{{ $user->password_changed_at->format('d M Y') }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h2 class="text-lg font-semibold text-slate-800">Role & Akses</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wide mb-2">Role</p>
                        <div class="flex flex-wrap gap-1.5">
                            @forelse($roleInfo['roles'] as $role)
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium 
                                    {{ $role['group'] === 'SYSTEM' ? 'bg-[#1E3A8A] text-white' : 
                                       ($role['group'] === 'STRUKTURAL' ? 'bg-violet-100 text-violet-800' : 
                                       ($role['group'] === 'MANAJERIAL' ? 'bg-green-100 text-green-900' : 
                                       ($role['group'] === 'OPERATOR' ? 'bg-amber-100 text-amber-950' : 
                                       'bg-slate-200 text-slate-700'))) }}">
                                    {{ $role['display_name'] }}
                                    @if($role['level'])
                                        <span class="ml-1 text-[10px] opacity-75">{{ $role['level'] }}</span>
                                    @endif
                                </span>
                            @empty
                                <span class="text-sm text-slate-400">Tidak ada role</span>
                            @endforelse
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                        <span class="text-sm text-slate-500">Approval Authority</span>
                        @if($roleInfo['has_approval'])
                            <span class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Ya
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-500">
                                Tidak
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                        <span class="text-sm text-slate-500">Monitoring Authority</span>
                        @if($roleInfo['has_monitoring'])
                            <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Ya
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-500">
                                Tidak
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h2 class="text-lg font-semibold text-slate-800">Aktivitas Login</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-500">Total Login</span>
                        <span class="text-sm font-medium text-slate-900">{{ $loginCount }}x</span>
                    </div>

                    @if($lastAuditActivity)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-500">Aktivitas Terakhir</span>
                        <span class="text-sm text-slate-700">{{ $lastAuditActivity->created_at->format('d M Y H:i') }}</span>
                    </div>
                    <div class="pt-2 border-t border-gray-100">
                        <p class="text-xs text-slate-400">Aksi terakhir: <span class="text-slate-600 font-medium">{{ $lastAuditActivity->action }}</span></p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection