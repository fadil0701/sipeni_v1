@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <h1 class="text-xl font-bold text-slate-900">Workflow Permissions</h1>
    <p class="mt-2 text-sm text-slate-600">
        Halaman ini tidak lagi digunakan. Konfigurasi hak akses role dilakukan di halaman edit role.
    </p>
    @isset($role)
    <div class="mt-4 flex flex-wrap gap-2">
        <a href="{{ route('admin.roles.edit', $role->id) }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
            Buka Edit Role
        </a>
        <a href="{{ route('admin.roles.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
            Kembali ke Daftar Role
        </a>
    </div>
    @endisset
</div>
@endsection
