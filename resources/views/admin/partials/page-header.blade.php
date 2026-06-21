<div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
    <div>
        @if(!empty($backUrl))
            <a href="{{ $backUrl }}" class="mb-2 inline-flex items-center text-sm text-slate-600 hover:text-slate-900">
                <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                {{ $backLabel ?? 'Kembali' }}
            </a>
        @endif
        <h1 class="text-xl font-semibold text-slate-900">{{ $title }}</h1>
        @if(!empty($subtitle))
            <p class="mt-1 text-sm text-slate-500">{{ $subtitle }}</p>
        @endif
    </div>
    @if(!empty($actionUrl) && !empty($actionLabel))
        <a href="{{ $actionUrl }}" class="inline-flex shrink-0 items-center justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            {{ $actionLabel }}
        </a>
    @endif
</div>
