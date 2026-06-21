<?php
$nodes = $nodes ?? [];
$currentStage = $currentStage ?? null;
$showLabels = $showLabels ?? true;

$stageColors = [
    'draft' => 'bg-slate-100 text-slate-500',
    'submitted' => 'bg-blue-100 text-blue-700',
    'pending' => 'bg-amber-100 text-amber-700',
    'approve' => 'bg-emerald-100 text-emerald-700',
    'approved' => 'bg-emerald-500 text-white',
    'verify' => 'bg-cyan-100 text-cyan-700',
    'verified' => 'bg-cyan-500 text-white',
    'process' => 'bg-violet-100 text-violet-700',
    'processed' => 'bg-violet-500 text-white',
    'finish' => 'bg-slate-100 text-slate-500',
    'completed' => 'bg-emerald-500 text-white',
    'rejected' => 'bg-red-500 text-white',
    'cancelled' => 'bg-slate-400 text-white',
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
                    $colorClass = 'bg-red-500 text-white';
                } elseif ($isCompleted) {
                    $colorClass = 'bg-emerald-500 text-white';
                } elseif ($isActive) {
                    $colorClass = $stageColors[$stage] ?? 'bg-blue-600 text-white';
                } else {
                    $colorClass = 'bg-slate-100 text-slate-400';
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