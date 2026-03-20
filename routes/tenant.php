<?php

declare(strict_types=1);

use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

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
        Route::livewire('media/upload', 'pages::dashboard.media.upload-files')->name('tenants.media.upload');
        Route::livewire('media/files', 'pages::dashboard.media.uploaded-files')->name('tenants.media.files');

        Route::get('pages/{slug}/page-builder', [PostController::class, 'pagebuilder'])->name('tenants.posts.page-builder');
        Route::post('pages/{slug}/update-content', [PostController::class, 'update'])->name('tenants.posts.update-content');
        Route::get('pages/{slug}/preview', [PostController::class, 'preview'])->name('tenants.posts.preview');

        Route::get('generate-landing-data', [\App\Http\Controllers\GeminiController::class, 'generateLandingData'])->name('tenants.generate-landing-data');

        Route::get('pages/{slug}/code-editor', [PostController::class, 'codeEditor'])->name('tenants.posts.code-editor');
        Route::post('pages/{slug}/update-code', [PostController::class, 'updateWithCodeMirror'])->name('tenants.posts.update-code');

    });

});
