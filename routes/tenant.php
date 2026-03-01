<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Http\Controllers\PostController;
/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::get('/', function () {
        return 'This is your multi-tenant application. The id of the current tenant is '.tenant('id');
    });

    /**
     * Rutas protegidas por el middleware EnsureUserBelongsToTenant,
     * que verifica que el usuario autenticado pertenece al tenant actual.
     */
    Route::prefix('dashboard')->middleware(['auth', 'tenant.access'])->group(function () {

        Route::view('', 'pages.tenants.index');
        Route::livewire('web-settings', 'pages::tenants.web-settings-edit')->name('tenants.web-settings.edit');
        Route::livewire('pages', 'pages::tenants.posts-pages')->name('tenants.posts.index');
        Route::livewire('components', 'pages::tenants.posts-component')->name('tenants.posts-component.index');

        Route::get('pages/{slug}/page-builder', [PostController::class, 'pagebuilder'])->name('tenants.posts.page-builder');
        Route::post('pages/{slug}/update-content', [PostController::class, 'update'])->name('tenants.posts.update-content');
        Route::get('pages/{slug}/preview', [PostController::class, 'preview'])->name('tenants.posts.preview');

    });

});
