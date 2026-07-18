@extends('layouts.app')

@section('content')
<div class="rounded-xl bg-[#F8FAFC] p-4 sm:p-6">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Workflow Template</h1>
            <p class="mt-1 text-sm text-slate-600">Template dokumen mengikuti alur proses standar pemerintahan.</p>
        </div>
        <a
            href="{{ route('admin.print-templates.create') }}"
            class="inline-flex shrink-0 items-center justify-center rounded-xl bg-[#1E3A8A] px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-blue-900"
        >
            Tambah Template
        </a>
    </div>

    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-xl border border-[#E5E7EB] bg-white p-4 shadow-sm lg:col-span-1">
            <p class="text-xs font-medium text-slate-500">Total Template</p>
            <p class="mt-1 text-2xl font-semibold text-slate-900">{{ number_format($summary['total_templates']) }}</p>
        </div>
        <div class="rounded-xl border border-[#E5E7EB] bg-white p-4 shadow-sm">
            <p class="text-xs font-medium text-slate-500">Aktif</p>
            <p class="mt-1 text-2xl font-semibold text-[#22C55E]">{{ number_format($summary['active_templates']) }}</p>
        </div>
        <div class="col-span-2 rounded-xl border border-[#E5E7EB] bg-white p-4 shadow-sm lg:col-span-2">
            <p class="text-sm font-semibold text-slate-800">Alur proses standar</p>
            <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center">
                @foreach (['Draft', 'Diajukan', 'Mengetahui', 'Verifikasi', 'Proses', 'Selesai'] as $step)
                    <div class="flex items-center gap-2">
                        <span class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-[#1E3A8A]">{{ $step }}</span>
                        @if (!$loop->last)
                            <span class="hidden text-slate-300 sm:inline">↓</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.print-templates.index') }}" class="mb-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <div class="relative flex-1">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400" aria-hidden="true">🔍</span>
                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari nama template..."
                    class="w-full rounded-xl border border-[#E5E7EB] bg-white py-2.5 pl-10 pr-4 text-sm shadow-sm placeholder:text-slate-400 focus:border-[#1E3A8A] focus:outline-none focus:ring-2 focus:ring-[#1E3A8A]/20"
                >
            </div>
            <button type="submit" class="rounded-xl border border-[#E5E7EB] bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                Filter
            </button>
        </div>
    </form>

    <div class="space-y-4">
        @forelse($templates as $tpl)
            <article class="rounded-xl border border-[#E5E7EB] bg-white p-5 shadow-sm transition hover:shadow-md">
                <div class="flex flex-col gap-5 lg:flex-row lg:justify-between">
                    <div class="flex gap-4">
                        <div class="hidden shrink-0 flex-col items-center sm:flex">
                            @foreach (['Draft', 'Diajukan', 'Mengetahui', 'Verifikasi', 'Proses', 'Selesai'] as $i => $step)
                                <div class="flex flex-col items-center">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-full {{ $i === 0 ? 'bg-[#1E3A8A] text-white' : ($i === 5 ? 'bg-[#22C55E] text-white' : 'bg-slate-100 text-slate-600') }} text-xs font-semibold">{{ $i + 1 }}</span>
                                    @if ($i < 5)
                                        <span class="my-0.5 text-slate-300">|</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <div class="min-w-0">
                            <h2 class="text-lg font-semibold text-slate-900">{{ $tpl->name }}</h2>
                            <div class="mt-2 flex flex-wrap gap-1 sm:hidden">
                                @foreach (['Draft', 'Diajukan', 'Mengetahui', 'Verifikasi', 'Proses', 'Selesai'] as $step)
                                    <span class="rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-medium text-[#1E3A8A]">{{ $step }}</span>
                                @endforeach
                            </div>
                            <p class="mt-1 text-sm text-slate-600">Dokumen alur — {{ strtoupper($tpl->paper_size ?? 'a4') }}, {{ ($tpl->orientation ?? 'portrait') === 'landscape' ? 'lanskap' : 'potret' }}</p>
                            <div class="mt-3 flex flex-wrap gap-2">
                                @if($tpl->is_active)
                                    <span class="inline-flex rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-[#22C55E]">Aktif</span>
                                @else
                                    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">Nonaktif</span>
                                @endif
                                <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-[#1E3A8A]">Workflow</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                        <a
                            href="{{ route('admin.print-templates.preview', $tpl) }}"
                            target="_blank"
                            rel="noopener"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-[#E5E7EB] bg-white text-sm text-slate-700 shadow-sm hover:bg-slate-50"
                            title="Pratinjau"
                        >👁</a>
                        <a
                            href="{{ route('admin.print-templates.show', $tpl) }}"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-[#E5E7EB] bg-white text-sm text-slate-700 shadow-sm hover:bg-slate-50"
                            title="Detail"
                        >📄</a>
                        <a
                            href="{{ route('admin.print-templates.pdf', $tpl) }}"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-[#E5E7EB] bg-white text-sm text-slate-700 shadow-sm hover:bg-slate-50"
                            title="PDF"
                        >⬇</a>
                        <a
                            href="{{ route('admin.print-templates.edit', $tpl) }}"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-[#E5E7EB] bg-white text-sm text-slate-700 shadow-sm hover:bg-slate-50"
                            title="Edit"
                        >✏</a>
                        <form action="{{ route('admin.print-templates.destroy', $tpl) }}" method="POST" class="inline" data-confirm="Hapus template ini?">
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-red-100 bg-red-50 text-sm text-[#EF4444] hover:bg-red-100"
                                title="Hapus"
                            >🗑</button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-xl border border-[#E5E7EB] bg-white py-14 text-center shadow-sm">
                <p class="text-sm font-medium text-slate-900">Belum ada template</p>
                <p class="mt-1 text-sm text-slate-500">Buat template pertama untuk mendukung cetak dokumen.</p>
            </div>
        @endforelse
    </div>

    @if($templates->hasPages())
        <div class="mt-6 rounded-xl border border-[#E5E7EB] bg-white px-4 py-3 shadow-sm">
            {{ $templates->links() }}
        </div>
    @endif
</div>
@endsection
