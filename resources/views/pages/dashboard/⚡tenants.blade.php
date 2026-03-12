<?php

use App\Models\Tenant;
use Livewire\Component;

new class extends Component
{
    public string $baseDomain = '';

    public function boot(): void
    {
        $this->baseDomain = parse_url(config('app.url'), PHP_URL_HOST);
    }

    /**
     * Get the user's tenants with their role.
     */
    public function getUserTenantsProperty()
    {
        return auth()->user()->tenants()->orderBy('created_at', 'desc')->get();
    }
};
?>

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <flux:heading size="lg">Mis Tenants</flux:heading>
            <a href="{{ route('tenants.create') }}" wire:navigate>
                <flux:button variant="primary" icon="plus" size="sm">
                    Nuevo Tenant
                </flux:button>
            </a>
        </div>

        <!-- Success/Error Messages -->
        @if (session()->has('message'))
            <flux:callout variant="success" icon="check-circle">
                {{ session('message') }}
            </flux:callout>
        @endif

        @if (session()->has('error'))
            <flux:callout variant="danger" icon="exclamation-triangle">
                {{ session('error') }}
            </flux:callout>
        @endif

        <!-- Tenants List -->
        <div class="space-y-4">
            @if ($this->userTenants->isEmpty())
                <div class="text-center py-12">
                    <flux:icon.building-office-2 class="mx-auto h-12 w-12 text-neutral-400" />
                    <flux:heading size="md" class="mt-4 text-neutral-900 dark:text-neutral-100">
                        No tienes tenants
                    </flux:heading>
                    <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">
                        Empieza creando tu primer tenant para organizar tu trabajo.
                    </p>
                    <a href="{{ route('tenants.create') }}" wire:navigate>
                        <flux:button variant="primary" class="mt-4" icon="plus">
                            Crear mi primer tenant
                        </flux:button>
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($this->userTenants as $tenant)
                        <div class="bg-white dark:bg-neutral-800 rounded-lg border border-neutral-200 dark:border-neutral-700 p-6 hover:shadow-md transition-shadow">
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <flux:heading size="md" class="truncate">
                                        <a href="http://{{ $tenant->domains->first()->domain ?? $tenant->id . '.' . $baseDomain }}:8000/dashboard" target="_blank" rel="noopener noreferrer">
                                            {{ $tenant->domains->first()->domain ?? $tenant->id . '.' . $baseDomain }}
                                        
                                        </a>
                                    </flux:heading>
                                    <p class="text-sm text-neutral-500 dark:text-neutral-400 font-mono">
                                        {{ $tenant->id }}
                                    </p>
                                </div>
                                <flux:badge variant="solid" size="sm" class="ml-2 flex-shrink-0">
                                    {{ ucfirst($tenant->pivot->role) }}
                                </flux:badge>
                            </div>
                            
                            <div class="mt-4 pt-4 border-t border-neutral-200 dark:border-neutral-700">
                                <div class="flex justify-between items-center text-xs text-neutral-500 dark:text-neutral-400">
                                    <span>Creado {{ $tenant->created_at->diffForHumans() }}</span>
                                    <flux:button variant="ghost" size="xs" icon="cog-6-tooth">
                                        Configurar
                                    </flux:button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>