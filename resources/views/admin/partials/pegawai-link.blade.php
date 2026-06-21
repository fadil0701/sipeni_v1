<details class="rounded-lg border border-slate-200 bg-slate-50/80">
    <summary class="cursor-pointer px-4 py-3 text-sm font-medium text-slate-800">Hubungkan dengan Master Pegawai</summary>
    <div class="border-t border-slate-200 px-4 pb-4 pt-3">
        <label for="pegawai_id" class="mb-1 block text-xs font-medium text-slate-600">Pilih pegawai (opsional)</label>
        <select
            id="pegawai_id"
            name="pegawai_id"
            class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:ring-slate-500"
            onchange="typeof fillFromPegawai === 'function' && fillFromPegawai()"
        >
            <option value="">— Tidak dihubungkan —</option>
            @foreach($pegawais as $pegawai)
                <option
                    value="{{ $pegawai->id }}"
                    data-nama="{{ e($pegawai->nama_pegawai) }}"
                    data-email="{{ e($pegawai->email_pegawai ?? '') }}"
                    @selected((string) old('pegawai_id', $selectedPegawaiId ?? '') === (string) $pegawai->id)
                >
                    {{ $pegawai->nama_pegawai }} ({{ $pegawai->nip_pegawai }})
                </option>
            @endforeach
        </select>
        <p class="mt-2 text-xs text-slate-500">Mengisi nama dan email otomatis. Role tetap diatur di field Role di bawah.</p>
    </div>
</details>
