<?php

namespace App\Http\Requests\Planning;

use Illuminate\Foundation\Http\FormRequest;

class CreateRkuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'tahun_anggaran' => (string) ((int) date('Y') + 2),
        ]);
    }

    public function rules(): array
    {
        $tahunOtomatis = (int) date('Y') + 2;

        return [
            'id_unit_kerja' => ['required', 'exists:master_unit_kerja,id_unit_kerja'],
            'tahun_anggaran' => ['required', 'digits:4', 'integer', 'in:'.$tahunOtomatis],
            'jenis_rku' => ['nullable', 'in:BARANG,JASA,MODAL'],
            'id_rekening_belanja' => ['nullable', 'exists:master_rekening_belanja,id'],
            'priority' => ['nullable', 'in:normal,urgent,cito'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.jenis_rku' => ['required', 'in:BARANG,JASA,MODAL'],
            'details.*.id_data_barang' => ['nullable', 'exists:master_data_barang,id_data_barang'],
            'details.*.nama_item' => ['required', 'string', 'max:255'],
            'details.*.qty_rencana' => ['required', 'numeric', 'min:0.0001', 'max:999999.9999'],
            'details.*.id_satuan' => ['required', 'exists:master_satuan,id_satuan'],
            'details.*.harga_satuan_rencana' => ['required', 'numeric', 'min:0'],
            'details.*.keterangan' => ['nullable', 'string', 'max:500'],
            'details.*.foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_unit_kerja.required' => 'Unit kerja wajib dipilih.',
            'tahun_anggaran.required' => 'Tahun anggaran wajib diisi.',
            'tahun_anggaran.in' => 'Tahun anggaran harus otomatis (tahun berjalan + 2).',
            'jenis_rku.required' => 'Jenis RKU wajib dipilih.',
            'details.required' => 'Minimal harus ada 1 item barang.',
            'details.min' => 'Minimal harus ada 1 item barang.',
            'details.*.jenis_rku.required' => 'Jenis item wajib dipilih pada setiap detail.',
            'details.*.nama_item.required' => 'Nama barang/aset wajib diisi pada setiap item.',
            'details.*.qty_rencana.required' => 'Jumlah rencana wajib diisi.',
            'details.*.qty_rencana.min' => 'Jumlah harus lebih dari 0.',
            'details.*.id_satuan.required' => 'Satuan wajib dipilih.',
            'details.*.harga_satuan_rencana.required' => 'Harga satuan wajib diisi.',
            'details.*.jenis_rku.in' => 'Jenis item detail harus Barang, Jasa, Modal.',
            'details.*.foto.image' => 'File foto harus berupa gambar.',
            'details.*.foto.max' => 'Ukuran foto maksimal 5 MB.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $names = collect($this->details ?? [])
                ->map(fn ($detail) => mb_strtolower(trim((string) ($detail['nama_item'] ?? ''))))
                ->filter()
                ->values()
                ->toArray();

            if (count($names) !== count(array_unique($names))) {
                $validator->errors()->add('details', 'Item barang/aset tidak boleh duplikat.');
            }
        });
    }
}
