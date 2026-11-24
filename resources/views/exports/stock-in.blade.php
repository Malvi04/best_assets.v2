<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
    <table border="1" cellpadding="3" cellspacing="0" style="border-collapse: collapse; text-align: center; vertical-align: middle;">
        <thead>
            <tr>
                <th style="font-weight: bold; text-align: center; vertical-align: middle;">Tanggal</th>
                <th style="font-weight: bold; text-align: center; vertical-align: middle;">Tipe</th>
                <th style="font-weight: bold; text-align: left; vertical-align: middle;">Nama Barang</th>
                <th style="font-weight: bold; text-align: center; vertical-align: middle;">Qty</th>
                <th style="font-weight: bold; text-align: left; vertical-align: middle;">Sumber</th>
                <th style="font-weight: bold; text-align: center; vertical-align: middle;">Dicatat Oleh</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $trx)
                <tr>
                    <td style="text-align: center; vertical-align: middle;">{{ $trx->created_at->format('d M Y, H:i') }}</td>
                    <td style="text-align: center; vertical-align: middle;">{{ $trx->asset_type === 'App\Models\Tool' ? 'Alat' : 'Obat' }}</td>
                    <td style="text-align: left; vertical-align: middle;">{{ $trx->asset?->name ?? 'Deleted' }}</td>
                    <td style="text-align: center; vertical-align: middle;">{{ $trx->qty }} {{ $trx->asset?->unit ?? '' }}</td>
                    <td style="text-align: left; vertical-align: middle;">{{ $trx->source ?? '-' }}</td>
                    <td style="text-align: center; vertical-align: middle;">{{ $trx->user?->name ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
