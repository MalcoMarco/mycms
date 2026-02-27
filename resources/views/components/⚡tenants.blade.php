<?php

use App\Http\Requests\TenantRequest;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Component;

new class extends Component
{
    // Form properties
    public string $tenant_id = '';
    public bool $showCreateForm = false;
    public string $baseDomain = '';
    // Loading states
    public bool $creating = false;

    public function boot() 
    {
        $this->baseDomain = parse_url(config('app.url'), PHP_URL_HOST);
    }

    /**
     * Get the user's tenants with their role
     */
    public function getUserTenantsProperty()
    {
        return auth()->user()->tenants()->orderBy('created_at', 'desc')->get();
    }

    /**
     * Toggle the create form visibility
     */
    public function toggleCreateForm(): void
    {
        $this->showCreateForm = !$this->showCreateForm;
        $this->resetForm();
    }

    /**
     * Reset the form to initial state
     */
    public function resetForm(): void
    {
        $this->reset('tenant_id');
        $this->resetValidation();
    }

    /**
     * Validate and create a new tenant
     */
    public function createTenant(): void
    {
        $this->creating = true;

        // Validate the input
        $validated = $this->validate([
            'tenant_id' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'regex:/^[a-z0-9-]+$/',
                'unique:tenants,id',
            ],
        ], [
            'tenant_id.required' => 'El subdominio es obligatorio.',
            'tenant_id.min' => 'El subdominio debe tener al menos 3 caracteres.',
            'tenant_id.max' => 'El subdominio no puede tener más de 50 caracteres.',
            'tenant_id.regex' => 'El subdominio solo puede contener letras minúsculas, números y guiones.',
            'tenant_id.unique' => 'Este subdominio ya está en uso.',
        ]);

        try {
            // Create the tenant
            $tenant = Tenant::create([
                'id' => $validated['tenant_id'],
                'data' => [
                    'name' => ucfirst($validated['tenant_id']),
                ],
            ]);
            $tenant->domains()->create([
                'domain' => $tenant->id . '.' . $this->baseDomain,
            ]);
            // Attach the current user as owner
            $tenant->users()->attach(auth()->id(), ['role' => 'owner']);

            // Reset form and hide it
            $this->resetForm();
            $this->showCreateForm = false;

            // Show success message
            session()->flash('message', 'Tenant creado exitosamente.');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al crear el tenant: ' . $e->getMessage());
        } finally {
            $this->creating = false;
        }
    }
};
?>

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <flux:heading size="lg">Mis Tenants</flux:heading>
            <flux:button 
                wire:click="toggleCreateForm" 
                variant="primary" 
                icon="plus"
                size="sm">
                {{ $showCreateForm ? 'Cancelar' : 'Nuevo Tenant' }}
            </flux:button>
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

        <!-- Create Form -->
        @if ($showCreateForm)
            <div class="bg-white dark:bg-neutral-800 rounded-lg border border-neutral-200 dark:border-neutral-700 p-6">
                <flux:heading size="md" class="mb-4">Crear Nuevo Tenant</flux:heading>
                
                <form wire:submit="createTenant" class="space-y-4">
                    <!-- Subdomain Field -->
                    <flux:field>
                        <flux:label>Subdominio</flux:label>
                        <div class="flex items-center">
                            <flux:input 
                                wire:model="tenant_id" 
                                placeholder="mi-empresa"
                                :disabled="$creating" 
                                class="flex-1" />
                            <span class="ml-2 text-sm text-neutral-500 dark:text-neutral-400">
                                .{{ $baseDomain }}
                            </span>
                        </div>
                        <flux:error name="tenant_id" />
                        <flux:description>Tu tenant será accesible en: <strong>{{ $tenant_id ?: 'subdominio' }}.{{ $baseDomain }}</strong></flux:description>
                    </flux:field>

                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-3 pt-4 border-t border-neutral-200 dark:border-neutral-700">
                        <flux:button 
                            type="button" 
                            wire:click="toggleCreateForm" 
                            variant="ghost"
                            :disabled="$creating">
                            Cancelar
                        </flux:button>
                        <flux:button 
                            type="submit" 
                            variant="primary"
                            :disabled="$creating">
                            <span wire:loading.remove wire:target="createTenant">Crear Tenant</span>
                            <span wire:loading wire:target="createTenant" class="flex items-center gap-2">
                                <flux:icon.spinner class="animate-spin" size="sm" />
                                Creando...
                            </span>
                        </flux:button>
                    </div>
                </form>
            </div>
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
                    @unless ($showCreateForm)
                        <flux:button 
                            wire:click="toggleCreateForm" 
                            variant="primary" 
                            class="mt-4"
                            icon="plus">
                            Crear mi primer tenant
                        </flux:button>
                    @endunless
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