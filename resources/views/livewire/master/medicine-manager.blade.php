<?php

use Livewire\Volt\Component;
use Livewire\WithPagination; // <-- 1. Import trait paginasi
use App\Models\Medicine;
use App\Models\Category;

new class extends Component {
    use WithPagination;

    public $perPage = 10;

    public $editingMedicineId = null;
    public $category_id = '';
    public $name = '';
    public $unit = '';
    public $description = '';
    public $initial_stock = 0;

    public function with(): array
    {
        return [
            'categories' => Category::orderBy('name')->get(),
            'medicines' => Medicine::with('category')
                ->orderByDesc('created_at')
                ->paginate($this->perPage)
        ];
    }

    public function updatingPerPage($value)
    {
        $this->resetPage();
    }

    public function resetForm()
    {
        $this->editingMedicineId = null;
        $this->reset(['category_id', 'name', 'unit', 'description', 'initial_stock']);
    }

    public function edit($id)
    {
        $medicine = Medicine::findOrFail($id);
        $this->editingMedicineId = $id;
        $this->category_id = $medicine->category_id;
        $this->name = $medicine->name;
        $this->unit = $medicine->unit;
        $this->description = $medicine->description;
        $this->initial_stock = $medicine->stock;
        $this->dispatch('open-modal');
    }

    public function save()
    {
        $this->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'description' => 'nullable|string',
            'initial_stock' => 'required|integer|min:0',
        ]);

        if ($this->editingMedicineId) {
            // Update existing
            $medicine = Medicine::findOrFail($this->editingMedicineId);
            $medicine->update([
                'category_id' => $this->category_id,
                'name' => $this->name,
                'unit' => $this->unit,
                'description' => $this->description,
            ]);
            $message = 'Obat berhasil diperbarui.';
        } else {
            // Create new
            $medicine = Medicine::create([
                'category_id' => $this->category_id,
                'name' => $this->name,
                'stock' => $this->initial_stock,
                'unit' => $this->unit,
                'description' => $this->description,
            ]);

            if ($this->initial_stock > 0) {
                \App\Models\Transaction::create([
                    'asset_id' => $medicine->id,
                    'asset_type' => Medicine::class,
                    'user_id' => auth()->id(),
                    'type' => \App\Enums\TransactionType::In,
                    'qty' => $this->initial_stock,
                    'source' => 'Stok Awal',
                    'notes' => 'Pendaftaran obat baru',
                ]);
            }
            $message = 'Obat berhasil ditambahkan.';
        }

        $this->resetForm();
        $this->dispatch('close-modal');
        session()->flash('message', $message);
    }

    public function delete($id)
    {
        Medicine::findOrFail($id)->delete();
        session()->flash('message', 'Obat berhasil dihapus.');
    }
}; ?>

<div x-data="{ showModal: false }" @open-modal.window="showModal = true" @close-modal.window="showModal = false">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-bold text-gray-800">Daftar Obat / Chemical</h3>
        <button @click="showModal = true; $wire.resetForm()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow-sm transition flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah Obat Baru
        </button>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 p-4 text-green-700 bg-green-100 rounded-lg shadow-sm border border-green-200">
            {{ session('message') }}
        </div>
    @endif

    <div x-show="showModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showModal = false" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="showModal" class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form wire:submit="save">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                            @if($editingMedicineId)
                                Edit Data Obat
                            @else
                                Tambah Data Obat Baru
                            @endif
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <x-input-label for="category_id" value="Kategori" />
                                <select wire:model="category_id" id="category_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="name" value="Nama Obat" />
                                <x-text-input wire:model="name" id="name" type="text" class="mt-1 block w-full" required />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="initial_stock" value="{{ $editingMedicineId ? 'Stok Saat Ini' : 'Stok Awal' }}" />
                                    <x-text-input wire:model="initial_stock" id="initial_stock" type="number" min="0" class="mt-1 block w-full" :disabled="(bool)$editingMedicineId" :required="!$editingMedicineId" />
                                    <x-input-error :messages="$errors->get('initial_stock')" class="mt-2" />
                                    @if($editingMedicineId)
                                        <p class="text-xs text-gray-500 mt-1">Ubah stok melalui fitur Stock In/Out</p>
                                    @endif
                                </div>
                                <div>
                                    <x-input-label for="unit" value="Satuan (Unit)" />
                                    <x-text-input wire:model="unit" id="unit" type="text" class="mt-1 block w-full" placeholder="Botol, Tablet, Pcs" required />
                                    <x-input-error :messages="$errors->get('unit')" class="mt-2" />
                                </div>
                            </div>
                            <div>
                                <x-input-label for="description" value="Deskripsi (Opsional)" />
                                <textarea wire:model="description" id="description" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            @if($editingMedicineId) Perbarui @else Simpan @endif
                        </button>
                        <button type="button" @click="showModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto rounded-lg shadow ring-1 ring-black ring-opacity-5">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Nama Obat</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Kategori</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Stok</th>
                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($medicines as $medicine)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $medicine->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $medicine->category->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold {{ $medicine->stock < 10 ? 'text-red-600' : 'text-green-600' }}">
                            {{ $medicine->stock }} {{ $medicine->unit }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <button wire:click="edit({{ $medicine->id }})" @click="showModal = true" class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 px-3 py-1 rounded-md transition">Edit</button>
                            <button wire:click="delete({{ $medicine->id }})" wire:confirm="Yakin ingin menghapus obat ini?" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-3 py-1 rounded-md transition">Hapus</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-6 py-3 bg-gray-50">
            {{ $medicines->links() }}
        </div>
    </div>
</div>