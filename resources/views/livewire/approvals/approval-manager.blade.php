<?php

use Livewire\Volt\Component;
use App\Models\RequestBatch;
use App\Models\Transaction;
use App\Enums\RequestStatus;
use App\Enums\TransactionType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

new class extends Component {
    public function with(): array
    {
        return [
            'batches' => RequestBatch::with(['user', 'approver', 'stockRequests.asset'])
                            ->orderBy('created_at', 'desc')
                            ->get()
        ];
    }

    public function approve($id)
    {
        $batch = RequestBatch::with('stockRequests.asset')->findOrFail($id);

        if ($batch->status !== RequestStatus::Pending) {
            session()->flash('error', 'Request ini sudah diproses sebelumnya.');
            return;
        }

        // Check stock for all items
        foreach ($batch->stockRequests as $req) {
            if (! $req->asset || $req->asset->stock < $req->qty) {
                $name = $req->asset?->name ?? 'Unknown';
                session()->flash('error', "Stok tidak mencukupi untuk barang: {$name}. Request dibatalkan.");
                return;
            }
        }

        DB::transaction(function () use ($batch) {
            $batch->update([
                'status' => RequestStatus::Approved,
                'approved_by' => Auth::id(),
            ]);

            foreach ($batch->stockRequests as $req) {
                $req->asset->decrement('stock', $req->qty);

                Transaction::create([
                    'asset_id' => $req->asset_id,
                    'asset_type' => $req->asset_type,
                    'user_id' => $batch->user_id,
                    'type' => TransactionType::Out,
                    'qty' => $req->qty,
                    'notes' => 'Approved request batch #' . $batch->id,
                ]);

                // Check low stock and send notification
                if ($req->asset->fresh()->stock < 10) {
                    \Illuminate\Support\Facades\Log::warning("LOW STOCK ALERT: {$req->asset->name} hanya tersisa {$req->asset->fresh()->stock} {$req->asset->unit}.");
                }
            }
        });

        session()->flash('message', 'Request disetujui dan stok berhasil dikurangi.');
    }

    public function reject($id)
    {
        $batch = RequestBatch::findOrFail($id);
        
        if ($batch->status !== RequestStatus::Pending) {
            session()->flash('error', 'Request ini sudah diproses sebelumnya.');
            return;
        }

        $batch->update([
            'status' => RequestStatus::Rejected,
            'approved_by' => Auth::id(),
        ]);

        session()->flash('message', 'Request berhasil ditolak.');
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

    <div class="space-y-6">
        @forelse($batches as $batch)
            <div class="bg-white border rounded-xl shadow-sm overflow-hidden transition hover:shadow-md">
                <div class="bg-gray-50 px-6 py-4 border-b flex flex-col md:flex-row md:justify-between md:items-center">
                    <div>
                        <div class="flex items-center space-x-3">
                            <span class="font-bold text-gray-800 text-lg">#REQ-{{ str_pad($batch->id, 5, '0', STR_PAD_LEFT) }}</span>
                            @if($batch->status->value === 'pending')
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                            @elseif($batch->status->value === 'approved')
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800">Approved</span>
                            @else
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-red-100 text-red-800">Rejected</span>
                            @endif
                        </div>
                        <div class="text-sm text-gray-500 mt-1">
                            Diajukan oleh <span class="font-medium text-gray-700">{{ $batch->user->name }}</span> pada {{ $batch->created_at->format('d M Y, H:i') }}
                        </div>
                    </div>
                    
                    <div class="mt-4 md:mt-0 flex items-center space-x-3">
                        @if($batch->status->value === 'pending')
                            <button wire:click="approve({{ $batch->id }})" wire:confirm="Yakin ingin MENYETUJUI semua barang dalam request ini?" class="text-white bg-green-600 hover:bg-green-700 px-4 py-2 rounded-lg font-medium shadow-sm transition">Approve</button>
                            <button wire:click="reject({{ $batch->id }})" wire:confirm="Yakin ingin MENOLAK request ini?" class="text-white bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg font-medium shadow-sm transition">Reject</button>
                        @else
                            <span class="text-gray-500 text-sm bg-gray-100 px-3 py-1 rounded-md border">Diproses oleh {{ $batch->approver ? $batch->approver->name : '-' }}</span>
                        @endif
                    </div>
                </div>
                
                <div class="p-6">
                    <h4 class="text-sm font-semibold text-gray-600 uppercase tracking-wider mb-3">Daftar Barang Diminta</h4>
                    <div class="overflow-x-auto rounded-lg border">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipe</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Barang</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah Diminta</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sisa Stok Gudang</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($batch->stockRequests as $req)
                                    <tr>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $req->asset_type === 'App\\Models\\Tool' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                                {{ $req->asset_type === 'App\\Models\\Tool' ? 'Alat' : 'Obat' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">{{ $req->asset?->name ?? 'Deleted' }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-bold text-gray-700">{{ $req->qty }} {{ $req->asset?->unit ?? '' }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold {{ ($req->asset?->stock ?? 0) < $req->qty ? 'text-red-600' : 'text-green-600' }}">
                                            {{ $req->asset?->stock ?? 0 }} {{ $req->asset?->unit ?? '' }}
                                            @if(($req->asset?->stock ?? 0) < $req->qty && $batch->status->value === 'pending')
                                                <span class="ml-2 text-xs text-red-500 italic">(Stok Kurang)</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    @if($batch->notes)
                        <div class="mt-4 p-4 bg-yellow-50 border border-yellow-100 rounded-lg text-sm text-yellow-800">
                            <span class="font-semibold block mb-1">Catatan Teknisi:</span>
                            {{ $batch->notes }}
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-12 text-gray-500 bg-white rounded-xl border border-dashed border-gray-300">
                Belum ada request yang perlu diproses.
            </div>
        @endforelse
    </div>
</div>
