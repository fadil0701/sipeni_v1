<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventarisasi BMD</title>
    <style>
        :root {
            --border: #111827;
            --text: #111827;
            --muted: #4b5563;
        }
        body {
            margin: 0;
            padding: 24px;
            background: #f3f4f6;
            font-family: Arial, sans-serif;
            color: var(--text);
        }
        .actions {
            max-width: 480px;
            margin: 0 auto 12px;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }
        .btn {
            border: 1px solid #d1d5db;
            background: #fff;
            color: #111827;
            padding: 8px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
        }
        .card {
            width: 420px;
            max-width: 100%;
            margin: 0 auto;
            background: #fff;
            border: 3px solid var(--border);
            padding: 20px 18px;
            text-align: center;
            box-sizing: border-box;
        }
        .title {
            font-size: 22px;
            font-weight: 800;
            line-height: 1.2;
            margin: 8px 0 14px;
        }
        .subtitle {
            font-size: 13px;
            color: var(--muted);
            margin-bottom: 6px;
        }
        .qr-wrap {
            margin: 8px auto 14px;
            width: 220px;
            height: 220px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #d1d5db;
            background: #fff;
            overflow: hidden;
        }
        .qr-wrap img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .year {
            font-size: 42px;
            font-weight: 800;
            margin: 4px 0 10px;
            letter-spacing: 1px;
        }
        .reg {
            font-size: 22px;
            font-weight: 700;
            margin-top: 2px;
            word-break: break-word;
        }
        .name {
            font-size: 14px;
            color: #374151;
            margin-top: 10px;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .actions { display: none; }
            .card { border-width: 2px; width: 100%; max-width: 100%; }
        }
    </style>
</head>
<body>
    <div class="actions">
        <a class="btn" href="{{ route('inventory.inventory-item.edit', $inventoryItem->id_item) }}">Kembali</a>
        <button class="btn" id="downloadPngBtn" type="button">Download PNG</button>
        <button class="btn" id="downloadJpegBtn" type="button">Download JPEG</button>
        <a class="btn" href="{{ route('inventory.inventory-item.template-qr.download', $inventoryItem->id_item) }}">Download SVG Template</a>
        <button class="btn" onclick="window.print()">Print</button>
    </div>

    <div class="card">
        <div class="subtitle">Inventarisasi BMD</div>
        <div class="title">Pusat Pelayanan Kesehatan Pegawai</div>

        <div class="qr-wrap">
            <img src="{{ $inventoryItem->qrCodePublicUrl() }}" alt="QR Code {{ $inventoryItem->kode_register }}">
        </div>

        <div class="year">Tahun {{ $tahun }}</div>
        <div class="reg">Kode Reg: {{ $inventoryItem->kode_register }}</div>
        <div class="name">{{ $inventoryItem->inventory->dataBarang->nama_barang ?? 'Item Inventory' }}</div>
    </div>

    <script>
        const qrUrl = @json($inventoryItem->qrCodePublicUrl());
        const tahun = @json((string) $tahun);
        const kodeReg = @json((string) $inventoryItem->kode_register);
        const namaBarang = @json((string) ($inventoryItem->inventory->dataBarang->nama_barang ?? 'Item Inventory'));

        async function renderTemplateToCanvas() {
            const canvas = document.createElement('canvas');
            canvas.width = 900;
            canvas.height = 1300;
            const ctx = canvas.getContext('2d');

            // background
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.lineWidth = 6;
            ctx.strokeStyle = '#111111';
            ctx.strokeRect(8, 8, 884, 1284);

            // titles
            ctx.fillStyle = '#111111';
            ctx.textAlign = 'center';
            ctx.font = '700 70px Arial';
            ctx.fillText('Inventarisasi BMD', 450, 150);
            ctx.font = '700 58px Arial';
            ctx.fillText('Puspelkes DKI Jakarta', 450, 230);

            // QR image
            const qrImg = new Image();
            qrImg.crossOrigin = 'anonymous';
            await new Promise((resolve, reject) => {
                qrImg.onload = resolve;
                qrImg.onerror = reject;
                qrImg.src = qrUrl;
            });
            ctx.drawImage(qrImg, 220, 300, 460, 460);

            // footer texts
            ctx.font = '800 74px Arial';
            ctx.fillText(`Tahun ${tahun}`, 450, 940);
            ctx.font = '700 46px Arial';
            ctx.fillText(`Kode Reg : ${kodeReg}`, 450, 1040);
            ctx.font = '500 34px Arial';
            ctx.fillText(namaBarang, 450, 1110);

            return canvas;
        }

        function fileSafeName(input) {
            return String(input || 'template-qr').replace(/[\\/:*?"<>| ]+/g, '_');
        }

        async function downloadCanvas(mimeType, ext) {
            try {
                const canvas = await renderTemplateToCanvas();
                const fileName = `${fileSafeName(kodeReg)}_template.${ext}`;
                canvas.toBlob((blob) => {
                    if (!blob) return;
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = fileName;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    URL.revokeObjectURL(url);
                }, mimeType, ext === 'jpeg' ? 0.92 : undefined);
            } catch (err) {
                alert('Gagal membuat file image template. Pastikan QR code dapat dimuat.');
                console.error(err);
            }
        }

        document.getElementById('downloadPngBtn')?.addEventListener('click', () => {
            downloadCanvas('image/png', 'png');
        });
        document.getElementById('downloadJpegBtn')?.addEventListener('click', () => {
            downloadCanvas('image/jpeg', 'jpeg');
        });
    </script>
</body>
</html>

