<?php
$nodes = $nodes ?? [];
$currentStage = $currentStage ?? null;
$showLabels = $showLabels ?? true;

// Semantic workflow stages only (no violet/cyan rainbow)
$stageColors = [
    'draft' => \App\Support\UiColor::badge('neutral'),
    'submitted' => \App\Support\UiColor::badge('warning'),
    'pending' => \App\Support\UiColor::badge('warning'),
    'approve' => \App\Support\UiColor::badge('success'),
    'approved' => 'bg-green-600 text-white',
    'verify' => \App\Support\UiColor::badge('info'),
    'verified' => 'bg-blue-600 text-white',
    'process' => \App\Support\UiColor::badge('info'),
    'processed' => 'bg-blue-600 text-white',
    'finish' => \App\Support\UiColor::badge('neutral'),
    'completed' => 'bg-green-600 text-white',
    'rejected' => 'bg-red-600 text-white',
    'cancelled' => 'bg-gray-500 text-white',
];

$nodeLabels = [
    'draft' => 'D',
    'submitted' => 'S',
    'pending' => 'P',
    'approve' => 'A',
    'approved' => 'A',
    'verify' => 'V',
    'verified' => 'V',
    'process' => 'P',
    'processed' => 'P',
    'finish' => 'F',
    'completed' => 'F',
    'rejected' => 'X',
    'cancelled' => 'X',
];
?>

@if(count($nodes) > 0)
    <span class="inline-flex items-center gap-1.5 text-xs">
        @foreach($nodes as $index => $node)
            @php
                $stage = $node['stage'] ?? 'draft';
                $isActive = $node['active'] ?? false;
                $isCompleted = $node['completed'] ?? false;
                $isRejected = $node['rejected'] ?? false;

                if ($isRejected) {
                    $colorClass = 'bg-red-600 text-white';
                } elseif ($isCompleted) {
                    $colorClass = 'bg-green-600 text-white';
                } elseif ($isActive) {
                    $colorClass = $stageColors[$stage] ?? 'bg-blue-600 text-white';
                } else {
                    $colorClass = 'bg-gray-100 text-gray-500';
                }
            @endphp

            <span class="flex h-6 w-6 items-center justify-center rounded-full {{ $colorClass }} text-[10px] font-medium">
                {{ $nodeLabels[$stage] ?? '?' }}
            </span>

            @if($index < count($nodes) - 1)
                <span class="text-slate-300">──</span>
            @endif
        @endforeach
    </span>
@else
    <span class="text-xs text-slate-400">-</span>
@endif
