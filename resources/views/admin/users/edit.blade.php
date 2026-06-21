@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl">
    @include('admin.partials.page-header', [
        'title' => 'Edit User',
        'subtitle' => $user->email,
        'backUrl' => route('admin.users.index'),
        'backLabel' => 'Kembali ke daftar user',
    ])


    <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')

        <div class="rounded-lg border border-slate-200 bg-white p-5 space-y-5">
            <x-admin.form-section title="Data akun">
                <div class="mb-4">
                    @include('admin.partials.pegawai-link', [
                        'pegawais' => $pegawais,
                        'selectedPegawaiId' => old('pegawai_id', $user->pegawai?->id),
                    ])
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Nama <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" required value="{{ old('name', $user->name) }}"
                            class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm @error('name') border-red-500 @enderror">
                        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email <span class="text-red-500">*</span></label>
                        <input type="email" id="email" name="email" required value="{{ old('email', $user->email) }}"
                            class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm @error('email') border-red-500 @enderror">
                        @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="password" class="mb-1 block text-sm font-medium text-slate-700">Password baru</label>
                        <input type="password" id="password" name="password" placeholder="Kosongkan jika tidak diubah"
                            class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm @error('password') border-red-500 @enderror">
                        <p class="mt-1 text-xs text-slate-500">{{ \App\Support\SipeniPassword::requirementHint() }}</p>
                        @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="mb-1 block text-sm font-medium text-slate-700">Konfirmasi password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                            class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-slate-300 text-slate-800"
                                @checked(old('is_active', $user->is_active ?? true))>
                            Akun aktif
                        </label>
                    </div>
                </div>
            </x-admin.form-section>

            <x-admin.form-section title="Role">
                @include('admin.partials.role-picker', [
                    'roles' => $roles,
                    'selectedRoleIds' => array_map('intval', (array) old('role_ids', $user->roles->pluck('id')->all())),
                ])
            </x-admin.form-section>
        </div>

        <x-admin.action-bar :cancel-url="route('admin.users.index')" submit-label="Simpan Perubahan" />
    </form>
</div>

@push('scripts')
<script>
function fillFromPegawai() {
    const select = document.getElementById('pegawai_id');
    if (!select) return;
    const opt = select.options[select.selectedIndex];
    if (!opt?.value) return;
    document.getElementById('name').value = opt.getAttribute('data-nama') || '';
    const email = opt.getAttribute('data-email');
    if (email) document.getElementById('email').value = email;
}
</script>
@endpush
@endsection
