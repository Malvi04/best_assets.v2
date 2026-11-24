<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

new class extends Component {
    use WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function with(): array
    {
        return [
            'activities' => Activity::with(['causer', 'subject'])
                ->when($this->search, function ($query) {
                    $query->where('description', 'like', '%' . $this->search . '%')
                        ->orWhereHas('causer', function ($q) {
                            $q->where('name', 'like', '%' . $this->search . '%');
                        });
                })
                ->latest()
                ->paginate(15),
        ];
    }
}; ?>

<div>
    <div class="mb-4 flex justify-between items-center">
        <h3 class="text-lg font-medium text-gray-900">Catatan Aktivitas Sistem</h3>
        <div class="w-1/3">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari aktivitas atau nama pengguna..." class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengguna</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aktivitas</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modul/Target</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Detail Perubahan</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($activities as $activity)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $activity->created_at->format('d M Y H:i:s') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $activity->causer ? $activity->causer->name : 'Sistem' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $activity->event === 'created' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $activity->event === 'updated' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $activity->event === 'deleted' ? 'bg-red-100 text-red-800' : '' }}
                                {{ !in_array($activity->event, ['created', 'updated', 'deleted']) ? 'bg-gray-100 text-gray-800' : '' }}
                            ">
                                {{ ucfirst($activity->event) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($activity->subject_type)
                                {{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            @if($activity->properties && count($activity->properties) > 0)
                                <div class="text-xs overflow-x-auto max-w-xs">
                                    @if(isset($activity->properties['old']) && count($activity->properties['old']) > 0)
                                        <div class="mb-1"><span class="font-semibold text-red-600">Lama:</span> 
                                            @foreach($activity->properties['old'] as $key => $value)
                                                {{ $key }}: {{ is_array($value) ? json_encode($value) : $value }}<br>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if(isset($activity->properties['attributes']) && count($activity->properties['attributes']) > 0)
                                        <div><span class="font-semibold text-green-600">Baru:</span> 
                                            @foreach($activity->properties['attributes'] as $key => $value)
                                                {{ $key }}: {{ is_array($value) ? json_encode($value) : $value }}<br>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            Tidak ada catatan aktivitas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $activities->links() }}
    </div>
</div>
