@props([
    'backRoute' => '',
    'submitLabel' => 'Simpan',
    'submitIcon' => 'check-lg',
    'showSubmit' => true,
    'showDraft' => false,
    'draftRoute' => '',
    'secondaryLabel' => 'Batal',
    'secondaryRoute' => '',
    'additionalButtons' => '',
])

<div class="d-flex justify-content-between align-items-center border-top pt-4 mt-4">
    <div>
        @if($secondaryRoute)
        <a href="{{ $secondaryRoute }}" class="btn btn-outline-secondary rounded-3 px-4">
            <i class="bi bi-arrow-left me-2"></i>
            {{ $secondaryLabel }}
        </a>
        @endif
    </div>
    <div class="d-flex gap-2">
        @if($showDraft && $draftRoute)
        <form action="{{ $draftRoute }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-primary rounded-3 px-4">
                <i class="bi bi-file-earmark me-2"></i>
                Simpan Draft
            </button>
        </form>
        @endif
        @if($showSubmit)
        <button type="submit" class="btn btn-primary rounded-3 px-4">
            <i class="bi bi-{{ $submitIcon }} me-2"></i>
            {{ $submitLabel }}
        </button>
        @endif
        {!! $additionalButtons !!}
    </div>
</div>