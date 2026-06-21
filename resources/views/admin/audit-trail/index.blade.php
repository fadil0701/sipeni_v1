@extends('layouts.app')

@section('content')
<div class="w-full">
    @include('admin.partials.page-header', [
        'title' => 'Audit Trail',
        'subtitle' => 'Riwayat aktivitas sistem — siapa, kapan, modul, dan perubahan data.',
    ])


    @include('admin.partials.summary-cards', [
        'cards' => [
            ['label' => 'Total Entri', 'value' => number_format($summary['total_entries'])],
            ['label' => 'Hari Ini', 'value' => number_format($summary['today_entries']), 'valueClass' => 'text-blue-600'],
        ],
    ])

    <form method="GET" action="{{ route('admin.audit-trail.index') }}" class="mb-4 grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-6 lg:items-end">
        <div class="lg:col-span-2">
            <input
                type="search"
                name="search"
                value="{{ request('search') }}"
                placeholder="Cari user, aksi, deskripsi..."
                class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
        </div>
        <div>
            <select name="user_id" class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua user</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" @selected((string) request('user_id') === (string) $u->id)>{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <select name="module_key" class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua modul</option>
                @foreach($moduleKeys as $key)
                    <option value="{{ $key }}" @selected(request('module_key') === $key)>{{ $moduleLabels[$key] ?? $key }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <select name="action" class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua aksi</option>
                @foreach($actions as $action)
                    <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="block w-full rounded-md border border-gray-300 px-2 py-2 text-sm" title="Dari tanggal">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="block w-full rounded-md border border-gray-300 px-2 py-2 text-sm" title="Sampai tanggal">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="inline-flex items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Filter</button>
            @if(request()->hasAny(['search', 'user_id', 'module_key', 'action', 'date_from', 'date_to']))
                <a href="{{ route('admin.audit-trail.index') }}" class="rounded-md px-3 py-2 text-sm text-gray-600 hover:text-gray-900">Reset</a>
            @endif
        </div>
    </form>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Waktu</th>
                        <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">User</th>
                        <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Modul</th>
                        <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Aksi</th>
                        <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Entitas</th>
                        <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Deskripsi</th>
                        <th class="px-3 py-2 text-right text-xs font-medium uppercase text-gray-500">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="whitespace-nowrap px-3 py-2 text-xs text-gray-600">{{ $log->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td class="px-3 py-2">
                                <p class="font-medium text-gray-900">{{ $log->user?->name ?? 'Sistem' }}</p>
                                @if($log->user?->email)
                                    <p class="text-xs text-gray-500">{{ $log->user->email }}</p>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-gray-700">{{ $moduleLabels[$log->module_key] ?? $log->module_key ?? '—' }}</td>
                            <td class="px-3 py-2"><span class="rounded bg-gray-100 px-1.5 py-0.5 text-xs font-medium text-gray-800">{{ $log->action }}</span></td>
                            <td class="px-3 py-2 text-xs text-gray-600">{{ $log->entityLabel() }}</td>
                            <td class="max-w-xs truncate px-3 py-2 text-gray-600" title="{{ $log->description }}">{{ $log->description ?? '—' }}</td>
                            <td class="px-3 py-2 text-right">
                                <a href="{{ route('admin.audit-trail.show', $log) }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-500">Belum ada aktivitas tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($logs->hasPages())
        <div class="mt-4">{{ $logs->links() }}</div>
    @endif
</div>
@endsection
