@extends('layouts.app')

@section('content')
<x-form.card-form title="Tambah Kegiatan" icon="activity" size="col-xl-10">
    <form action="{{ route('master.kegiatankegitan.store') }}" method="POST" class="row g-4">
        @csrf
        
        <div class="col-md-6">
            <label for="id_program" class="form-label fw-semibold mb-2">
                Program <span class="text-danger">*</span>
            </label>
            <select 
                id="id_program" 
                name="id_program" 
                required
                class="form-select rounded-3 @error('id_program') is-invalid @enderror"
            >
                <option value="">Pilih Program...</option>
                @foreach($programs ?? [] as $program)
                    <option value="{{ $program->id_program }}" {{ old('id_program') == $program->id_program ? 'selected' : '' }}>
                        {{ $program->kode_program }} - {{ $program->nama_program }}
                    </option>
                @endforeach
            </select>
            @error('id_program')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label for="is_active" class="form-label fw-semibold mb-2">
                Status <span class="text-danger">*</span>
            </label>
            <select 
                id="is_active" 
                name="is_active" 
                required
                class="form-select rounded-3 @error('is_active') is-invalid @enderror"
            >
                <option value="1" {{ old('is_active') == '1' ? 'selected' : '' }}>Aktif</option>
                <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Nonaktif</option>
            </select>
            @error('is_active')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label for="kode_kegiatankegitan" class="form-label fw-semibold mb-2">
                Kode Kegiatan <span class="text-danger">*</span>
            </label>
            <input 
                type="text" 
                id="kode_kegiatankegitan" 
                name="kode_kegiatankegitan" 
                value="{{ old('kode_kegiatankegitan') }}"
                placeholder="Contoh: 1.02.01"
                required
                class="form-control rounded-3 @error('kode_kegiatankegitan') is-invalid @enderror"
            >
            @error('kode_kegiatankegitan')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-8">
            <label for="nama_kegiatankegitan" class="form-label fw-semibold mb-2">
                Nama Kegiatan <span class="text-danger">*</span>
            </label>
            <input 
                type="text" 
                id="nama_kegiatankegitan" 
                name="nama_kegiatankegitan" 
                value="{{ old('nama_kegiatankegitan') }}"
                placeholder="Masukkan nama kegiatan"
                required
                class="form-control rounded-3 @error('nama_kegiatankegitan') is-invalid @enderror"
            >
            @error('nama_kegiatankegitan')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12">
            <label for="keterangan" class="form-label fw-semibold mb-2">
                Keterangan
            </label>
            <textarea 
                id="keterangan" 
                name="keterangan" 
                rows="3"
                placeholder="Catatan tambahan (opsional)"
                class="form-control rounded-3 @error('keterangan') is-invalid @enderror"
            >{{ old('keterangan') }}</textarea>
            @error('keterangan')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex justify-content-end gap-3 border-top pt-4 mt-2">
            <a href="{{ route('master.kegiatankegitan.index') }}" class="btn btn-outline-secondary rounded-3 px-4">
                <i class="bi bi-arrow-left me-2"></i>
                Batal
            </a>
            <button type="submit" class="btn btn-primary rounded-3 px-4">
                <i class="bi bi-check-lg me-2"></i>
                Simpan
            </button>
        </div>
    </form>
</x-form.card-form>
@endsection