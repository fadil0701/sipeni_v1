<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi segel dokumen</title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 0; background: #f3f4f6; color: #111; }
        .wrap { max-width: 560px; margin: 32px auto; padding: 0 16px; }
        .card {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,.08);
        }
        .ok { color: #047857; font-weight: 600; }
        .label { font-size: 12px; color: #6b7280; margin-top: 16px; }
        .mono { font-family: ui-monospace, monospace; font-size: 13px; word-break: break-all; }
        h1 { font-size: 1.25rem; margin: 0 0 8px; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <h1>Segel dokumen ditemukan</h1>
        <p class="ok">Hash konten sesuai rekaman sistem (tahap verifikasi internal).</p>

        <div class="label">Jenis dokumen</div>
        <div>{{ $seal->document_type }}</div>

        @if(!empty($seal->meta['nama_unit_kerja']))
            <div class="label">Unit kerja</div>
            <div>{{ $seal->meta['nama_unit_kerja'] }}</div>
        @endif

        <div class="label">Kode verifikasi</div>
        <div class="mono">{{ $seal->verification_code }}</div>

        <div class="label">SHA-256 (integritas konten)</div>
        <div class="mono">{{ $seal->content_hash_sha256 }}</div>

        <div class="label">Diterbitkan</div>
        <div>{{ $seal->issued_at?->timezone(config('app.timezone'))->format('d M Y H:i') }}</div>

        @php
            $roleOrder = ['kepala_unit' => 'Kepala Ruangan/Unit Kerja', 'pengurus_barang' => 'Pengurus Barang', 'kepala_pusat' => 'Kepala Pusat (Mengetahui)'];
            $sigs = $seal->signatures->sortBy(fn ($s) => array_search($s->signer_role, array_keys($roleOrder), true) ?: 99);
        @endphp
        @if($sigs->isNotEmpty())
            <div class="label" style="margin-top: 20px;">Tanda tangan elektronik (internal, per peran)</div>
            <ul style="margin: 8px 0 0; padding-left: 18px; font-size: 14px;">
                @foreach($sigs as $sig)
                    <li style="margin-bottom: 8px;">
                        <strong>{{ $roleOrder[$sig->signer_role] ?? $sig->signer_role }}</strong>:
                        @if($sig->signed_at)
                            {{ $sig->signed_at->timezone(config('app.timezone'))->format('d M Y H:i') }}
                            @if($sig->signature_hash)
                                <span class="mono" style="display:block;font-size:11px;color:#6b7280;">{{ $sig->signature_hash }}</span>
                            @endif
                        @else
                            <span style="color:#b45309;">Belum ditandatangani</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif

        <p style="margin-top: 24px; font-size: 13px; color: #6b7280;">
            Ini adalah verifikasi integritas konten yang di-segel di aplikasi. Bukan tanda tangan elektronik resmi BSrE/PSrE.
        </p>
    </div>
</div>
</body>
</html>
