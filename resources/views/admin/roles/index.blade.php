@extends('layouts.app')

@section('content')
<div class="w-full">
    @include('admin.partials.page-header', [
        'title' => 'Role & Workflow Authority',
        'subtitle' => 'Kelola template hak akses (permission) per role.',
        'actionUrl' => route('admin.roles.create'),
        'actionLabel' => 'Tambah Role',
    ])


    @include('admin.partials.summary-cards', [
        'cards' => [
            ['label' => 'Total Role', 'value' => number_format($summary['total_roles'])],
            ['label' => 'Role Dipakai User', 'value' => number_format($summary['roles_in_use'])],
        ],
    ])

    <form method="GET" action="{{ route('admin.roles.index') }}" class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-end">
        <div class="flex-1">
            <input
                type="search"
                name="search"
                value="{{ request('search') }}"
                placeholder="Cari nama role..."
                class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
        </div>
        <button type="submit" class="inline-flex items-center justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Cari</button>
        @if(request()->filled('search'))
            <a href="{{ route('admin.roles.index') }}" class="rounded-md px-3 py-2 text-sm text-gray-600 hover:text-gray-900">Reset</a>
        @endif
    </form>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Nama Role</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Scope</th>
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">Permission</th>
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">User</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($roles as $role)
                        @php $isSystem = \App\Support\Admin\SystemRole::isSystemRole($role); @endphp
                        <tr class="transition-colors hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-medium text-gray-900">{{ $role->display_name }}</span>
                                    @if($isSystem)
                                        <span class="rounded bg-blue-600 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-white">System Role</span>
                                    @endif
                                </div>
                                @if($role->description)
                                    <p class="mt-0.5 line-clamp-1 text-xs text-gray-500">{{ $role->description }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ ($role->level_akses ?? 'unit') === 'pusat' ? 'Pusat' : 'Unit Kerja' }}
                            </td>
                            <td class="px-4 py-3 text-center text-gray-700">{{ $role->permissions_count }}</td>
                            <td class="px-4 py-3 text-center text-gray-700">{{ $role->users_count }}</td>
                            <td class="px-4 py-3">
                                @if($role->is_active ?? true)
                                    <span class="text-xs font-medium text-green-700">Aktif</span>
                                @else
                                    <span class="text-xs font-medium text-gray-500">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                @php
                                    $cloneLink = '<a href="'.e(route('admin.roles.create', ['clone_from_role_id' => $role->id])).'" title="Clone role" aria-label="Clone role" class="inline-flex h-7 items-center justify-center rounded-md border border-gray-300 bg-white px-2.5 text-xs font-medium text-gray-700 transition-colors hover:bg-gray-50">Clone</a>';
                                    $canDelete = ! $isSystem && $role->users_count == 0;
                                @endphp
                                @include('admin.partials.table-actions', [
                                    'editUrl' => route('admin.roles.edit', $role->id),
                                    'showUrl' => route('admin.roles.show', $role->id),
                                    'extraHtml' => $cloneLink,
                                    'deleteForm' => $canDelete ? route('admin.roles.destroy', $role->id) : false,
                                    'deleteConfirm' => 'Hapus role ini?',
                                ])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">Belum ada role.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($roles->hasPages())
        <div class="mt-4">{{ $roles->links() }}</div>
    @endif
</div>
@endsection
