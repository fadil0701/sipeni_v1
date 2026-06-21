@extends('layouts.app')

@section('content')
<div class="w-full">
    @include('admin.partials.page-header', [
        'title' => 'User & Account Directory',
        'subtitle' => 'Kelola akun login dan penugasan role.',
        'actionUrl' => route('admin.users.create'),
        'actionLabel' => 'Tambah User',
    ])


    @include('admin.partials.summary-cards', [
        'cards' => [
            [
                'label' => 'Total User',
                'value' => number_format($summary['total_users']),
            ],
            [
                'label' => 'Perlu Perhatian (tanpa role)',
                'value' => number_format($summary['users_without_roles']),
                'valueClass' => $summary['users_without_roles'] > 0 ? 'text-amber-600' : 'text-slate-900',
                'href' => route('admin.users.index', ['needs_attention' => 1]),
                'link' => 'Tampilkan saja yang belum punya role',
            ],
        ],
    ])

    <form method="GET" action="{{ route('admin.users.index') }}" class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-end">
        <div class="flex-1">
            <input
                type="search"
                name="search"
                value="{{ request('search') }}"
                placeholder="Cari nama atau email..."
                class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
        </div>
        <div class="w-full sm:w-48">
            <select name="role_id" class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua role</option>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}" @selected((string) request('role_id') === (string) $role->id)>{{ $role->display_name }}</option>
                @endforeach
            </select>
        </div>
        @if(request()->boolean('needs_attention'))
            <input type="hidden" name="needs_attention" value="1">
        @endif
        <button type="submit" class="inline-flex items-center justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Filter</button>
        @if(request()->hasAny(['search', 'role_id', 'needs_attention']))
            <a href="{{ route('admin.users.index') }}" class="rounded-md px-3 py-2 text-sm text-gray-600 hover:text-gray-900">Reset</a>
        @endif
    </form>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Nama / Email</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Pegawai</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Role</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($users as $user)
                        @php
                            $pegawai = $user->pegawai;
                            $isActive = (bool) ($user->is_active ?? true);
                        @endphp
                        <tr class="transition-colors hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900">{{ $user->name }}</p>
                                <p class="text-xs text-gray-500">{{ $user->email }}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                @if($pegawai)
                                    <span class="text-xs">{{ $pegawai->nama_pegawai }}</span>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1">
                                    @forelse($user->roles as $r)
                                        <span class="rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-700">{{ $r->display_name }}</span>
                                    @empty
                                        <span class="text-xs text-amber-600">Belum ada role</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @if($isActive)
                                    <span class="text-xs font-medium text-green-700">Aktif</span>
                                @else
                                    <span class="text-xs font-medium text-gray-500">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                @include('admin.partials.table-actions', [
                                    'editUrl' => route('admin.users.edit', $user->id),
                                    'showUrl' => route('admin.users.show', $user->id),
                                    'deleteForm' => $user->id !== auth()->id() ? route('admin.users.destroy', $user->id) : false,
                                    'deleteConfirm' => 'Hapus user ini?',
                                ])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500">Tidak ada user.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($users->hasPages())
        <div class="mt-4">{{ $users->links() }}</div>
    @endif
</div>
@endsection
