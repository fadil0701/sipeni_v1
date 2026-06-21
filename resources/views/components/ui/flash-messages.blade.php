{{-- Flash session: tampil konsisten di seluruh modul (layout utama memanggil komponen ini sekali). --}}
@php
    $dismissible = $dismissible ?? true;
    $toastMirror = config('sipeni.notifications.toast_mirror_flash', false);
@endphp

@if ($toastMirror)
    {{-- Toast mirror aktif: pesan hanya lewat window.Sipeni.toast (hindari duplikat banner + toast). --}}
@else
<div class="sipeni-flash-stack mb-4 space-y-2" data-sipeni-flash-stack>
    @if (session('success'))
        <div class="alert-box alert-success flex items-start justify-between gap-3" role="status">
            <div class="flex items-start gap-2">
                <svg class="mt-0.5 h-5 w-5 shrink-0 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm font-medium">{{ session('success') }}</p>
            </div>
            @if ($dismissible)
                <button type="button" class="text-green-800/70 hover:text-green-900" data-dismiss-flash aria-label="Tutup">&times;</button>
            @endif
        </div>
    @endif

    @if (session('error'))
        <div class="alert-box alert-error flex items-start justify-between gap-3" role="alert">
            <div class="flex items-start gap-2">
                <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm font-medium">{{ session('error') }}</p>
            </div>
            @if ($dismissible)
                <button type="button" class="text-red-800/70 hover:text-red-900" data-dismiss-flash aria-label="Tutup">&times;</button>
            @endif
        </div>
    @endif

    @if (session('warning'))
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 flex items-start justify-between gap-3" role="alert">
            <div class="flex items-start gap-2">
                <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <p class="font-medium">{{ session('warning') }}</p>
            </div>
            @if ($dismissible)
                <button type="button" class="text-amber-900/70 hover:text-amber-950" data-dismiss-flash aria-label="Tutup">&times;</button>
            @endif
        </div>
    @endif

    @if (session('info'))
        <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900 flex items-start justify-between gap-3" role="status">
            <div class="flex items-start gap-2">
                <svg class="mt-0.5 h-5 w-5 shrink-0 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="font-medium">{{ session('info') }}</p>
            </div>
            @if ($dismissible)
                <button type="button" class="text-blue-900/70 hover:text-blue-950" data-dismiss-flash aria-label="Tutup">&times;</button>
            @endif
        </div>
    @endif

    @if (session('status'))
        <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-800 flex items-start justify-between gap-3" role="status">
            <p class="font-medium">{{ session('status') }}</p>
            @if ($dismissible)
                <button type="button" class="text-gray-600 hover:text-gray-900" data-dismiss-flash aria-label="Tutup">&times;</button>
            @endif
        </div>
    @endif
</div>
@endif
