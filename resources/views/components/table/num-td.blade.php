@props(['paginator' => null])
@php
    $loopContext = $__env->getLastLoop();
    $rowIndex = is_array($loopContext)
        ? ($loopContext['index'] ?? null)
        : (is_object($loopContext) ? ($loopContext->index ?? null) : null);
    $rowIteration = is_array($loopContext)
        ? ($loopContext['iteration'] ?? null)
        : (is_object($loopContext) ? ($loopContext->iteration ?? null) : null);
@endphp
<td {{ $attributes->merge(['class' => 'px-6 py-4 whitespace-nowrap text-sm text-gray-600 tabular-nums']) }}>
    @if($paginator instanceof \Illuminate\Contracts\Pagination\Paginator && $rowIndex !== null)
        {{ $paginator->firstItem() + $rowIndex }}
    @elseif($rowIteration !== null)
        {{ $rowIteration }}
    @else
        -
    @endif
</td>
