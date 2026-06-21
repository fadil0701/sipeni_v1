@extends('layouts.app')

@section('content')
<div class="w-full">
    @include('admin.partials.page-header', [
        'title' => 'Detail Audit Trail',
        'subtitle' => $log->description ?? 'Detail perubahan aktivitas.',
        'backUrl' => route('admin.audit-trail.index'),
        'backLabel' => 'Kembali ke Audit Trail',
    ])

    <x-admin.form-section title="Ringkasan">
        <dl class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
            <div>
                <dt class="text-xs font-medium uppercase text-gray-500">Waktu</dt>
                <dd class="mt-0.5 text-gray-900">{{ $log->created_at?->format('d F Y H:i:s') ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase text-gray-500">User</dt>
                <dd class="mt-0.5 text-gray-900">{{ $log->user?->name ?? 'Sistem' }} @if($log->user?->email)<span class="text-gray-500">({{ $log->user->email }})</span>@endif</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase text-gray-500">Modul</dt>
                <dd class="mt-0.5 text-gray-900">{{ $moduleLabel ?? $log->module_key ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase text-gray-500">Aksi</dt>
                <dd class="mt-0.5"><span class="rounded bg-gray-100 px-2 py-0.5 text-xs font-medium">{{ $log->action }}</span></dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase text-gray-500">Entitas</dt>
                <dd class="mt-0.5 font-mono text-xs text-gray-800">{{ $log->entity_type ?? '—' }} @if($log->entity_id)#{{ $log->entity_id }}@endif</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs font-medium uppercase text-gray-500">Deskripsi</dt>
                <dd class="mt-0.5 text-gray-900">{{ $log->description ?? '—' }}</dd>
            </div>
        </dl>
    </x-admin.form-section>

    <x-admin.form-section title="Request context" class="mt-4">
        <dl class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
            <div class="sm:col-span-2">
                <dt class="text-xs font-medium uppercase text-gray-500">URL</dt>
                <dd class="mt-0.5 break-all font-mono text-xs text-gray-700">{{ $log->request_url ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase text-gray-500">Method</dt>
                <dd class="mt-0.5 text-gray-900">{{ $log->method ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase text-gray-500">IP</dt>
                <dd class="mt-0.5 text-gray-900">{{ $log->ip_address ?? '—' }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs font-medium uppercase text-gray-500">User Agent</dt>
                <dd class="mt-0.5 break-all text-xs text-gray-600">{{ $log->user_agent ?? '—' }}</dd>
            </div>
        </dl>
    </x-admin.form-section>

    @php
        $pretty = fn ($data) => $data ? json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : null;
    @endphp

    <x-admin.form-section title="Nilai sebelum (old)" class="mt-4">
        @if($log->old_values)
            <details class="rounded-md border border-gray-200 bg-gray-50" open>
                <summary class="cursor-pointer px-3 py-2 text-xs font-medium text-gray-600">Tampilkan JSON</summary>
                <pre class="max-h-80 overflow-auto p-3 text-xs text-gray-800">{{ $pretty($log->old_values) }}</pre>
            </details>
        @else
            <p class="text-sm text-gray-500">Tidak ada data sebelum perubahan.</p>
        @endif
    </x-admin.form-section>

    <x-admin.form-section title="Nilai sesudah (new)" class="mt-4">
        @if($log->new_values)
            <details class="rounded-md border border-gray-200 bg-gray-50" open>
                <summary class="cursor-pointer px-3 py-2 text-xs font-medium text-gray-600">Tampilkan JSON</summary>
                <pre class="max-h-80 overflow-auto p-3 text-xs text-gray-800">{{ $pretty($log->new_values) }}</pre>
            </details>
        @else
            <p class="text-sm text-gray-500">Tidak ada data sesudah perubahan.</p>
        @endif
    </x-admin.form-section>

    @if($log->metadata)
        <x-admin.form-section title="Metadata" class="mt-4">
            <details class="rounded-md border border-gray-200 bg-gray-50">
                <summary class="cursor-pointer px-3 py-2 text-xs font-medium text-gray-600">Tampilkan JSON</summary>
                <pre class="max-h-64 overflow-auto p-3 text-xs text-gray-800">{{ $pretty($log->metadata) }}</pre>
            </details>
        </x-admin.form-section>
    @endif
</div>
@endsection
