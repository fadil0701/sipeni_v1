@php
    $roleModel = $role ?? null;
    $isEdit = $roleModel !== null;
    $isSystemRole = $isEdit && \App\Support\Admin\SystemRole::isSystemRole($roleModel);
@endphp
<section class="rounded-lg border border-slate-200 bg-slate-50/50 p-4">
    <div class="flex flex-wrap items-center gap-2">
        <h3 class="text-sm font-semibold text-slate-900">Informasi Role</h3>
        @if($isSystemRole)
            <span class="rounded bg-slate-800 px-1.5 py-0.5 text-[10px] font-semibold uppercase text-white">System Role</span>
        @endif
    </div>
    <p class="mt-1 text-xs text-slate-500">Identitas role dan level akses organisasi.</p>

    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <label for="display_name" class="mb-1 block text-sm font-medium text-gray-700">Nama tampilan <span class="text-red-500">*</span></label>
            <input type="text" id="display_name" name="display_name" required
                value="{{ old('display_name', $roleModel?->display_name) }}"
                placeholder="contoh: Admin Gudang Unit"
                class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('display_name') border-red-500 @enderror">
            @error('display_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="name" class="mb-1 block text-sm font-medium text-gray-700">Kode role <span class="text-red-500">*</span></label>
            <input type="text" id="name" name="name" required
                value="{{ old('name', $roleModel?->name) }}"
                placeholder="contoh: admin_gudang_unit"
                class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('name') border-red-500 @enderror">
            @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            <p class="mt-1 text-xs text-gray-500">Kode internal; gunakan huruf kecil dan underscore.</p>
        </div>
        <div>
            <label for="level_akses" class="mb-1 block text-sm font-medium text-gray-700">Level akses <span class="text-red-500">*</span></label>
            <select id="level_akses" name="level_akses" required class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm @error('level_akses') border-red-500 @enderror">
                <option value="unit" {{ old('level_akses', $roleModel?->level_akses ?? 'unit') === 'unit' ? 'selected' : '' }}>Unit Kerja</option>
                <option value="pusat" {{ old('level_akses', $roleModel?->level_akses) === 'pusat' ? 'selected' : '' }}>Pusat</option>
            </select>
            @error('level_akses')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        @unless($isEdit)
        <div>
            <label for="clone_from_role_id" class="mb-1 block text-sm font-medium text-gray-700">Clone hak akses dari role</label>
            <select id="clone_from_role_id" name="clone_from_role_id" class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm">
                <option value="">Mulai dari kosong</option>
                @foreach(\App\Models\Role::orderBy('display_name')->get() as $cloneRole)
                    <option value="{{ $cloneRole->id }}" {{ (string) old('clone_from_role_id', request('clone_from_role_id')) === (string) $cloneRole->id ? 'selected' : '' }}>
                        {{ $cloneRole->display_name }}
                    </option>
                @endforeach
            </select>
        </div>
        @endunless
        <div class="flex items-end md:col-span-2">
            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    {{ old('is_active', $isEdit ? (int) $roleModel->is_active : 1) ? 'checked' : '' }}>
                <span>Role aktif</span>
            </label>
        </div>
    </div>

    <div class="mt-4">
        <label for="description" class="mb-1 block text-sm font-medium text-gray-700">Deskripsi</label>
        <textarea id="description" name="description" rows="3" placeholder="Deskripsi singkat peran dan tanggung jawab..."
            class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description', $roleModel?->description) }}</textarea>
    </div>
</section>
