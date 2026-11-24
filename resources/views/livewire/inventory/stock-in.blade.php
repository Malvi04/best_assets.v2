<?php

use Livewire\Volt\Component;
use App\Models\Tool;
use App\Models\Medicine;
use App\Models\Transaction;
use App\Enums\TransactionType;
use Illuminate\Support\Facades\DB;

new class extends Component {
    public $asset_type = '';
    public $asset_id = '';
    public $qty = 1;
    public $source = '';
    public $notes = '';

    public $startDate = '';
    public $endDate = '';
    public $search = '';

    private function getTransactionsQuery()
    {
        return Transaction::with(['asset', 'user'])
            ->where('type', TransactionType::In)
            ->when($this->startDate, function ($query) {
                $query->whereDate('created_at', '>=', $this->startDate);
            })
            ->when($this->endDate, function ($query) {
                $query->whereDate('created_at', '<=', $this->endDate);
            })
            ->when($this->search, function ($query) {
                $query->whereHasMorph('asset', [Tool::class, Medicine::class], function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                })->orWhere('source', 'like', '%' . $this->search . '%');
            })
            ->orderByDesc('created_at');
    }

    public function with(): array
    {
        return [
            'transactions' => $this->getTransactionsQuery()->limit(100)->get()
        ];
    }

    public function getToolsProperty()
    {
        return Tool::orderBy('name')->get();
    }

    public function getMedicinesProperty()
    {
        return Medicine::orderBy('name')->get();
    }

    public function save()
    {
        $this->validate([
            'asset_type' => 'required|in:tool,medicine',
            'asset_id' => 'required|integer',
            'qty' => 'required|integer|min:1',
            'source' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $modelClass = $this->asset_type === 'tool' ? Tool::class : Medicine::class;
        $asset = $modelClass::find($this->asset_id);

        if (! $asset) {
            session()->flash('error', 'Barang tidak ditemukan.');
            return;
        }

        DB::transaction(function () use ($asset, $modelClass) {
            Transaction::create([
                'asset_id' => $asset->id,
                'asset_type' => $modelClass,
                'user_id' => auth()->id(),
                'type' => TransactionType::In,
                'qty' => $this->qty,
                'source' => $this->source,
                'notes' => $this->notes,
            ]);

            $asset->increment('stock', $this->qty);
        });

        $this->reset(['asset_type', 'asset_id', 'qty', 'source', 'notes']);
        $this->qty = 1;
        $this->dispatch('close-modal');
        session()->flash('message', 'Stok masuk berhasil dicatat.');
    }

    public function exportExcel()
    {
        $transactions = $this->getTransactionsQuery()->get();
        $html = view('exports.stock-in', ['transactions' => $transactions])->render();
        
        return response()->streamDownload(function () use ($html) {
            echo $html;
        }, 'Laporan-Stock-Masuk-' . date('Y-m-d') . '.xls', [
            'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
        ]);
    }
}; ?>

<div x-data="{ showModal: false }" @close-modal.window="showModal = false">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-bold text-gray-800">Riwayat Stok Masuk</h3>
        <button @click="showModal = true" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow-sm transition flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah Stok Masuk
        </button>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 p-4 text-green-700 bg-green-100 rounded-lg shadow-sm border border-green-200">
            {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 p-4 text-red-700 bg-red-100 rounded-lg shadow-sm border border-red-200">
            {{ session('error') }}
        </div>
    @endif

    <!-- Pop‑up Modal -->
    <div x-show="showModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showModal = false" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="showModal" class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form wire:submit="save">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">Catat Stok Masuk</h3>
                        <div class="space-y-4">
                            <div>
                                <x-input-label for="asset_type" value="Tipe Barang" />
                                <select wire:model.live="asset_type" id="asset_type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="">-- Pilih Tipe --</option>
                                    <option value="tool"> Alat</option>
                                    <option value="medicine"> Obat / Chemical</option>
                                </select>
                                <x-input-error :messages="$errors->get('asset_type')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="asset_id" value="Pilih Barang" />
                                <select wire:model="asset_id" id="asset_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="">-- Pilih Barang --</option>
                                    @if($asset_type === 'tool')
                                        @foreach($this->tools as $tool)
                                            <option value="{{ $tool->id }}">{{ $tool->name }} (Stok: {{ $tool->stock }})</option>
                                        @endforeach
                                    @elseif($asset_type === 'medicine')
                                        @foreach($this->medicines as $med)
                                            <option value="{{ $med->id }}">{{ $med->name }} (Stok: {{ $med->stock }})</option>
                                        @endforeach
                                    @endif
                                </select>
                                <x-input-error :messages="$errors->get('asset_id')" class="mt-2" />
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="qty" value="Jumlah Masuk" />
                                    <x-text-input wire:model="qty" id="qty" type="number" min="1" class="mt-1 block w-full" required />
                                    <x-input-error :messages="$errors->get('qty')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="source" value="Sumber Barang" />
                                    <x-text-input wire:model="source" id="source" type="text" class="mt-1 block w-full" placeholder="Vendor / Pembelian" required />
                                    <x-input-error :messages="$errors->get('source')" class="mt-2" />
                                </div>
                            </div>
                            <div>
                                <x-input-label for="notes" value="Catatan (Opsional)" />
                                <textarea wire:model="notes" id="notes" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Simpan
                        </button>
                        <button type="button" @click="showModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-sm overflow-hidden">
        <div class="flex flex-col md:flex-row gap-4 mb-6 justify-between items-end">
            <div class="flex gap-4 items-end w-full md:w-auto">
                <div>
                    <x-input-label for="startDate" value="Dari Tanggal" />
                    <x-text-input wire:model.live="startDate" id="startDate" type="date" class="mt-1 block w-full text-sm" />
                </div>
                <div>
                    <x-input-label for="endDate" value="Sampai Tanggal" />
                    <x-text-input wire:model.live="endDate" id="endDate" type="date" class="mt-1 block w-full text-sm" />
                </div>
                <div>
                    <x-input-label for="search" value="Cari Barang/Sumber" />
                    <x-text-input wire:model.live.debounce.300ms="search" id="search" type="text" class="mt-1 block w-full text-sm" placeholder="Ketik kata kunci..." />
                </div>
            </div>
            
            <button wire:click="exportExcel" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2 shadow-sm transition whitespace-nowrap">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export Excel
            </button>
        </div>

        <div class="overflow-x-auto rounded-lg shadow ring-1 ring-black ring-opacity-5">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Tanggal</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Tipe</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Nama Barang</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Qty</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Sumber</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Dicatat Oleh</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($transactions as $trx)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $trx->created_at->format('d M Y, H:i') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $trx->asset_type === 'App\\Models\\Tool' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $trx->asset_type === 'App\\Models\\Tool' ? 'Alat' : 'Obat' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $trx->asset?->name ?? 'Deleted' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">+{{ $trx->qty }} {{ $trx->asset?->unit ?? '' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $trx->source ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $trx->user?->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">Belum ada data stok masuk.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>
