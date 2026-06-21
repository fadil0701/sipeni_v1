@php
    $cards = $cards ?? [];
@endphp
<div class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
    @foreach($cards as $card)
        <div class="rounded-lg border border-slate-200 bg-white px-4 py-3">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ $card['label'] }}</p>
            <p class="mt-1 text-2xl font-semibold {{ $card['valueClass'] ?? 'text-slate-900' }}">{{ $card['value'] }}</p>
            @if(!empty($card['href']))
                <a href="{{ $card['href'] }}" class="mt-1 inline-block text-xs font-medium text-blue-600 hover:text-blue-800">{{ $card['link'] ?? 'Lihat' }}</a>
            @endif
        </div>
    @endforeach
</div>
