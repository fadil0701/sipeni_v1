{{-- Penjelasan singkat: role permission vs modul user (sidebar) --}}
<div class="bg-slate-50 border border-slate-200 rounded-lg p-4 mb-6 text-sm text-slate-800">
    <p class="font-medium text-slate-900 mb-2">Dua hal yang saling melengkapi</p>
    <ul class="list-disc pl-5 space-y-1.5 text-slate-700">
        <li><strong>Hak akses di bawah</strong> menentukan route dan aksi yang diizinkan untuk siapa pun yang memakai role ini (pemeriksaan oleh sistem).</li>
        <li><strong>Modul menu di Manajemen User</strong> membatasi menu sidebar yang ditampilkan per akun. Jika sebuah modul tidak dipilih untuk user, menu terkait disembunyikan walaupun role punya permission.</li>
        <li><strong>Level akses Anda</strong> membatasi permission yang dapat dicentang: hanya hak yang sama dengan yang Anda miliki (menurut role dan data permission) yang dapat diberikan ke role lain.</li>
        <li><strong>Pairing aksi otomatis</strong>: `create` dipasangkan dengan `store`, dan `edit` dipasangkan dengan `update` agar assign role lebih aman.</li>
    </ul>
</div>
