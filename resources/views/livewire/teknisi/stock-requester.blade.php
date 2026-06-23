<?php

use Livewire\Volt\Component;
use App\Models\Tool;
use App\Models\Medicine;
use App\Models\RequestBatch;
use App\Models\StockRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Enums\Role;
use Illuminate\Support\Facades\DB;

new class extends Component {
    // Cart for multiple items
    public $cart = [];
    
    public $asset_type = '';
    public $asset_id = '';
    public $qty = 1;
    public $notes = '';

    public function with(): array
    {
        return [
            'tools' => Tool::orderBy('name')->get(),
            'medicines' => Medicine::orderBy('name')->get(),
            'batches' => RequestBatch::with(['approver', 'stockRequests.asset'])
                            ->where('user_id', Auth::id())
                            ->orderBy('created_at', 'desc')
                            ->get()
        ];
    }

    public function addToCart()
    {
        $this->validate([
            'asset_type' => 'required|in:tool,medicine',
            'asset_id' => 'required|integer',
            'qty' => 'required|integer|min:1',
        ]);

        $modelClass = $this->asset_type === 'tool' ? Tool::class : Medicine::class;
        $asset = $modelClass::find($this->asset_id);

        if (! $asset) {
            session()->flash('error', 'Barang tidak ditemukan.');
            return;
        }

        // Check if asset already in cart
        $existingIndex = collect($this->cart)->search(function ($cartItem) use ($modelClass) {
            return $cartItem['asset_id'] == $this->asset_id && $cartItem['asset_type'] === $modelClass;
        });

        if ($existingIndex !== false) {
            $this->cart[$existingIndex]['qty'] += $this->qty;
        } else {
            $this->cart[] = [
                'asset_id' => $asset->id,
                'asset_type' => $modelClass,
                'type_label' => $this->asset_type === 'tool' ? 'Alat' : 'Obat',
                'name' => $asset->name,
                'qty' => $this->qty,
                'unit' => $asset->unit,
            ];
        }

        $this->reset(['asset_type', 'asset_id', 'qty']);
        $this->qty = 1;
    }

    public function removeFromCart($index)
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
    }

    public function submitRequest()
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Keranjang request kosong.');
            return;
        }

        DB::transaction(function () {
            $batch = RequestBatch::create([
                'user_id' => Auth::id(),
                'notes' => $this->notes,
            ]);

            foreach ($this->cart as $cartItem) {
                StockRequest::create([
                    'request_batch_id' => $batch->id,
                    'asset_id' => $cartItem['asset_id'],
                    'asset_type' => $cartItem['asset_type'],
                    'qty' => $cartItem['qty'],
                ]);
            }
        });

        $this->cart = [];
        $this->reset(['notes']);
        
        
        session()->flash('message', 'Request stok berhasil dibuat dan menunggu persetujuan Admin.');
    }
}; ?>

