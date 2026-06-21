@props([
    'title' => '',
    'icon' => '',
    'size' => 'col-xl-10', // col-xl-8, col-xl-10, col-xl-12
])

<div class="page-enterprise container-fluid px-4 py-4">
    <div class="row justify-content-center">
        <div class="{{ $size }}">
            <div class="card rounded-4 shadow-sm border-0">
                <div class="card-header bg-white border-0 px-4 py-3">
                    <div class="d-flex align-items-center gap-3">
                        @if($icon)
                        <div class="rounded-3 bg-primary bg-opacity-10 p-2">
                            <i class="bi bi-{{ $icon }} text-primary fs-5"></i>
                        </div>
                        @endif
                        <h5 class="mb-0 fw-bold">{{ $title }}</h5>
                    </div>
                </div>
                <div class="card-body px-4 py-3">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</div>