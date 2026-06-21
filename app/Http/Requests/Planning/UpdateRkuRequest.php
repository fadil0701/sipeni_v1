<?php

namespace App\Http\Requests\Planning;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRkuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rkuId = $this->route('rku');

        return [
            'id_unit_kerja' => ['sometimes', 'required', 'exists:master_unit_kerja,id_unit_kerja'],
            'tahun_anggaran' => ['sometimes', 'required', 'digits:4', 'integer', 'min:2020', 'max:2100'],
            'jenis_rku' => ['sometimes', 'nullable', 'in:BARANG,JASA,MODAL'],
            'id_rekening_belanja' => ['nullable', 'exists:master_rekening_belanja,id'],
            'priority' => ['nullable', 'in:normal,urgent,cito'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'details' => ['sometimes', 'required', 'array', 'min:1'],
            'details.*.id_rku_detail' => [
                'nullable',
                'integer',
                Rule::exists('rku_detail', 'id_rku_detail')->where('id_rku', $rkuId),
            ],
            'details.*.jenis_rku' => ['required_with:details', 'in:BARANG,JASA,MODAL'],
            'details.*.id_data_barang' => ['nullable', 'exists:master_data_barang,id_data_barang'],
            'details.*.nama_item' => ['required_with:details', 'string', 'max:255'],
            'details.*.qty_rencana' => ['required_with:details', 'numeric', 'min:0.0001', 'max:999999.9999'],
            'details.*.id_satuan' => ['required_with:details', 'exists:master_satuan,id_satuan'],
            'details.*.harga_satuan_rencana' => ['required_with:details', 'numeric', 'min:0'],
            'details.*.keterangan' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'jenis_rku.required' => 'Jenis RKU wajib dipilih.',
            'details.required' => 'Minimal harus ada 1 item barang.',
            'details.min' => 'Minimal harus ada 1 item barang.',
            'details.*.jenis_rku.in' => 'Jenis item detail harus Barang, Jasa, atau Modal.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->has('details')) {
                $names = collect($this->details)
                    ->map(fn ($detail) => mb_strtolower(trim((string) ($detail['nama_item'] ?? ''))))
                    ->filter()
                    ->values()
                    ->toArray();

                if (count($names) !== count(array_unique($names))) {
                    $validator->errors()->add('details', 'Item barang/aset tidak boleh duplikat.');
                }
            }
        });
    }
}