<div>
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Form Add to Cart -->
        <div class="lg:col-span-1 p-6 border rounded-xl bg-gradient-to-br from-white to-blue-50 shadow-sm">
            <h3 class="text-lg font-bold text-blue-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Pilih Barang / Obat
            </h3>
            <form wire:submit="addToCart" class="space-y-4">
                <div>
                    <x-input-label for="asset_type" value="Tipe Barang" />
                    <select wire:model.live="asset_type" id="asset_type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                        <option value="">-- Pilih Tipe --</option>
                        <option value="tool">🔧 Alat</option>
                        <option value="medicine">💊 Obat / Chemical</option>
                    </select>
                </div>
                <div>
                    <x-input-label for="asset_id" value="Pilih Barang" />
                    <select wire:model="asset_id" id="asset_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                        <option value="">-- Pilih Barang --</option>
                        @if($asset_type === 'tool')
                            @foreach($tools as $tool)
                                <option value="{{ $tool->id }}">{{ $tool->name }} (Stok: {{ $tool->stock }} {{ $tool->unit }})</option>
                            @endforeach
                        @elseif($asset_type === 'medicine')
                            @foreach($medicines as $med)
                                <option value="{{ $med->id }}">{{ $med->name }} (Stok: {{ $med->stock }} {{ $med->unit }})</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div>
                    <x-input-label for="qty" value="Jumlah" />
                    <x-text-input wire:model="qty" id="qty" type="number" min="1" class="mt-1 block w-full" required />
                </div>
                <div class="pt-2">
                    <x-secondary-button type="submit" class="w-full justify-center">Tambah ke Keranjang</x-secondary-button>
                </div>
            </form>
        </div>

        <!-- Cart Display -->
        <div class="lg:col-span-2 p-6 border rounded-xl bg-white shadow-sm">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                Keranjang Request
            </h3>
            
            @if(empty($this->cart))
                <div class="text-center py-8 text-gray-500 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                    Keranjang masih kosong. Silakan pilih barang di samping.
                </div>
            @else
                <div class="overflow-x-auto mb-4 border rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barang</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($cart as $index => $cartItem)
                                <tr>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $cartItem['type_label'] === 'Alat' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                            {{ $cartItem['type_label'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">{{ $cartItem['name'] }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $cartItem['qty'] }} {{ $cartItem['unit'] }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                        <button wire:click="removeFromCart({{ $index }})" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-2 py-1 rounded">Batal</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="space-y-4">
                    <div>
                        <x-input-label for="notes" value="Catatan Tambahan (Opsional)" />
                        <x-text-input wire:model="notes" id="notes" type="text" class="mt-1 block w-full bg-gray-50" placeholder="Misal: Untuk pemeliharaan area utara" />
                    </div>
                    <x-primary-button wire:click="submitRequest" class="w-full justify-center bg-blue-600 hover:bg-blue-700 h-10">Kirim Request Ke Admin</x-primary-button>
                </div>
            @endif
        </div>
    </div>

    <h3 class="text-xl font-bold text-gray-800 mb-4">Riwayat Request Saya</h3>
    <div class="space-y-4">
        @forelse($batches as $batch)
            <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
                <div class="bg-gray-50 px-6 py-4 border-b flex flex-col md:flex-row md:justify-between md:items-center">
                    <div>
                        <span class="text-sm text-gray-500">Nomor Request:</span>
                        <span class="font-bold text-gray-800 ml-1">#REQ-{{ str_pad($batch->id, 5, '0', STR_PAD_LEFT) }}</span>
                        <div class="text-xs text-gray-500 mt-1">{{ $batch->created_at->format('d M Y, H:i') }}</div>
                    </div>
                    <div class="mt-2 md:mt-0 flex items-center space-x-4">
                        @if($batch->status->value === 'pending')
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                        @elseif($batch->status->value === 'approved')
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800">Approved</span>
                        @else
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-red-100 text-red-800">Rejected</span>
                        @endif
                    </div>
                </div>
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead>
                            <tr>
                                <th class="text-left text-xs font-medium text-gray-500 uppercase pb-3">Tipe</th>
                                <th class="text-left text-xs font-medium text-gray-500 uppercase pb-3">Barang</th>
                                <th class="text-left text-xs font-medium text-gray-500 uppercase pb-3">Jumlah Diminta</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($batch->stockRequests as $req)
                                <tr>
                                    <td class="py-3 text-sm">
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $req->asset_type === 'App\\Models\\Tool' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                            {{ $req->asset_type === 'App\\Models\\Tool' ? 'Alat' : 'Obat' }}
                                        </span>
                                    </td>
                                    <td class="py-3 text-sm text-gray-800">{{ $req->asset?->name ?? 'Deleted' }}</td>
                                    <td class="py-3 text-sm text-gray-600">{{ $req->qty }} {{ $req->asset?->unit ?? '' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if($batch->notes)
                        <div class="mt-4 text-sm text-gray-500 bg-gray-50 p-3 rounded-md border border-gray-100">
                            <strong>Catatan:</strong> {{ $batch->notes }}
                        </div>
                    @endif
                    @if($batch->status->value !== 'pending')
                        <div class="mt-2 text-xs text-gray-400">
                            Diproses oleh: {{ $batch->approver ? $batch->approver->name : '-' }} pada {{ $batch->updated_at->format('d M Y, H:i') }}
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-8 text-gray-500 bg-white rounded-xl border border-dashed border-gray-300">
                Belum ada riwayat request.
            </div>
        @endforelse
    </div>
</div>
