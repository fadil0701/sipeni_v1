@php
    $selectedRoleIds = $selectedRoleIds ?? [];
    $fieldId = $fieldId ?? 'role_ids';
@endphp
<div>
    <label class="mb-1 block text-sm font-medium text-slate-700">
        Role <span class="text-red-500">*</span>
    </label>
    <p class="mb-2 text-xs text-slate-500">Hak akses user mengikuti role yang dipilih.</p>
    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 @error('role_ids') rounded-lg ring-1 ring-red-500 @enderror">
        @foreach($roles as $role)
            <label class="flex cursor-pointer items-start gap-2 rounded-md border border-slate-200 bg-white px-3 py-2 text-sm hover:border-slate-300">
                <input
                    type="checkbox"
                    name="role_ids[]"
                    value="{{ $role->id }}"
                    class="mt-0.5 h-4 w-4 rounded border-slate-300 text-slate-800 focus:ring-slate-500"
                    @checked(in_array($role->id, $selectedRoleIds, true))
                >
                <span class="min-w-0 flex-1">
                    <span class="block font-medium text-slate-900">{{ $role->display_name }}</span>
                    @if($role->description)
                        <span class="block truncate text-xs text-slate-500">{{ $role->description }}</span>
                    @endif
                </span>
            </label>
        @endforeach
    </div>
    @error('role_ids')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
    @error('role_ids.*')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
