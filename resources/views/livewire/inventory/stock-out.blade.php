<?php

use Livewire\Volt\Component;
use App\Models\Transaction;
use App\Enums\TransactionType;

new class extends Component {
    public function with(): array
    {
        return [
            'transactions' => Transaction::with(['asset', 'user'])
                ->where('type', TransactionType::Out)
                ->orderByDesc('created_at')
                ->limit(100)
                ->get()
        ];
    }
}; ?>

<div>
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-xl font-bold text-gray-800">Riwayat Barang Keluar</h3>
    </div>

    <div class="overflow-x-auto rounded-lg shadow ring-1 ring-black ring-opacity-5">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Tanggal</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Nama Barang</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Jumlah Barang</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Remarks</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Nama Teknisi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($transactions as $trx)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $trx->created_at->format('d M Y, H:i') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $trx->asset?->name ?? 'Deleted' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $trx->qty }} {{ $trx->asset?->unit ?? '' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $trx->notes ?? $trx->source ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $trx->user?->name ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">Belum ada data barang keluar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
