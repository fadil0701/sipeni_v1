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
    <div class="px-6 py-5 border-b border-gray-200 flex justify-between items-center">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Profil Role</h2>
            <p class="text-sm text-gray-600 mt-1">
                <span class="font-semibold text-gray-900">{{ $role->display_name }}</span>
            </p>
        </div>
        <a 
            href="{{ route('admin.roles.edit', $role->id) }}" 
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
        >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Edit
        </a>
    </div>
    
    <div class="p-6">
        <div class="grid grid-cols-1 gap-6">
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Role</h3>
                <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Kategori Akses</dt>
                        <dd class="text-sm text-gray-900">{{ $role->level_akses === 'pusat' ? 'Pusat' : 'Unit Kerja' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Nama tampilan</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $role->display_name }}</dd>
                    </div>
                    @if($role->description)
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 mb-1">Deskripsi</dt>
                        <dd class="text-sm text-gray-900">{{ $role->description }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Hak Akses Modul — {{ $role->permissions->count() }} entri</h3>
                @if($role->permissions->count() > 0)
                    <div class="overflow-x-auto rounded-xl border border-gray-200">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-100 text-gray-700">
                                <tr>
                                    <th class="px-3 py-2 text-left">Modul</th>
                                    <th class="px-3 py-2 text-center">View</th>
                                    <th class="px-3 py-2 text-center">Create</th>
                                    <th class="px-3 py-2 text-center">Update</th>
                                    <th class="px-3 py-2 text-center">Approve</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($simplifiedMatrix as $modKey => $item)
                                    <tr class="border-t border-gray-200">
                                        <td class="px-3 py-2">
                                            <p class="font-medium text-gray-800">{{ $item['label'] }}</p>
                                        </td>
                                        @foreach (['view', 'create', 'update', 'approve'] as $action)
                                            <td class="px-3 py-2 text-center">
                                                @if ($item['actions'][$action]['ids'] ?? false)
                                                    <svg class="inline-block h-5 w-5 {{ $item['actions'][$action]['all_checked'] ? 'text-green-600' : 'text-amber-500' }}" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                    </svg>
                                                @else
                                                    <span class="text-gray-300">–</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-gray-500">Role ini belum memiliki hak akses. <a href="{{ route('admin.roles.edit', $role->id) }}" class="text-blue-600 hover:text-blue-800">Atur di halaman edit</a></p>
                @endif
            </div>

            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">User dengan role ini ({{ $role->users->count() }})</h3>
                @if($role->users->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Terdaftar</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($role->users as $user)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $user->name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $user->email }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $user->created_at->format('d/m/Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-gray-500">Belum ada user dengan role ini.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
