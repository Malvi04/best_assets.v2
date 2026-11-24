<?php

use Livewire\Volt\Component;
use App\Models\Tool;
use App\Models\Medicine;
use App\Models\Transaction;
use App\Enums\TransactionType;
use Illuminate\Support\Carbon;

new class extends Component {
    public $startDate = '';
    public $endDate = '';
    public $search = '';

    public function with(): array
    {
        return [
            'groupedData' => $this->getGroupedData(),
        ];
    }

    private function getGroupedData()
    {
        $transactions = Transaction::with(['asset', 'user'])
            ->where('type', TransactionType::Out)
            ->when($this->startDate, function ($query) {
                $query->whereDate('created_at', '>=', $this->startDate);
            })
            ->when($this->endDate, function ($query) {
                $query->whereDate('created_at', '<=', $this->endDate);
            })
            ->when($this->search, function ($query) {
                $query->whereHasMorph('asset', [Tool::class, Medicine::class], function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('created_at', 'asc')
            ->get();

        $groupedByMonth = [];
        
        foreach ($transactions as $trx) {
            Carbon::setLocale('id'); // Ensure Indonesian month names
            $monthName = $trx->created_at->translatedFormat('F'); 
            $dateStr = $trx->created_at->format('d-M-y'); 
            $picName = $trx->user?->name ?? 'Unknown';
            $assetName = $trx->asset?->name ?? 'Deleted';
            
            // Format qty like the image if decimal (e.g. 1.5 -> 1,5 or something. I'll just keep it numeric)
            $qty = $trx->qty;

            if (!isset($groupedByMonth[$monthName])) {
                $groupedByMonth[$monthName] = [
                    'total_qty' => 0,
                    'items' => 0, 
                    'dates' => []
                ];
            }
            
            $datePicKey = $dateStr . '|' . $picName;
            if (!isset($groupedByMonth[$monthName]['dates'][$datePicKey])) {
                $groupedByMonth[$monthName]['dates'][$datePicKey] = [
                    'date' => $dateStr,
                    'pic' => $picName,
                    'items' => 0, 
                    'assets' => []
                ];
            }
            
            if (!isset($groupedByMonth[$monthName]['dates'][$datePicKey]['assets'][$assetName])) {
                $groupedByMonth[$monthName]['dates'][$datePicKey]['assets'][$assetName] = 0;
                $groupedByMonth[$monthName]['dates'][$datePicKey]['items']++;
                $groupedByMonth[$monthName]['items']++;
            }
            
            $groupedByMonth[$monthName]['dates'][$datePicKey]['assets'][$assetName] += $qty;
            $groupedByMonth[$monthName]['total_qty'] += $qty;
        }

        return $groupedByMonth;
    }

    public function exportExcel()
    {
        $groupedData = $this->getGroupedData();
        $html = view('exports.stock-out', ['groupedData' => $groupedData])->render();
        
        return response()->streamDownload(function () use ($html) {
            echo $html;
        }, 'Laporan-Stock-Keluar-' . date('Y-m-d') . '.xls', [
            'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
        ]);
    }
}; ?>

<div>
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-xl font-bold text-gray-800">Laporan Stock Keluar</h3>
        <button wire:click="exportExcel" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2 shadow-sm transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Export ke Excel
        </button>
    </div>

    <div class="flex flex-col md:flex-row gap-4 mb-6 items-end">
        <div>
            <x-input-label for="startDateOut" value="Dari Tanggal" />
            <x-text-input wire:model.live="startDate" id="startDateOut" type="date" class="mt-1 block w-full text-sm" />
        </div>
        <div>
            <x-input-label for="endDateOut" value="Sampai Tanggal" />
            <x-text-input wire:model.live="endDate" id="endDateOut" type="date" class="mt-1 block w-full text-sm" />
        </div>
        <div>
            <x-input-label for="searchOut" value="Cari Nama Barang" />
            <x-text-input wire:model.live.debounce.300ms="search" id="searchOut" type="text" class="mt-1 block w-full text-sm" placeholder="Ketik kata kunci..." />
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 border-collapse border border-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3 border border-gray-200 text-center text-sm font-bold text-gray-800 uppercase">Bulan</th>
                        <th class="px-4 py-3 border border-gray-200 text-left text-sm font-bold text-gray-800 uppercase">Nama Barang</th>
                        <th class="px-4 py-3 border border-gray-200 text-center text-sm font-bold text-gray-800 uppercase">Total</th>
                        <th class="px-4 py-3 border border-gray-200 text-center text-sm font-bold text-gray-800 uppercase">Total barang</th>
                        <th class="px-4 py-3 border border-gray-200 text-center text-sm font-bold text-gray-800 uppercase">Tgl pengambilan</th>
                        <th class="px-4 py-3 border border-gray-200 text-center text-sm font-bold text-gray-800 uppercase">PIC</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 text-sm text-gray-700">
                    @forelse($groupedData as $monthName => $monthData)
                        @php $isFirstRowMonth = true; @endphp
                        @foreach($monthData['dates'] as $dateData)
                            @php $isFirstRowDate = true; @endphp
                            @foreach($dateData['assets'] as $assetName => $qty)
                                <tr class="hover:bg-gray-50 transition">
                                    @if($isFirstRowMonth)
                                        <td rowspan="{{ $monthData['items'] }}" class="px-4 py-3 border border-gray-200 text-center font-bold text-gray-900 bg-gray-50 align-top pt-4">
                                            {{ $monthName }}
                                        </td>
                                    @endif
                                    
                                    <td class="px-4 py-3 border border-gray-200">{{ $assetName }}</td>
                                    <td class="px-4 py-3 border border-gray-200 text-center">{{ $qty }}</td>
                                    
                                    @if($isFirstRowMonth)
                                        <td rowspan="{{ $monthData['items'] }}" class="px-4 py-3 border border-gray-200 text-center font-bold align-top pt-4">
                                            {{ str_replace('.', ',', $monthData['total_qty']) }}
                                        </td>
                                    @endif
                                    
                                    @if($isFirstRowDate)
                                        <td rowspan="{{ $dateData['items'] }}" class="px-4 py-3 border border-gray-200 text-center align-top pt-4">
                                            {{ $dateData['date'] }}
                                        </td>
                                        <td rowspan="{{ $dateData['items'] }}" class="px-4 py-3 border border-gray-200 text-center align-top pt-4">
                                            {{ $dateData['pic'] }}
                                        </td>
                                    @endif
                                </tr>
                                @php 
                                    $isFirstRowMonth = false; 
                                    $isFirstRowDate = false;
                                @endphp
                            @endforeach
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500 border border-gray-200">Belum ada data barang keluar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
