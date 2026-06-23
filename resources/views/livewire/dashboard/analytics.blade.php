<?php

use Livewire\Volt\Component;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Tool;
use App\Models\Medicine;
use App\Enums\TransactionType;
use App\Enums\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

new class extends Component {
    public $topItems = [];
    public $topTechnicians = [];
    
    public $totalTools = 0;
    public $totalMedicines = 0;
    public $totalTransactions = 0;
    public $isTeknisi = false;

    public function mount()
    {
        $this->isTeknisi = Auth::user()->role === Role::Teknisi;
        $this->loadAnalytics();
    }

    public function loadAnalytics()
    {
        // Total stats - always global for all roles
        $this->totalTools = Tool::count();
        $this->totalMedicines = Medicine::count();

        // Determine transaction query based on role
        $transactionQuery = Transaction::where('type', TransactionType::Out);
        
        if ($this->isTeknisi) {
            // Teknisi only sees their own transactions
            $transactionQuery = $transactionQuery->where('user_id', Auth::id());
        }

        $this->totalTransactions = $transactionQuery->count();

        // Top 5 Assets (Tools + Medicines combined) by usage
        $assetsQuery = Transaction::select('asset_id', 'asset_type', DB::raw('SUM(qty) as total_qty'))
                        ->where('type', TransactionType::Out);
        
        if ($this->isTeknisi) {
            // Teknisi only sees their own assets usage
            $assetsQuery = $assetsQuery->where('user_id', Auth::id());
        }

        $assetsData = $assetsQuery
                        ->groupBy('asset_id', 'asset_type')
                        ->orderByDesc('total_qty')
                        ->limit(5)
                        ->get()
                        ->map(function ($row) {
                            $row->asset = $row->asset; // load polymorphic
                            return $row;
                        });

        $this->topItems = [
            'labels' => $assetsData->map(fn($r) => $r->asset?->name ?? 'Unknown')->toArray(),
            'data' => $assetsData->pluck('total_qty')->toArray(),
            'types' => $assetsData->map(fn($r) => $r->asset_type === Tool::class ? 'Alat' : 'Obat')->toArray(),
        ];

        // Top 5 Technicians - only show for admin and atasan
        if (!$this->isTeknisi) {
            $techsData = Transaction::select('user_id', DB::raw('SUM(qty) as total_qty'))
                            ->where('type', TransactionType::Out)
                            ->groupBy('user_id')
                            ->orderByDesc('total_qty')
                            ->limit(5)
                            ->with('user')
                            ->get();

            $this->topTechnicians = [
                'labels' => $techsData->pluck('user.name')->toArray(),
                'data' => $techsData->pluck('total_qty')->toArray(),
            ];
        }
    }
}; ?>

<div class="space-y-6">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium uppercase tracking-wider">Total Alat</p>
                    <p class="text-3xl font-bold mt-1">{{ $totalTools }}</p>
                </div>
                <div class="p-3 bg-white/20 rounded-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-emerald-100 text-sm font-medium uppercase tracking-wider">Total Obat / Chemical</p>
                    <p class="text-3xl font-bold mt-1">{{ $totalMedicines }}</p>
                </div>
                <div class="p-3 bg-white/20 rounded-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-indigo-100 text-sm font-medium uppercase tracking-wider">Total Transaksi Keluar</p>
                    <p class="text-3xl font-bold mt-1">{{ $totalTransactions }}</p>
                </div>
                <div class="p-3 bg-white/20 rounded-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 @if(!$isTeknisi) lg:grid-cols-2 @endif gap-6">
        <!-- Top Items Chart -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 min-w-0">
            <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">
                @if($isTeknisi)
                    Barang / Obat Sering Dipakai (Saya)
                @else
                    Top 5 Barang / Obat Sering Dipakai
                @endif
            </h3>
            <div class="relative h-72 w-full">
                <canvas id="itemsChart"></canvas>
            </div>
            @if(empty($topItems['labels']))
                <div class="text-center text-gray-500 mt-4 text-sm">Belum ada data transaksi barang.</div>
            @endif
        </div>

        <!-- Top Technicians Chart - Only for Admin and Atasan -->
        @if(!$isTeknisi)
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 min-w-0">
                <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Top 5 Teknisi Teraktif</h3>
                <div class="relative h-72 w-full">
                    <canvas id="techsChart"></canvas>
                </div>
                @if(empty($topTechnicians['labels']))
                    <div class="text-center text-gray-500 mt-4 text-sm">Belum ada data transaksi teknisi.</div>
                @endif
            </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            const itemsData = @json($topItems);
            const techsData = @json($topTechnicians);
            const isTeknisi = @json($isTeknisi);

            // Chart Colors
            const colors = [
                'rgba(59, 130, 246, 0.8)', // blue
                'rgba(99, 102, 241, 0.8)', // indigo
                'rgba(16, 185, 129, 0.8)', // green
                'rgba(245, 158, 11, 0.8)', // amber
                'rgba(236, 72, 153, 0.8)'  // pink
            ];

            if (itemsData.labels && itemsData.labels.length > 0) {
                // Show type (Alat/Obat) in labels
                const labelsFull = itemsData.labels.map((label, i) => {
                    const type = itemsData.types?.[i] ?? '';
                    return type ? `${label} (${type})` : label;
                });

                new Chart(document.getElementById('itemsChart'), {
                    type: 'bar',
                    data: {
                        labels: labelsFull,
                        datasets: [{
                            label: 'Jumlah Diambil (Qty)',
                            data: itemsData.data,
                            backgroundColor: colors,
                            borderRadius: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: { beginAtZero: true }
                        }
                    }
                });
            }

            // Only render technicians chart for admin and atasan
            if (!isTeknisi && techsData.labels && techsData.labels.length > 0) {
                const chartElement = document.getElementById('techsChart');
                if (chartElement) {
                    new Chart(chartElement, {
                        type: 'doughnut',
                        data: {
                            labels: techsData.labels,
                            datasets: [{
                                data: techsData.data,
                                backgroundColor: colors,
                                borderWidth: 0,
                                hoverOffset: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'right' }
                            },
                            cutout: '65%'
                        }
                    });
                }
            }
        });
    </script>
</div>
