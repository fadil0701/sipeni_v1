@props([
    'title' => '',
    'subtitle' => '',
    'backRoute' => '',
    'breadcrumbs' => [],
])

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        @if(count($breadcrumbs) > 0)
        <nav aria-label="breadcrumb" class="mb-2">
            <ol class="breadcrumb mb-0">
                @foreach($breadcrumbs as $crumb)
                <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
                    @if($loop->last)
                    {{ $crumb }}
                    @else
                    <a href="{{ $crumb['url'] ?? '#' }}">{{ $crumb['label'] }}</a>
                    @endif
                </li>
                @endforeach
            </ol>
        </nav>
        @endif
        <h4 class="mb-1 fw-bold text-dark">{{ $title }}</h4>
        @if($subtitle)
        <p class="text-muted mb-0">{{ $subtitle }}</p>
        @endif
    </div>
    @if($backRoute)
    <a href="{{ $backRoute }}" class="btn btn-outline-secondary rounded-3">
        <i class="bi bi-arrow-left me-2"></i>
        Kembali
    </a>
    @endif
</div>