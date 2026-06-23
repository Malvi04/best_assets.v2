<!DOCTYPE html>
<html>
<head>
    <title>New Stock Request</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <h2>Permintaan Stok Baru</h2>
    <p>Halo Admin / Atasan,</p>
    <p>Terdapat permintaan stok baru dari Teknisi. Berikut adalah detail permintaannya:</p>
    <ul>
        <li><strong>Teknisi:</strong> {{ $stockRequest->user->name }}</li>
        <li><strong>Barang:</strong> {{ $stockRequest->item->name }}</li>
        <li><strong>Jumlah Diminta:</strong> {{ $stockRequest->qty }} {{ $stockRequest->item->unit }}</li>
        <li><strong>Sisa Stok Saat Ini:</strong> {{ $stockRequest->item->stock }} {{ $stockRequest->item->unit }}</li>
        <li><strong>Catatan:</strong> {{ $stockRequest->notes ?? '-' }}</li>
    </ul>
    <p>Silakan login ke sistem untuk melakukan persetujuan (Approve) atau menolak (Reject) permintaan ini.</p>
    <p>
        <a href="{{ url('/') }}" style="display: inline-block; padding: 10px 20px; background-color: #2563eb; color: #fff; text-decoration: none; border-radius: 5px;">Login ke Sistem</a>
    </p>
    <p>Terima kasih,<br>Tim Asset Tracking Pest Control</p>
</body>
</html>
