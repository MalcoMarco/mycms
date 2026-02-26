<?php

use App\Http\Controllers\TenantController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'pages.dashboard.index')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');
Route::prefix('dashboard')->middleware(['auth', 'verified'])->group(function () {

    Route::livewire('tenants', 'tenants')->name('tenants.index');
    Route::livewire('tenants/{tenant}', 'tenant-manager')->name('tenants.manager');
});

require __DIR__.'/settings.php';
