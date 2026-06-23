<?php

use Illuminate\Support\Facades\Route;

// Route::view('/', 'welcome');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('/', 'dashboard')->name('dashboard');

    // Admin Only
    Route::middleware(['role:admin'])->group(function () {
        Route::view('master/categories', 'master.categories')->name('categories.index');
        Route::view('master/tools', 'master.tools')->name('tools.index');
        Route::view('master/medicines', 'master.medicines')->name('medicines.index');
        Route::view('inventory/stock-in', 'inventory.stock-in')->name('stock-in.index');
        Route::view('inventory/stock-out', 'inventory.stock-out')->name('stock-out.index');
        Route::view('approvals', 'approvals.index')->name('approvals.index');
        Route::view('admin/users', 'admin.users')->name('admin.users.index');
    });

    // Teknisi
    Route::middleware(['role:teknisi'])->group(function () {
        Route::view('request-stock', 'teknisi.request-stock')->name('request-stock.index');
    });
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';

// Route untuk membuat symbolic link dari public/storage ke storage/app/public (hosting)
// Route::get('/symlink-generate', function () {
//     Artisan::call('storage:link');
//     return 'Symlink berhasil dibuat!';
// });
