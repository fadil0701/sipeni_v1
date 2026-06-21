@props(['title' => null, 'description' => null])

<section {{ $attributes->merge(['class' => 'rounded-lg border border-slate-200 bg-slate-50/50 p-4']) }}>
    @if($title)
        <h3 class="text-sm font-semibold text-slate-900">{{ $title }}</h3>
    @endif
    @if($description)
        <p class="mt-0.5 text-xs text-slate-500">{{ $description }}</p>
    @endif
    <div class="{{ $title || $description ? 'mt-4' : '' }}">
        {{ $slot }}
    </div>
</section>
