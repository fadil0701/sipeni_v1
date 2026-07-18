@php
    $editUrl = $editUrl ?? '#';
    $showUrl = $showUrl ?? null;
    $deleteForm = $deleteForm ?? false;
    $deleteConfirm = $deleteConfirm ?? 'Hapus item ini?';
    $extraHtml = $extraHtml ?? '';
@endphp
<div class="flex items-center justify-end gap-1.5" data-skip-action-icons="1">
    <a
        href="{{ $editUrl }}"
        title="Edit"
        aria-label="Edit"
        class="inline-flex h-7 w-7 items-center justify-center rounded-full border border-blue-200 bg-blue-50 text-blue-700 transition-colors hover:bg-blue-100"
    >
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"></path>
        </svg>
        <span class="sr-only">Edit</span>
    </a>
    @if($showUrl)
        <a
            href="{{ $showUrl }}"
            title="Detail"
            aria-label="Detail"
            class="inline-flex h-7 w-7 items-center justify-center rounded-full border border-blue-200 bg-blue-50 text-blue-600 transition-colors hover:bg-blue-100"
        >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7S3.732 16.057 2.458 12z"></path>
            </svg>
            <span class="sr-only">Detail</span>
        </a>
    @endif
    {!! $extraHtml !!}
    @if($deleteForm)
        <form action="{{ $deleteForm }}" method="POST" class="inline" data-confirm="{{ $deleteConfirm }}">
            @csrf
            @method('DELETE')
            <button
                type="submit"
                title="Hapus"
                aria-label="Hapus"
                class="inline-flex h-7 w-7 items-center justify-center rounded-full border border-red-200 bg-red-50 text-red-600 transition-colors hover:bg-red-100"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-1 12a2 2 0 01-2 2H8a2 2 0 01-2-2L5 7"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11v6M14 11v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16"></path>
                </svg>
                <span class="sr-only">Hapus</span>
            </button>
        </form>
    @endif
</div>