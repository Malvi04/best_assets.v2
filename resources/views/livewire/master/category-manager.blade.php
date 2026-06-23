<?php

use Livewire\Volt\Component;
use App\Models\Category;

new class extends Component {
    public $editingCategoryId = null;
    public $name = '';
    public $description = '';

    public function with(): array
    {
        return [
            'categories' => Category::all()
        ];
    }

    public function resetForm()
    {
        $this->editingCategoryId = null;
        $this->reset(['name', 'description']);
    }

    public function edit($id)
    {
        $category = Category::findOrFail($id);
        $this->editingCategoryId = $id;
        $this->name = $category->name;
        $this->description = $category->description;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($this->editingCategoryId) {
            // Update existing
            $category = Category::findOrFail($this->editingCategoryId);
            $category->update([
                'name' => $this->name,
                'description' => $this->description,
            ]);
            $message = 'Kategori berhasil diperbarui.';
        } else {
            // Create new
            Category::create([
                'name' => $this->name,
                'description' => $this->description,
            ]);
            $message = 'Kategori berhasil ditambahkan.';
        }

        $this->resetForm();
        session()->flash('message', $message);
    }

    public function delete($id)
    {
        Category::findOrFail($id)->delete();
        session()->flash('message', 'Kategori berhasil dihapus.');
    }
}; ?>

<div>
    <div class="mb-6 p-4 border rounded-md bg-blue-50">
        <h3 class="text-lg font-semibold text-blue-800 mb-4">
            {{ $editingCategoryId ? 'Edit Kategori' : 'Tambah Kategori Baru' }}
        </h3>
        <form wire:submit="save" class="space-y-4">
            <div>
                <x-input-label for="name" value="Nama Kategori" />
                <x-text-input wire:model="name" id="name" type="text" class="mt-1 block w-full" required />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="description" value="Deskripsi" />
                <x-text-input wire:model="description" id="description" type="text" class="mt-1 block w-full" />
                <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>
            <div class="flex items-center space-x-3">
                <x-primary-button class="bg-blue-600 hover:bg-blue-700">
                    {{ $editingCategoryId ? 'Perbarui Kategori' : 'Simpan Kategori' }}
                </x-primary-button>
                @if($editingCategoryId)
                    <button type="button" wire:click="resetForm" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">Batal</button>
                @endif
            </div>
        </form>
    </div>

    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach($categories as $category)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $category->name }}</td>
                    <td class="px-6 py-4">{{ $category->description }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <button wire:click="edit({{ $category->id }})" class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                        <button wire:click="delete({{ $category->id }})" wire:confirm="Yakin ingin menghapus?" class="text-red-600 hover:text-red-900">Hapus</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
