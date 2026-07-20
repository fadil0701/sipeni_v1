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
<td {{ $attributes->merge(['class' => 'w-0 whitespace-nowrap px-2 py-3 text-center text-sm text-gray-600 tabular-nums align-middle']) }}>
    @if($paginator instanceof \Illuminate\Contracts\Pagination\Paginator && $rowIndex !== null)
        {{ $paginator->firstItem() + $rowIndex }}
    @elseif($rowIteration !== null)
        {{ $rowIteration }}
    @else
        -
    @endif
</td>
