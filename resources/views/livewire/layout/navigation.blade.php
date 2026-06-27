<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();
        $this->redirect('/', navigate: true);
    }
}; ?>

<aside class="bg-blue-900 text-white w-64 flex-shrink-0 h-full flex flex-col shadow-xl overflow-y-auto">
    <div class="h-16 md:h-20 flex items-center justify-between md:justify-center border-b border-blue-800 px-4 flex-shrink-0 relative">
        <a href="{{ route('dashboard') }}" wire:navigate class="text-xl md:text-2xl font-bold tracking-wider">
            Best Assets
        </a>
        
        <button @click="sidebarOpen = false" class="md:hidden text-blue-200 hover:text-white p-2 -mr-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <div class="p-3 md:p-6 border-b border-blue-800 bg-blue-950/50 flex-shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-blue-400 flex items-center justify-center font-bold text-white text-sm flex-shrink-0">
                {{ substr(auth()->user()->name, 0, 1) }}
            </div>
            <div class="overflow-hidden min-w-0">
                <p class="font-semibold text-sm truncate" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></p>
                <p class="text-xs text-blue-300 truncate">{{ ucfirst(auth()->user()->role->value) }}</p>
            </div>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto py-4 space-y-1 px-2">
        <a href="{{ route('dashboard') }}" wire:navigate 
            class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm transition {{ request()->routeIs('dashboard') ? 'bg-blue-700 text-white font-medium' : 'text-blue-100 hover:bg-blue-800' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
            <span class="truncate">Dashboard</span>
        </a>

        @if(auth()->user()->role->value === 'admin')
            <div class="pt-4 pb-2 px-4 text-xs font-bold text-blue-300 uppercase tracking-wider">Master Data</div>

            <a href="{{ route('categories.index') }}" wire:navigate 
                class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm transition {{ request()->routeIs('categories.*') ? 'bg-blue-700 text-white font-medium' : 'text-blue-100 hover:bg-blue-800' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                </svg>
                <span class="truncate">Kategori</span>
            </a>

            <a href="{{ route('tools.index') }}" wire:navigate 
                class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm transition {{ request()->routeIs('tools.*') ? 'bg-blue-700 text-white font-medium' : 'text-blue-100 hover:bg-blue-800' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                </svg>
                <span class="truncate">Data Alat</span>
            </a>

            <a href="{{ route('medicines.index') }}" wire:navigate 
                class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm transition {{ request()->routeIs('medicines.*') ? 'bg-blue-700 text-white font-medium' : 'text-blue-100 hover:bg-blue-800' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                </svg>
                <span class="truncate">Data Obat</span>
            </a>

            <a href="{{ route('admin.users.index') }}" wire:navigate 
                class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm transition {{ request()->routeIs('admin.users.*') ? 'bg-blue-700 text-white font-medium' : 'text-blue-100 hover:bg-blue-800' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                <span class="truncate">Pengguna</span>
            </a>

            <div class="pt-4 pb-2 px-4 text-xs font-bold text-blue-300 uppercase tracking-wider">Inventory</div>

            <a href="{{ route('stock-in.index') }}" wire:navigate 
                class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm transition {{ request()->routeIs('stock-in.*') ? 'bg-blue-700 text-white font-medium' : 'text-blue-100 hover:bg-blue-800' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                </svg>
                <span class="truncate">Stok Masuk</span>
            </a>

            <a href="{{ route('stock-out.index') }}" wire:navigate 
                class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm transition {{ request()->routeIs('stock-out.*') ? 'bg-blue-700 text-white font-medium' : 'text-blue-100 hover:bg-blue-800' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-4 4m0 0l-4-4m4 4V7"></path>
                </svg>
                <span class="truncate">Barang Keluar</span>
            </a>

            <a href="{{ route('approvals.index') }}" wire:navigate 
                class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm transition {{ request()->routeIs('approvals.*') ? 'bg-blue-700 text-white font-medium' : 'text-blue-100 hover:bg-blue-800' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="truncate">Approval</span>
            </a>
        @endif

        @if(auth()->user()->role->value === 'teknisi')
            <div class="pt-4 pb-2 px-4 text-xs font-bold text-blue-300 uppercase tracking-wider">Operasional</div>

            <a href="{{ route('request-stock.index') }}" wire:navigate 
                class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm transition {{ request()->routeIs('request-stock.*') ? 'bg-blue-700 text-white font-medium' : 'text-blue-100 hover:bg-blue-800' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <span class="truncate">Request Stok</span>
            </a>
        @endif
    </nav>

    <div class="border-t border-blue-800 p-2 space-y-1 flex-shrink-0 bg-blue-950/30">
        <a href="{{ route('profile') }}" wire:navigate 
            class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm text-blue-100 hover:bg-blue-800 transition">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
            </svg>
            <span class="truncate">Profil</span>
        </a>

        <button wire:click="logout" 
            class="w-full flex items-center gap-3 px-4 py-3 rounded-lg text-sm text-red-300 hover:bg-red-900/40 transition">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
            </svg>
            <span class="truncate">Log Out</span>
        </button>
    </div>
</aside>