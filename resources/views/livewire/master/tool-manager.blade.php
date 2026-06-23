<?php

use Livewire\Volt\Component;
use App\Models\Tool;
use App\Models\Category;

new class extends Component {
    public $editingToolId = null;

    public $category_id = '';
    public $name = '';
    public $unit = '';
    public $condition = '';
    public $description = '';
    public $initial_stock = 0;

    public function with(): array
    {
        return [
            'tools' => Tool::with('category')->latest()->get(),
            'categories' => Category::orderBy('name')->get(),
        ];
    }

    public function resetForm()
    {
        $this->editingToolId = null;
        $this->reset(['category_id', 'name', 'unit', 'condition', 'description', 'initial_stock']);
    }

    public function edit($id)
    {
        $tool = Tool::findOrFail($id);
        $this->editingToolId = $id;
        $this->category_id = $tool->category_id;
        $this->name = $tool->name;
        $this->unit = $tool->unit;
        $this->condition = $tool->condition;
        $this->description = $tool->description;
        $this->initial_stock = $tool->stock;
        $this->dispatch('open-modal');
    }

    public function save()
    {
        $this->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'condition' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'initial_stock' => 'required|integer|min:0',
        ]);

        if ($this->editingToolId) {
            // Update existing
            $tool = Tool::findOrFail($this->editingToolId);
            $tool->update([
                'category_id' => $this->category_id,
                'name' => $this->name,
                'unit' => $this->unit,
                'condition' => $this->condition,
                'description' => $this->description,
            ]);
            $message = 'Alat berhasil diperbarui.';
        } else {
            // Create new
            $tool = Tool::create([
                'category_id' => $this->category_id,
                'name' => $this->name,
                'stock' => $this->initial_stock,
                'unit' => $this->unit,
                'condition' => $this->condition,
                'description' => $this->description,
            ]);

            if ($this->initial_stock > 0) {
                \App\Models\Transaction::create([
                    'asset_id' => $tool->id,
                    'asset_type' => Tool::class,
                    'user_id' => auth()->id(),
                    'type' => \App\Enums\TransactionType::In,
                    'qty' => $this->initial_stock,
                    'source' => 'Stok Awal',
                    'notes' => 'Pendaftaran alat baru',
                ]);
            }
            $message = 'Alat berhasil ditambahkan.';
        }

        $this->resetForm();
        $this->dispatch('close-modal');
        session()->flash('message', $message);
    }

    public function delete($id)
    {
        Tool::findOrFail($id)->delete();
        session()->flash('message', 'Alat berhasil dihapus.');
    }
}; ?>

<div x-data="{ showModal: false }" @open-modal.window="showModal = true" @close-modal.window="showModal = false">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-bold text-gray-800">Daftar Alat (Tools)</h3>
        <button @click="showModal = true; $wire.resetForm()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow-sm transition flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah Alat Baru
        </button>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 p-4 text-green-700 bg-green-100 rounded-lg shadow-sm border border-green-200">
            {{ session('message') }}
        </div>
    @endif

    <!-- Pop-up Modal -->
    <div x-show="showModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div x-show="showModal" 
                x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" 
                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" 
                class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showModal = false" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal panel -->
            <div x-show="showModal" 
                x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                
                <form wire:submit="save">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                            @if($editingToolId)
                                Edit Data Alat
                            @else
                                Tambah Data Alat Baru
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
                                <x-input-label for="name" value="Nama Alat" />
                                <x-text-input wire:model="name" id="name" type="text" class="mt-1 block w-full" required />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="initial_stock" value="{{ $editingToolId ? 'Stok Saat Ini' : 'Stok Awal' }}" />
                                    <x-text-input wire:model="initial_stock" id="initial_stock" type="number" min="0" class="mt-1 block w-full" :disabled="(bool)$editingToolId" :required="!$editingToolId" />
                                    <x-input-error :messages="$errors->get('initial_stock')" class="mt-2" />
                                    @if($editingToolId)
                                        <p class="text-xs text-gray-500 mt-1">Ubah stok melalui fitur Stock In/Out</p>
                                    @endif
                                </div>
                                <div>
                                    <x-input-label for="unit" value="Satuan (Unit)" />
                                    <x-text-input wire:model="unit" id="unit" type="text" class="mt-1 block w-full" placeholder="Unit, Set, Pcs" required />
                                    <x-input-error :messages="$errors->get('unit')" class="mt-2" />
                                </div>
                            </div>
                            <div>
                                <x-input-label for="condition" value="Kondisi" />
                                <select wire:model="condition" id="condition" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="Baik">Baik</option>
                                    <option value="Perlu Perbaikan">Perlu Perbaikan</option>
                                    <option value="Rusak">Rusak</option>
                                </select>
                            </div>
                            <div>
                                <x-input-label for="description" value="Deskripsi (Opsional)" />
                                <textarea wire:model="description" id="description" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            @if($editingToolId) Perbarui @else Simpan Data @endif
                        </button>
                        <button type="button" @click="showModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto rounded-lg shadow ring-1 ring-black ring-opacity-5">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Nama Alat</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Kategori</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Kondisi</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Stok</th>
                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($tools as $tool)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $tool->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $tool->category->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($tool->condition == 'Baik')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Baik</span>
                            @elseif($tool->condition == 'Rusak')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Rusak</span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">{{ $tool->condition }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold {{ $tool->stock < 10 ? 'text-red-600' : 'text-green-600' }}">
                            {{ $tool->stock }} {{ $tool->unit }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <button wire:click="edit({{ $tool->id }})" @click="showModal = true" class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 px-3 py-1 rounded-md transition">Edit</button>
                            <button wire:click="delete({{ $tool->id }})" wire:confirm="Yakin ingin menghapus alat ini?" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-3 py-1 rounded-md transition">Hapus</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
