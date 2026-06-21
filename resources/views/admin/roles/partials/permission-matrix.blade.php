@php
    $matrixActions = ['view', 'create', 'update', 'approve'];
    $checkedPermissionIds = $checkedPermissionIds ?? [];
    $groupedMatrix = $groupedMatrix ?? null;
    $simplifiedMatrix = $simplifiedMatrix ?? [];
    if ($groupedMatrix === null && $simplifiedMatrix !== []) {
        $groupedMatrix = [['key' => 'all', 'label' => 'Semua modul', 'items' => $simplifiedMatrix]];
    }
@endphp

<div class="permission-matrix-section">
    <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <h3 class="text-sm font-semibold text-slate-900">Permission Matrix</h3>
        <label class="inline-flex cursor-pointer items-center text-xs font-medium text-slate-600">
            <input type="checkbox" id="select-all-parents" class="mr-2 h-4 w-4 rounded border-slate-300 text-slate-800">
            Pilih semua yang tersedia
        </label>
    </div>

    @unless($canDelegateAllPermissions ?? true)
        <p class="mb-3 text-xs text-slate-500">Hanya permission yang Anda miliki yang dapat diberikan ke role ini.</p>
    @endunless

    <div class="space-y-2">
        @forelse ($groupedMatrix ?? [] as $group)
            <details class="permission-matrix-group rounded-lg border border-slate-200 bg-white" @if($loop->first) open @endif>
                <summary class="cursor-pointer px-3 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">{{ $group['label'] }}</summary>
                <div class="overflow-x-auto border-t border-slate-100">
                    <table class="permission-matrix-table min-w-full text-sm">
                        <thead class="sticky top-0 z-10 bg-slate-50 text-xs text-slate-600">
                            <tr>
                                <th class="px-3 py-2 text-left font-semibold">Sub modul</th>
                                @foreach ($matrixActions as $actionLabel)
                                    <th class="w-16 px-2 py-2 text-center font-semibold capitalize">{{ $actionLabel }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($group['items'] as $item)
                                @php $modKey = $item['key']; @endphp
                                <tr class="border-t border-slate-100">
                                    <td class="px-3 py-1.5 text-slate-800">{{ $item['label'] }}</td>
                                    @foreach ($matrixActions as $action)
                                        <td class="px-2 py-1.5 text-center align-middle">
                                            @php
                                                $cell = $item['actions'][$action] ?? null;
                                                $childIds = $cell['ids'] ?? $cell['permission_ids'] ?? [];
                                            @endphp
                                            @if (! empty($childIds))
                                                @php $allChecked = $cell['all_checked'] ?? false; @endphp
                                                <input type="checkbox"
                                                    class="parent-mod-toggle h-4 w-4 rounded border-slate-300 text-slate-800"
                                                    data-module="{{ $modKey }}"
                                                    data-action="{{ $action }}"
                                                    @checked($allChecked)>
                                                <div class="hidden child-checkboxes">
                                                    @foreach ($childIds as $cid)
                                                        <input type="checkbox" name="permissions[]" value="{{ $cid }}"
                                                            class="child-cb-{{ $modKey }}-{{ $action }}"
                                                            @checked(in_array((int) $cid, $checkedPermissionIds, true))>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-slate-300">–</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>
        @empty
            <p class="rounded-lg border border-slate-200 px-4 py-6 text-center text-sm text-slate-500">Tidak ada permission yang dapat diatur.</p>
        @endforelse
    </div>

    <p class="mt-2 text-xs text-slate-500"><span id="selected-count" class="font-semibold text-slate-700">{{ count($checkedPermissionIds) }}</span> permission dipilih</p>
</div>
