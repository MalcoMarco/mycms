<?php

declare(strict_types=1);

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
    Route::middleware([ 'auth', 'tenant.access', ])->group(function () {
    
        Route::view('dashboard', 'pages.tenants.index');
        Route::livewire('web-settings', 'pages::tenants.web-settings-edit')->name('tenants.web-settings.edit');

    });

});
