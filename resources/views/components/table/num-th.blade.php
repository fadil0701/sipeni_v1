{{-- Header nomor urut: teks harus tepat "No" (case-insensitive) agar layouts/app enhanceTable bisa menyelaraskan angka setelah sort. --}}
<th {{ $attributes->merge(['class' => 'no-sort px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-0 whitespace-nowrap']) }}>No</th>
