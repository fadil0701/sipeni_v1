@extends('layouts.app')

@section('content')
@php
    $checkedPermissionIds = $role->permissions->pluck('id')->map(fn ($id) => (int) $id)->all();
    $isSystemRole = \App\Support\Admin\SystemRole::isSystemRole($role);
@endphp
<div class="mx-auto max-w-5xl">
    @include('admin.partials.page-header', [
        'title' => 'Edit Role: '.$role->display_name,
        'subtitle' => 'Ubah informasi role dan permission matrix.',
        'backUrl' => route('admin.roles.index'),
        'backLabel' => 'Kembali ke daftar role',
    ])


    @if($isSystemRole)
        <p class="mb-4 rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-xs text-slate-600">
            <span class="font-semibold text-slate-800">System Role</span> — role inti dilindungi dari penghapusan.
        </p>
    @endif

    <form action="{{ route('admin.roles.update', $role->id) }}" method="POST" class="space-y-4" id="unified-role-form">
        @csrf
        @method('PUT')

        <div class="rounded-lg border border-slate-200 bg-white p-5 space-y-5">
            @include('admin.roles.partials.role-info-fields', ['role' => $role])

            <section>
                @include('admin.roles.partials.permission-help')
                @include('admin.roles.partials.permission-matrix', [
                    'groupedMatrix' => $groupedMatrix,
                    'simplifiedMatrix' => $simplifiedMatrix,
                    'checkedPermissionIds' => $checkedPermissionIds,
                    'canDelegateAllPermissions' => $canDelegateAllPermissions,
                ])
            </section>
        </div>

        <x-admin.action-bar :cancel-url="route('admin.roles.index')" submit-label="Simpan Perubahan">
            <a href="{{ route('admin.roles.create', ['clone_from_role_id' => $role->id]) }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Clone</a>
            <a href="{{ route('admin.roles.show', $role->id) }}" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-900">Detail</a>
        </x-admin.action-bar>
    </form>

    <p class="mt-4 text-[11px] text-slate-400">Konfigurasi workflow per status dokumen akan tersedia pada pembaruan berikutnya.</p>
</div>

@include('admin.roles.partials.permission-matrix-scripts')
@endsection
