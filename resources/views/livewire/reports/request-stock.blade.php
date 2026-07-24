<?php

use Livewire\Volt\Component;
use App\Models\StockRequest;

new class extends Component {
    public function with(): array
    {
        return [
            'reportData' => $this->getReportData(),
        ];
    }
    
    private function getReportData()
    {
        $requests = StockRequest::with(['requestBatch.user', 'asset'])
            ->get()
            ->groupBy(function ($req) {
                if (!$req->requestBatch) return 'unknown_unknown';
                $date = $req->requestBatch->created_at->format('Y-m-d');
                $userId = $req->requestBatch->user_id;
                return $date . '_' . $userId;
            })->filter(function($items, $key) {
                return $key !== 'unknown_unknown';
            });

        $reportData = [];
        
        foreach ($requests as $key => $items) {
            $firstItem = $items->first();
            $date = $firstItem->requestBatch->created_at->format('Y-m-d');
            $picName = $firstItem->requestBatch->user->name ?? 'Unknown';
            
            $itemDetails = $items->map(function ($req) {
                $assetName = $req->asset ? $req->asset->name : 'Deleted Asset';
                $unit = $req->asset ? $req->asset->unit : '';
                return "{$assetName} ({$req->qty} {$unit})";
            })->implode(', ');
            
            $reportData[] = [
                'date' => $date,
                'pic' => $picName,
                'items_count' => $items->count(),
                'details' => $itemDetails,
            ];
        }
        
        usort($reportData, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        
        return $reportData;
    }

    public function exportCsv()
    {
        $reportData = $this->getReportData();
        
        return response()->streamDownload(function () use ($reportData) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Tanggal', 'PIC', 'Jumlah Macam Barang', 'Detail Barang']);
            foreach ($reportData as $row) {
                fputcsv($file, [
                    $row['date'], 
                    $row['pic'], 
                    $row['items_count'], 
                    $row['details']
                ]);
            }
            fclose($file);
        }, 'laporan-request-stock-' . date('Y-m-d') . '.csv');
    }
}; ?>

<div>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900 border-b border-gray-200">
            <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                <h3 class="text-lg font-semibold text-gray-800">Laporan Request Stock (Digabung per PIC & Tanggal)</h3>
                <button wire:click="exportCsv" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2 shadow-sm transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export ke Excel (CSV)
                </button>
            </div>

            <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">PIC (User)</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Jumlah Macam Barang</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Detail Barang Request</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($reportData as $row)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-medium">{{ \Carbon\Carbon::parse($row['date'])->format('d M Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">{{ $row['pic'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 font-semibold">{{ $row['items_count'] }} item</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 leading-relaxed">{{ $row['details'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        <span class="font-medium text-gray-500">Belum ada data request stock.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
