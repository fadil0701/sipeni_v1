<?php
$items = $items ?? [];
$orientation = $orientation ?? 'vertical';
?>

@if(count($items) > 0)
    <div class="{{ $orientation === 'horizontal' ? 'flex items-center gap-4' : 'space-y-6' }}">
        @foreach($items as $item)
            <div class="relative flex {{ $orientation === 'horizontal' ? 'flex-col' : 'flex-row' }} gap-4">
                @if($orientation === 'vertical')
                    <div class="flex flex-col items-center">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full
                            {{ $item['color'] ?? 'bg-slate-100' }}
                            {{ $item['textColor'] ?? 'text-slate-600' }}
                        ">
                            {!! $item['icon'] ?? '' !!}
                        </div>
                        @if(!$loop->last)
                            <div class="w-0.5 h-full bg-gray-200 my-2"></div>
                        @endif
                    </div>
                @else
                    <div class="flex h-8 w-8 items-center justify-center rounded-full
                        {{ $item['color'] ?? 'bg-slate-100' }}
                        {{ $item['textColor'] ?? 'text-slate-600' }}
                    ">
                        {!! $item['icon'] ?? '' !!}
                    </div>
                @endif

                <div class="flex-1 min-w-0">
                    <div class="text-sm">
                        {!! $item['content'] !!}
                    </div>
                    @if(isset($item['time']))
                        <p class="text-xs text-slate-400 mt-1">{{ $item['time'] }}</p>
                    @endif
                    @if(isset($item['details']))
                        <div class="mt-2 text-xs text-slate-500">
                            {{ $item['details'] }}
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@else
    <p class="text-sm text-slate-400">Tidak ada aktivitas</p>
@endif