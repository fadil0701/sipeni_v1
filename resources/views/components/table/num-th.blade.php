{{-- Header nomor urut: teks harus tepat "No" (case-insensitive) agar layouts/app enhanceTable bisa menyelaraskan angka setelah sort. --}}
<th {{ $attributes->merge(['class' => 'no-sort px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-14']) }}>No</th>
