<?php

use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Models\Post;
use App\Models\Tenant;
use App\Models\WebSetting;
use Livewire\Component;

new class extends Component
{
    // Wizard step
    public int $step = 1;
    public int $totalSteps = 4;

    // Step 1: Identity
    public string $tenant_id = '';
    public string $site_name = '';
    public string $baseDomain = '';

    // Step 2: SEO & Description
    public string $meta_title = '';
    public string $meta_description = '';
    public string $meta_keywords = '';

    // Step 3: Branding
    public string $primary_color = '#4f46e5';
    public string $secondary_color = '#0d9488';
    public string $accent_color = '#f59e0b';
    public string $logo = '';

    // Step 4: Social
    public string $facebook_url = '';
    public string $instagram_url = '';
    public string $twitter_url = '';
    public string $whatsapp_number = '';

    // State
    public bool $creating = false;

    public function boot(): void
    {
        $this->baseDomain = parse_url(config('app.url'), PHP_URL_HOST);
    }

    /**
     * Validate and move to the next step.
     */
    public function nextStep(): void
    {
        $this->validateStep($this->step);
        $this->step = min($this->step + 1, $this->totalSteps);
    }

    /**
     * Move to the previous step.
     */
    public function previousStep(): void
    {
        $this->step = max($this->step - 1, 1);
    }

    /**
     * Go to a specific step (only if already visited).
     */
    public function goToStep(int $step): void
    {
        if ($step < $this->step) {
            $this->step = $step;
        }
    }

    /**
     * Get validation rules for a specific step.
     *
     * @return array<string, mixed>
     */
    protected function rulesForStep(int $step): array
    {
        return match ($step) {
            1 => [
                'tenant_id' => ['required', 'string', 'min:3', 'max:50', 'regex:/^[a-z0-9-]+$/', 'unique:tenants,id'],
                'site_name' => ['required', 'string', 'min:2', 'max:100'],
            ],
            2 => [
                'meta_title' => ['required', 'string', 'max:70'],
                'meta_description' => ['required', 'string', 'max:160'],
                'meta_keywords' => ['nullable', 'string', 'max:255'],
            ],
            3 => [
                'primary_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
                'secondary_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
                'accent_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
                'logo' => ['nullable', 'url', 'max:500'],
            ],
            4 => [
                'facebook_url' => ['nullable', 'url', 'max:255'],
                'instagram_url' => ['nullable', 'url', 'max:255'],
                'twitter_url' => ['nullable', 'url', 'max:255'],
                'whatsapp_number' => ['nullable', 'string', 'max:20'],
            ],
            default => [],
        };
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    protected function validationMessages(): array
    {
        return [
            'tenant_id.required' => 'El subdominio es obligatorio.',
            'tenant_id.min' => 'El subdominio debe tener al menos 3 caracteres.',
            'tenant_id.max' => 'El subdominio no puede tener más de 50 caracteres.',
            'tenant_id.regex' => 'Solo letras minúsculas, números y guiones.',
            'tenant_id.unique' => 'Este subdominio ya está en uso.',
            'site_name.required' => 'El nombre del sitio es obligatorio.',
            'meta_title.required' => 'El título SEO es obligatorio.',
            'meta_title.max' => 'Máximo 70 caracteres para SEO.',
            'meta_description.required' => 'La descripción es obligatoria.',
            'meta_description.max' => 'Máximo 160 caracteres para SEO.',
            'primary_color.regex' => 'Formato de color inválido (ej: #4f46e5).',
            'secondary_color.regex' => 'Formato de color inválido (ej: #0d9488).',
            'accent_color.regex' => 'Formato de color inválido (ej: #f59e0b).',
        ];
    }

    /**
     * Validate a specific step.
     */
    protected function validateStep(int $step): void
    {
        $this->validate($this->rulesForStep($step), $this->validationMessages());
    }

    /**
     * Create the tenant, web settings, and home post.
     */
    public function createSite(): void
    {
        $this->creating = true;

        // Validate the current step
        $this->validateStep($this->step);

        try {
            // Create tenant
            $tenant = Tenant::create([
                'id' => $this->tenant_id,
                'data' => ['name' => $this->site_name],
            ]);

            $tenant->domains()->create([
                'domain' => $this->tenant_id . '.' . $this->baseDomain,
            ]);

            $tenant->users()->attach(auth()->id(), ['role' => 'owner']);

            // Create web settings
            WebSetting::create([
                'tenant_id' => $tenant->id,
                'meta_title' => $this->meta_title,
                'meta_description' => $this->meta_description,
                'meta_keywords' => $this->meta_keywords,
                'og_title' => $this->meta_title,
                'og_description' => $this->meta_description,
                'primary_color' => $this->primary_color,
                'secondary_color' => $this->secondary_color,
                'accent_color' => $this->accent_color,
                'logo' => $this->logo ?: null,
                'facebook_url' => $this->facebook_url ?: null,
                'instagram_url' => $this->instagram_url ?: null,
                'twitter_url' => $this->twitter_url ?: null,
                'whatsapp_number' => $this->whatsapp_number ?: null,
            ]);

            // Create the homepage post
            Post::create([
                'tenant_id' => $tenant->id,
                'slug' => 'home',
                'type_id' => PostType::Page->value,
                'title' => $this->site_name,
                'excerpt' => $this->meta_description,
                'status' => PostStatus::Draft->value,
            ]);

            session()->flash('message', '¡Tu sitio ha sido creado exitosamente!');

            $this->redirect(route('tenants.index'), navigate: true);
        } catch (\Exception $e) {
            session()->flash('error', 'Error al crear el sitio: ' . $e->getMessage());
        } finally {
            $this->creating = false;
        }
    }
};
?>


    <div class="max-w-4xl mx-auto py-8 px-4">

        {{-- Header --}}
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center size-16 rounded-2xl bg-primary-100 dark:bg-primary-900/40 text-primary-600 dark:text-primary-400 mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" />
                </svg>
            </div>
            <flux:heading size="xl">Crea tu Página Web</flux:heading>
            <p class="mt-2 text-zinc-500 dark:text-zinc-400">Configura tu sitio paso a paso y tendrás todo listo en segundos</p>
        </div>

        {{-- Progress Steps --}}
        <div class="mb-10">
            <div class="flex items-center justify-between max-w-lg mx-auto">
                @foreach ([
                    ['step' => 1, 'label' => 'Identidad', 'icon' => 'globe-alt'],
                    ['step' => 2, 'label' => 'SEO', 'icon' => 'magnifying-glass'],
                    ['step' => 3, 'label' => 'Diseño', 'icon' => 'swatch'],
                    ['step' => 4, 'label' => 'Social', 'icon' => 'share'],
                ] as $i => $s)
                    @if ($i > 0)
                        <div class="flex-1 h-0.5 mx-2 rounded {{ $step > $s['step'] - 1 ? 'bg-primary-500' : 'bg-zinc-200 dark:bg-zinc-700' }} transition-colors"></div>
                    @endif
                    <button
                        type="button"
                        wire:click="goToStep({{ $s['step'] }})"
                        class="flex flex-col items-center gap-1.5 group {{ $s['step'] <= $step ? 'cursor-pointer' : 'cursor-default' }}"
                    >
                        <div class="size-10 rounded-xl flex items-center justify-center font-semibold text-sm transition-all
                            {{ $step === $s['step'] ? 'bg-primary-600 text-white shadow-lg shadow-primary-600/30 scale-110' : '' }}
                            {{ $step > $s['step'] ? 'bg-primary-100 dark:bg-primary-900/40 text-primary-600 dark:text-primary-400' : '' }}
                            {{ $step < $s['step'] ? 'bg-zinc-100 dark:bg-zinc-800 text-zinc-400 dark:text-zinc-500' : '' }}
                        ">
                            @if ($step > $s['step'])
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            @else
                                {{ $s['step'] }}
                            @endif
                        </div>
                        <span class="text-xs font-medium {{ $step >= $s['step'] ? 'text-zinc-700 dark:text-zinc-300' : 'text-zinc-400 dark:text-zinc-500' }}">
                            {{ $s['label'] }}
                        </span>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Error/Success Messages --}}
        @if (session()->has('error'))
            <div class="mb-6">
                <flux:callout variant="danger" icon="exclamation-triangle">
                    {{ session('error') }}
                </flux:callout>
            </div>
        @endif

        {{-- Wizard Card --}}
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm overflow-hidden">
            <form wire:submit="{{ $step === $totalSteps ? 'createSite' : 'nextStep' }}">

                {{-- ================================================ --}}
                {{-- STEP 1: Identity --}}
                {{-- ================================================ --}}
                @if ($step === 1)
                    <div class="p-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="size-10 rounded-xl bg-primary-100 dark:bg-primary-900/40 flex items-center justify-center">
                                <flux:icon.globe-alt class="size-5 text-primary-600 dark:text-primary-400" />
                            </div>
                            <div>
                                <flux:heading size="lg">Identidad de tu sitio</flux:heading>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">Elige el nombre y subdominio de tu página</p>
                            </div>
                        </div>

                        <div class="grid gap-6">
                            <flux:field>
                                <flux:label>Nombre del sitio</flux:label>
                                <flux:input wire:model.live.debounce.300ms="site_name" placeholder="Mi Negocio Increíble" icon="building-storefront" />
                                <flux:error name="site_name" />
                                <flux:description>El nombre público de tu página web.</flux:description>
                            </flux:field>

                            <flux:field>
                                <flux:label>Subdominio</flux:label>
                                <flux:input.group>
                                    <flux:input wire:model.live.debounce.300ms="tenant_id" placeholder="mi-negocio" />
                                    <flux:input.group.suffix>.{{ $baseDomain }}</flux:input.group.suffix>
                                </flux:input.group>
                                <flux:error name="tenant_id" />
                                <flux:description>Tu sitio será accesible en: <strong class="text-primary-600 dark:text-primary-400">{{ $tenant_id ?: 'subdominio' }}.{{ $baseDomain }}</strong></flux:description>
                            </flux:field>
                        </div>

                        {{-- Live Preview Card --}}
                        @if ($site_name || $tenant_id)
                            <div class="mt-8 rounded-xl border border-dashed border-primary-300 dark:border-primary-700 bg-primary-50/50 dark:bg-primary-950/20 p-5">
                                <p class="text-xs font-semibold text-primary-600 dark:text-primary-400 uppercase tracking-wider mb-3">Vista previa</p>
                                <div class="flex items-center gap-3">
                                    <div class="size-10 rounded-lg bg-primary-200 dark:bg-primary-800 flex items-center justify-center text-primary-700 dark:text-primary-300 font-bold text-lg">
                                        {{ strtoupper(substr($site_name ?: 'S', 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $site_name ?: 'Tu sitio' }}</p>
                                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $tenant_id ?: 'subdominio' }}.{{ $baseDomain }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- ================================================ --}}
                {{-- STEP 2: SEO --}}
                {{-- ================================================ --}}
                @if ($step === 2)
                    <div class="p-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="size-10 rounded-xl bg-secondary-100 dark:bg-secondary-900/40 flex items-center justify-center">
                                <flux:icon.magnifying-glass class="size-5 text-secondary-600 dark:text-secondary-400" />
                            </div>
                            <div>
                                <flux:heading size="lg">SEO y Descripción</flux:heading>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">Optimiza cómo aparecerá tu sitio en los buscadores</p>
                            </div>
                        </div>

                        <div class="grid gap-6">
                            <flux:field>
                                <flux:label>Título SEO</flux:label>
                                <flux:input wire:model.live.debounce.300ms="meta_title" placeholder="Mi Negocio - Servicios Profesionales" icon="document-text" maxlength="70" />
                                <flux:error name="meta_title" />
                                <flux:description>
                                    <span class="{{ strlen($meta_title) > 60 ? 'text-amber-500' : '' }}">{{ strlen($meta_title) }}/70 caracteres</span>
                                    — Aparece en la pestaña del navegador y resultados de Google.
                                </flux:description>
                            </flux:field>

                            <flux:field>
                                <flux:label>Meta Descripción</flux:label>
                                <flux:textarea wire:model.live.debounce.300ms="meta_description" placeholder="Describe tu negocio en 1-2 oraciones. Esto aparecerá en los resultados de búsqueda." rows="3" />
                                <flux:error name="meta_description" />
                                <flux:description>
                                    <span class="{{ strlen($meta_description) > 150 ? 'text-amber-500' : '' }}">{{ strlen($meta_description) }}/160 caracteres</span>
                                    — Aparece debajo del título en Google.
                                </flux:description>
                            </flux:field>

                            <flux:field>
                                <flux:label>Palabras clave</flux:label>
                                <flux:input wire:model="meta_keywords" placeholder="negocio, servicios, profesional" icon="tag" />
                                <flux:error name="meta_keywords" />
                                <flux:description>Separadas por comas (opcional).</flux:description>
                            </flux:field>
                        </div>

                        {{-- Google Preview --}}
                        <div class="mt-8 rounded-xl border border-dashed border-secondary-300 dark:border-secondary-700 bg-secondary-50/50 dark:bg-secondary-950/20 p-5">
                            <p class="text-xs font-semibold text-secondary-600 dark:text-secondary-400 uppercase tracking-wider mb-3">Vista previa en Google</p>
                            <div class="space-y-1">
                                <p class="text-lg font-medium text-blue-700 dark:text-blue-400 truncate">
                                    {{ $meta_title ?: 'Título de tu página' }}
                                </p>
                                <p class="text-sm text-green-700 dark:text-green-500">
                                    {{ $tenant_id ?: 'subdominio' }}.{{ $baseDomain }}
                                </p>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400 line-clamp-2">
                                    {{ $meta_description ?: 'La descripción de tu página aparecerá aquí...' }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ================================================ --}}
                {{-- STEP 3: Design --}}
                {{-- ================================================ --}}
                @if ($step === 3)
                    <div class="p-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="size-10 rounded-xl bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center">
                                <flux:icon.swatch class="size-5 text-amber-600 dark:text-amber-400" />
                            </div>
                            <div>
                                <flux:heading size="lg">Diseño y Marca</flux:heading>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">Define los colores que representan tu marca</p>
                            </div>
                        </div>

                        <div class="grid gap-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                {{-- Primary Color --}}
                                <flux:field>
                                    <flux:label>Color Primario</flux:label>
                                    <div class="flex items-center gap-3">
                                        <input type="color" wire:model.live="primary_color" class="size-10 rounded-lg border border-zinc-300 dark:border-zinc-600 cursor-pointer p-0.5" />
                                        <flux:input wire:model.live.debounce.300ms="primary_color" placeholder="#4f46e5" class="flex-1 font-mono text-sm" />
                                    </div>
                                    <flux:error name="primary_color" />
                                </flux:field>

                                {{-- Secondary Color --}}
                                <flux:field>
                                    <flux:label>Color Secundario</flux:label>
                                    <div class="flex items-center gap-3">
                                        <input type="color" wire:model.live="secondary_color" class="size-10 rounded-lg border border-zinc-300 dark:border-zinc-600 cursor-pointer p-0.5" />
                                        <flux:input wire:model.live.debounce.300ms="secondary_color" placeholder="#0d9488" class="flex-1 font-mono text-sm" />
                                    </div>
                                    <flux:error name="secondary_color" />
                                </flux:field>

                                {{-- Accent Color --}}
                                <flux:field>
                                    <flux:label>Color de Acento</flux:label>
                                    <div class="flex items-center gap-3">
                                        <input type="color" wire:model.live="accent_color" class="size-10 rounded-lg border border-zinc-300 dark:border-zinc-600 cursor-pointer p-0.5" />
                                        <flux:input wire:model.live.debounce.300ms="accent_color" placeholder="#f59e0b" class="flex-1 font-mono text-sm" />
                                    </div>
                                    <flux:error name="accent_color" />
                                </flux:field>
                            </div>

                            <flux:field>
                                <flux:label>URL del Logo</flux:label>
                                <flux:input wire:model="logo" type="url" placeholder="https://ejemplo.com/mi-logo.png" icon="photo" />
                                <flux:error name="logo" />
                                <flux:description>Enlace a la imagen de tu logo (opcional).</flux:description>
                            </flux:field>
                        </div>

                        {{-- Design Preview --}}
                        <div class="mt-8 rounded-xl border border-dashed border-amber-300 dark:border-amber-700 bg-amber-50/50 dark:bg-amber-950/20 p-5">
                            <p class="text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wider mb-4">Vista previa del diseño</p>
                            {{-- Mini website preview --}}
                            <div class="rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
                                {{-- Navbar preview --}}
                                <div class="px-4 py-3 flex items-center justify-between" x-bind:style="'background-color: ' + $wire.primary_color">
                                    <div class="flex items-center gap-2">
                                        @if ($logo)
                                            <img src="{{ $logo }}" alt="Logo" class="h-6 w-6 rounded object-cover" />
                                        @else
                                            <div class="size-6 rounded font-bold text-xs flex items-center justify-center text-white" x-bind:style="'background-color: ' + $wire.secondary_color">
                                                {{ strtoupper(substr($site_name ?: 'S', 0, 1)) }}
                                            </div>
                                        @endif
                                        <span class="text-white text-sm font-semibold">{{ $site_name ?: 'Mi Sitio' }}</span>
                                    </div>
                                    <div class="flex gap-3">
                                        <div class="h-2 w-10 bg-white/30 rounded"></div>
                                        <div class="h-2 w-10 bg-white/30 rounded"></div>
                                        <div class="h-2 w-10 bg-white/30 rounded"></div>
                                    </div>
                                </div>
                                {{-- Content preview --}}
                                <div class="p-4 space-y-3">
                                    <div class="h-3 w-48 rounded bg-zinc-200 dark:bg-zinc-700"></div>
                                    <div class="h-2 w-full bg-zinc-100 dark:bg-zinc-800 rounded"></div>
                                    <div class="h-2 w-3/4 bg-zinc-100 dark:bg-zinc-800 rounded"></div>
                                    <div class="flex gap-2 mt-3">
                                        <div class="px-3 py-1.5 rounded text-white text-xs font-medium" x-bind:style="'background-color: ' + $wire.primary_color">Primario</div>
                                        <div class="px-3 py-1.5 rounded text-white text-xs font-medium" x-bind:style="'background-color: ' + $wire.secondary_color">Secundario</div>
                                        <div class="px-3 py-1.5 rounded text-white text-xs font-medium" x-bind:style="'background-color: ' + $wire.accent_color">Acento</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ================================================ --}}
                {{-- STEP 4: Social + Summary --}}
                {{-- ================================================ --}}
                @if ($step === 4)
                    <div class="p-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="size-10 rounded-xl bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center">
                                <flux:icon.share class="size-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <flux:heading size="lg">Redes Sociales</flux:heading>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">Conecta tus perfiles sociales (opcional)</p>
                            </div>
                        </div>

                        <div class="grid gap-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <flux:field>
                                    <flux:label>Facebook</flux:label>
                                    <flux:input wire:model="facebook_url" type="url" placeholder="https://facebook.com/mi-negocio" />
                                    <flux:error name="facebook_url" />
                                </flux:field>

                                <flux:field>
                                    <flux:label>Instagram</flux:label>
                                    <flux:input wire:model="instagram_url" type="url" placeholder="https://instagram.com/mi-negocio" />
                                    <flux:error name="instagram_url" />
                                </flux:field>

                                <flux:field>
                                    <flux:label>Twitter / X</flux:label>
                                    <flux:input wire:model="twitter_url" type="url" placeholder="https://x.com/mi-negocio" />
                                    <flux:error name="twitter_url" />
                                </flux:field>

                                <flux:field>
                                    <flux:label>WhatsApp</flux:label>
                                    <flux:input wire:model="whatsapp_number" placeholder="+1 234 567 8900" />
                                    <flux:error name="whatsapp_number" />
                                </flux:field>
                            </div>
                        </div>

                        {{-- Final Summary --}}
                        <div class="mt-8 rounded-xl border border-dashed border-blue-300 dark:border-blue-700 bg-blue-50/50 dark:bg-blue-950/20 p-5">
                            <p class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider mb-4">Resumen de tu sitio</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="text-zinc-500 dark:text-zinc-400">Sitio</p>
                                    <p class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $site_name }}</p>
                                </div>
                                <div>
                                    <p class="text-zinc-500 dark:text-zinc-400">Subdominio</p>
                                    <p class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $tenant_id }}.{{ $baseDomain }}</p>
                                </div>
                                <div>
                                    <p class="text-zinc-500 dark:text-zinc-400">Título SEO</p>
                                    <p class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $meta_title }}</p>
                                </div>
                                <div>
                                    <p class="text-zinc-500 dark:text-zinc-400">Colores</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <div class="size-5 rounded-full border border-zinc-200 dark:border-zinc-600" style="background-color: {{ $primary_color }}"></div>
                                        <div class="size-5 rounded-full border border-zinc-200 dark:border-zinc-600" style="background-color: {{ $secondary_color }}"></div>
                                        <div class="size-5 rounded-full border border-zinc-200 dark:border-zinc-600" style="background-color: {{ $accent_color }}"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ================================================ --}}
                {{-- Navigation Footer --}}
                {{-- ================================================ --}}
                <div class="px-8 py-5 bg-zinc-50 dark:bg-zinc-800/50 border-t border-zinc-200 dark:border-zinc-700 flex items-center justify-between">
                    <div>
                        @if ($step > 1)
                            <flux:button type="button" wire:click="previousStep" variant="ghost" icon="arrow-left">
                                Anterior
                            </flux:button>
                        @else
                            <a href="{{ route('tenants.index') }}" wire:navigate>
                                <flux:button type="button" variant="ghost" icon="arrow-left">
                                    Volver
                                </flux:button>
                            </a>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-xs text-zinc-400 dark:text-zinc-500">Paso {{ $step }} de {{ $totalSteps }}</span>
                    </div>

                    <div>
                        @if ($step < $totalSteps)
                            <flux:button type="submit" variant="primary" icon-trailing="arrow-right">
                                Siguiente
                            </flux:button>
                        @else
                            <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="createSite">
                                <span wire:loading.remove wire:target="createSite" class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456z" /></svg>
                                    Crear mi sitio web
                                </span>
                                <span wire:loading wire:target="createSite" class="flex items-center gap-2">
                                    <svg class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                    Creando...
                                </span>
                            </flux:button>
                        @endif
                    </div>
                </div>

            </form>
        </div>

        {{-- AI Hint --}}
        <div class="mt-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary-50 dark:bg-primary-950/30 border border-primary-200 dark:border-primary-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456z" /></svg>
                <p class="text-sm text-primary-700 dark:text-primary-300">
                    Después de crear tu sitio, podrás usar la <strong>IA</strong> para generar tu landing page automáticamente.
                </p>
            </div>
        </div>

    </div>
