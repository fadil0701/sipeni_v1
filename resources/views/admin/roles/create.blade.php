@extends('layouts.app')

@section('content')
@php $checkedPermissionIds = []; @endphp
<div class="mx-auto max-w-5xl">
    @include('admin.partials.page-header', [
        'title' => 'Tambah Role',
        'subtitle' => 'Template hak akses; assign ke user lewat User Directory.',
        'backUrl' => route('admin.roles.index'),
        'backLabel' => 'Kembali ke daftar role',
    ])


    <form action="{{ route('admin.roles.store') }}" method="POST" class="space-y-4">
        @csrf

        <div class="rounded-lg border border-slate-200 bg-white p-5 space-y-5">
            @include('admin.roles.partials.role-info-fields')

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

        <x-admin.action-bar :cancel-url="route('admin.roles.index')" submit-label="Simpan Role" />
    </form>
</div>

@include('admin.roles.partials.permission-matrix-scripts')
@endsection
