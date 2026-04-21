@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.roles.index') }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar Role
    </a>
</div>

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Edit Role</h2>
    </div>
    
    <form action="{{ route('admin.roles.update', $role->id) }}" method="POST" class="p-6">
        @csrf
        @method('PUT')
        
        <div class="space-y-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Kode role (internal) <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    required
                    value="{{ old('name', $role->name) }}"
                    placeholder="contoh: admin, admin_gudang, kepala, pegawai"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('name') border-red-500 @enderror"
                >
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="display_name" class="block text-sm font-medium text-gray-700 mb-2">
                    Nama tampilan <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="display_name" 
                    name="display_name" 
                    required
                    value="{{ old('display_name', $role->display_name) }}"
                    placeholder="contoh: Admin, Admin Gudang, Kepala/Pimpinan"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('display_name') border-red-500 @enderror"
                >
                @error('display_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                <textarea 
                    id="description" 
                    name="description" 
                    rows="3"
                    placeholder="Masukkan deskripsi role..."
                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                >{{ old('description', $role->description) }}</textarea>
            </div>

            @include('admin.roles.partials.permission-help')

            @unless($canDelegateAllPermissions)
                <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4 mb-4 text-sm text-indigo-900">
                    <p class="font-medium">Tampilan disesuaikan dengan level akses Anda</p>
                    <p class="mt-1 text-indigo-800">Hanya permission yang Anda sendiri miliki yang dapat dicentang. Admin penuh melihat dan mengatur semua permission.</p>
                </div>
            @endunless

            @if(!empty($lockedPermissionGroups))
                <div class="mb-6 border border-amber-200 rounded-lg bg-amber-50/80 p-4">
                    <h3 class="text-sm font-semibold text-amber-900 mb-1">Permission pada role ini (tidak dapat Anda ubah)</h3>
                    <p class="text-xs text-amber-800 mb-4">Hak berikut tetap aktif pada role sampai diubah oleh akun dengan akses setara atau admin penuh.</p>
                    <div class="space-y-4 max-h-64 overflow-y-auto">
                        @foreach($lockedPermissionGroups as $group)
                            <div>
                                <p class="text-xs font-semibold text-amber-950 uppercase tracking-wide mb-2">{{ $group['label'] }}</p>
                                <ul class="space-y-2 pl-1">
                                    @foreach($group['items'] as $permission)
                                        <li class="text-sm text-amber-950 flex items-start gap-2">
                                            <svg class="w-4 h-4 text-amber-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                            <span>
                                                <span class="font-medium">{{ $permission->display_name }}</span>
                                                <span class="block text-xs font-mono text-amber-800/90 mt-0.5">{{ $permission->name }}</span>
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Permissions yang dapat Anda atur (checkbox) -->
            <div>
                <div class="flex items-center justify-between mb-4">
                    <label class="block text-sm font-medium text-gray-700">
                        Hak akses yang dapat Anda atur
                    </label>
                    <label class="flex items-center text-sm text-blue-600 hover:text-blue-800 cursor-pointer font-medium">
                        <input 
                            type="checkbox" 
                            id="select-all-permissions"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        >
                        <span class="ml-2">Pilih Semua</span>
                    </label>
                </div>

                <!-- Search Box -->
                <div class="mb-4">
                    <input 
                        type="text" 
                        id="permission-search"
                        placeholder="Cari permission..."
                        class="block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    >
                </div>

                <div class="assignable-permissions-scope bg-gray-50 p-4 rounded-lg border border-gray-200 max-h-[600px] overflow-y-auto">
                    @forelse($permissionGroups as $group)
                        @php $moduleId = 'module-' . str_replace(['.', '-', '_'], '-', $group['module']); @endphp
                        <div class="mb-4 border border-gray-200 rounded-lg bg-white overflow-hidden module-container" data-module="{{ $group['module'] }}">
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-4 py-3 border-b border-gray-200 cursor-pointer module-header" data-target="{{ $moduleId }}">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <svg class="w-5 h-5 text-blue-600 transition-transform transform module-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                        <h4 class="text-sm font-semibold text-gray-900">{{ $group['label'] }}</h4>
                                        <span class="px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 rounded-full module-count" data-module="{{ $group['module'] }}">
                                            {{ count($group['checked_ids']) }}/{{ $group['items']->count() }}
                                        </span>
                                    </div>
                                    <label class="flex items-center text-xs text-blue-600 hover:text-blue-800 cursor-pointer" onclick="event.stopPropagation()">
                                        <input 
                                            type="checkbox" 
                                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded module-select-all"
                                            data-module="{{ $group['module'] }}"
                                            {{ $group['all_checked'] ? 'checked' : '' }}
                                            {{ $group['some_checked'] ? 'indeterminate' : '' }}
                                        >
                                        <span class="ml-2 font-medium">Pilih Semua</span>
                                    </label>
                                </div>
                            </div>
                            <div id="{{ $moduleId }}" class="module-content hidden">
                                <div class="p-4 space-y-3">
                                    @foreach($group['items'] as $permission)
                                        <label class="flex items-start p-3 rounded-lg hover:bg-gray-50 transition-colors permission-item" data-permission-name="{{ strtolower($permission->display_name . ' ' . $permission->name) }}">
                                            <input 
                                                type="checkbox" 
                                                name="permissions[]" 
                                                value="{{ $permission->id }}"
                                                {{ in_array($permission->id, $group['checked_ids']) ? 'checked' : '' }}
                                                class="mt-0.5 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded permission-checkbox"
                                                data-module="{{ $group['module'] }}"
                                            >
                                            <div class="ml-3 flex-1">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-sm font-medium text-gray-900">{{ $permission->display_name }}</span>
                                                    @if(str_contains($permission->name, '.*'))
                                                        <span class="px-2 py-0.5 text-xs font-medium bg-green-100 text-green-800 rounded">All</span>
                                                    @endif
                                                </div>
                                                @if($permission->description)
                                                    <p class="text-xs text-gray-600 mt-1">{{ $permission->description }}</p>
                                                @endif
                                                <p class="text-xs text-gray-400 mt-1 font-mono">{{ $permission->name }}</p>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-600 py-4 text-center">Tidak ada permission pada level akses Anda yang dapat diberikan ke role ini. Hubungi admin penuh bila perlu menambah hak.</p>
                    @endforelse
                </div>
                <p class="mt-2 text-xs text-gray-500">
                    <span id="selected-count" class="font-medium text-blue-600">{{ $totalChecked }}</span> permission dipilih (yang dapat Anda kelola)
                </p>
            </div>

            @push('scripts')
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const scope = document.querySelector('.assignable-permissions-scope');
                if (!scope) return;

                const globalSelectAll = document.getElementById('select-all-permissions');

                function updateGlobalSelectAll() {
                    const allCheckboxes = scope.querySelectorAll('.permission-checkbox');
                    const checkedCount = Array.from(allCheckboxes).filter(cb => cb.checked).length;
                    if (globalSelectAll) {
                        globalSelectAll.checked = checkedCount === allCheckboxes.length;
                        globalSelectAll.indeterminate = checkedCount > 0 && checkedCount < allCheckboxes.length;
                    }
                }

                // Collapsible modules (hanya area yang dapat di-edit)
                scope.querySelectorAll('.module-header').forEach(function(header) {
                    header.addEventListener('click', function() {
                        const targetId = this.dataset.target;
                        const content = document.getElementById(targetId);
                        const arrow = this.querySelector('.module-arrow');
                        
                        if (content.classList.contains('hidden')) {
                            content.classList.remove('hidden');
                            arrow.classList.add('rotate-180');
                        } else {
                            content.classList.add('hidden');
                            arrow.classList.remove('rotate-180');
                        }
                    });
                });

                // Expand all modules by default
                scope.querySelectorAll('.module-content').forEach(function(content) {
                    content.classList.remove('hidden');
                    const header = content.previousElementSibling;
                    if (header) {
                        header.querySelector('.module-arrow').classList.add('rotate-180');
                    }
                });

                // Update module count
                function updateModuleCount(module) {
                    const checkboxes = scope.querySelectorAll(`.permission-checkbox[data-module="${module}"]`);
                    const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
                    const countElement = scope.querySelector(`.module-count[data-module="${module}"]`);
                    if (countElement) {
                        countElement.textContent = `${checkedCount}/${checkboxes.length}`;
                    }
                }

                // Update selected count
                function updateSelectedCount() {
                    const allCheckboxes = scope.querySelectorAll('.permission-checkbox');
                    const checkedCount = Array.from(allCheckboxes).filter(cb => cb.checked).length;
                    const countElement = document.getElementById('selected-count');
                    if (countElement) {
                        countElement.textContent = checkedCount;
                    }
                }

                // Handle "Select All" per module
                scope.querySelectorAll('.module-select-all').forEach(function(selectAll) {
                    selectAll.addEventListener('change', function(e) {
                        e.stopPropagation();
                        const module = this.dataset.module;
                        const checkboxes = scope.querySelectorAll(`.permission-checkbox[data-module="${module}"]`);
                        checkboxes.forEach(function(checkbox) {
                            checkbox.checked = selectAll.checked;
                        });
                        updateModuleCount(module);
                        updateSelectedCount();
                        updateGlobalSelectAll();
                    });
                });

                // Handle global "Select All"
                if (globalSelectAll) {
                    const allCheckboxes = scope.querySelectorAll('.permission-checkbox');
                    const checkedCount = Array.from(allCheckboxes).filter(cb => cb.checked).length;
                    globalSelectAll.checked = checkedCount === allCheckboxes.length;
                    globalSelectAll.indeterminate = checkedCount > 0 && checkedCount < allCheckboxes.length;

                    globalSelectAll.addEventListener('change', function() {
                        const boxes = scope.querySelectorAll('.permission-checkbox');
                        boxes.forEach(function(checkbox) {
                            checkbox.checked = this.checked;
                        }.bind(this));
                        
                        scope.querySelectorAll('.module-select-all').forEach(function(selectAll) {
                            selectAll.checked = this.checked;
                        }.bind(this));
                        
                        scope.querySelectorAll('.module-container').forEach(function(container) {
                            const module = container.dataset.module;
                            updateModuleCount(module);
                        });
                        updateSelectedCount();
                    });
                }

                // Update "Select All" checkbox when individual checkboxes change
                scope.querySelectorAll('.permission-checkbox').forEach(function(checkbox) {
                    checkbox.addEventListener('change', function() {
                        const module = this.dataset.module;
                        const moduleCheckboxes = scope.querySelectorAll(`.permission-checkbox[data-module="${module}"]`);
                        const checkedCount = Array.from(moduleCheckboxes).filter(cb => cb.checked).length;
                        const selectAll = scope.querySelector(`.module-select-all[data-module="${module}"]`);
                        if (selectAll) {
                            selectAll.checked = checkedCount === moduleCheckboxes.length;
                            selectAll.indeterminate = checkedCount > 0 && checkedCount < moduleCheckboxes.length;
                        }
                        updateModuleCount(module);
                        updateSelectedCount();
                        updateGlobalSelectAll();
                    });
                });

                // Search functionality
                const searchInput = document.getElementById('permission-search');
                if (searchInput) {
                    searchInput.addEventListener('input', function() {
                        const searchTerm = this.value.toLowerCase();
                        const permissionItems = scope.querySelectorAll('.permission-item');
                        const modules = new Set();
                        
                        permissionItems.forEach(function(item) {
                            const permissionName = item.dataset.permissionName || '';
                            if (permissionName.includes(searchTerm)) {
                                item.style.display = '';
                                const module = item.querySelector('.permission-checkbox').dataset.module;
                                modules.add(module);
                            } else {
                                item.style.display = 'none';
                            }
                        });

                        // Show/hide modules based on search
                        scope.querySelectorAll('.module-container').forEach(function(container) {
                            const module = container.dataset.module;
                            if (searchTerm === '' || modules.has(module)) {
                                container.style.display = '';
                                // Auto expand if searching
                                if (searchTerm !== '') {
                                    const content = container.querySelector('.module-content');
                                    const arrow = container.querySelector('.module-arrow');
                                    if (content && content.classList.contains('hidden')) {
                                        content.classList.remove('hidden');
                                        arrow.classList.add('rotate-180');
                                    }
                                }
                            } else {
                                container.style.display = 'none';
                            }
                        });
                    });
                }

                // Initialize counts
                scope.querySelectorAll('.module-container').forEach(function(container) {
                    const module = container.dataset.module;
                    updateModuleCount(module);
                });
                updateSelectedCount();
            });
            </script>
            @endpush
        </div>

        <div class="mt-8 flex justify-end space-x-3 border-t border-gray-200 pt-6">
            <a 
                href="{{ route('admin.roles.index') }}" 
                class="px-5 py-2.5 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
                Batal
            </a>
            <button 
                type="submit" 
                class="px-5 py-2.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
                Simpan
            </button>
        </div>
    </form>
</div>
@endsection

