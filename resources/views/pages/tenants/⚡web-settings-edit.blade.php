<?php

use App\Models\Tenant;
use App\Models\WebSetting;
use Livewire\Attributes\Layout;
use Livewire\Component;

new class extends Component
{
    #[Layout('layouts::tenantsApp')]

    #[Locked] 
    public string $tenant;

    // SEO
    public string $meta_title = '';
    public string $meta_description = '';
    public string $meta_keywords = '';
    public string $og_title = '';
    public string $og_description = '';
    public string $og_image = '';
    public string $canonical_url = '';
    public string $robots = 'index, follow';
    public string $favicon = '';

    // Social media
    public string $facebook_url = '';
    public string $instagram_url = '';
    public string $twitter_url = '';
    public string $linkedin_url = '';
    public string $youtube_url = '';
    public string $tiktok_url = '';
    public string $whatsapp_number = '';

    // Branding & colors
    public string $primary_color = '';
    public string $secondary_color = '';
    public string $accent_color = '';
    public string $logo = '';
    public string $logo_dark = '';

    // Analytics & scripts
    public string $google_analytics_id = '';
    public string $custom_head_scripts = '';
    public string $custom_body_scripts = '';

    public function mount(): void
    {
        $this->tenant = tenant('id');

        $setting = WebSetting::where('tenant_id', $this->tenant)->first();

        if ($setting) {
            $this->meta_title = $setting->meta_title ?? '';
            $this->meta_description = $setting->meta_description ?? '';
            $this->meta_keywords = $setting->meta_keywords ?? '';
            $this->og_title = $setting->og_title ?? '';
            $this->og_description = $setting->og_description ?? '';
            $this->og_image = $setting->og_image ?? '';
            $this->canonical_url = $setting->canonical_url ?? '';
            $this->robots = $setting->robots ?? 'index, follow';
            $this->favicon = $setting->favicon ?? '';
            $this->facebook_url = $setting->facebook_url ?? '';
            $this->instagram_url = $setting->instagram_url ?? '';
            $this->twitter_url = $setting->twitter_url ?? '';
            $this->linkedin_url = $setting->linkedin_url ?? '';
            $this->youtube_url = $setting->youtube_url ?? '';
            $this->tiktok_url = $setting->tiktok_url ?? '';
            $this->whatsapp_number = $setting->whatsapp_number ?? '';
            $this->primary_color = $setting->primary_color ?? '';
            $this->secondary_color = $setting->secondary_color ?? '';
            $this->accent_color = $setting->accent_color ?? '';
            $this->logo = $setting->logo ?? '';
            $this->logo_dark = $setting->logo_dark ?? '';
            $this->google_analytics_id = $setting->google_analytics_id ?? '';
            $this->custom_head_scripts = $setting->custom_head_scripts ?? '';
            $this->custom_body_scripts = $setting->custom_body_scripts ?? '';
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'meta_keywords' => ['nullable', 'string', 'max:500'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string', 'max:1000'],
            'og_image' => ['nullable', 'string', 'url', 'max:2048'],
            'canonical_url' => ['nullable', 'string', 'url', 'max:2048'],
            'robots' => ['nullable', 'string', 'max:100'],
            'favicon' => ['nullable', 'string', 'url', 'max:2048'],
            'facebook_url' => ['nullable', 'string', 'url', 'max:2048'],
            'instagram_url' => ['nullable', 'string', 'url', 'max:2048'],
            'twitter_url' => ['nullable', 'string', 'url', 'max:2048'],
            'linkedin_url' => ['nullable', 'string', 'url', 'max:2048'],
            'youtube_url' => ['nullable', 'string', 'url', 'max:2048'],
            'tiktok_url' => ['nullable', 'string', 'url', 'max:2048'],
            'whatsapp_number' => ['nullable', 'string', 'max:20'],
            'primary_color' => ['nullable', 'string', 'max:20'],
            'secondary_color' => ['nullable', 'string', 'max:20'],
            'accent_color' => ['nullable', 'string', 'max:20'],
            'logo' => ['nullable', 'string', 'url', 'max:2048'],
            'logo_dark' => ['nullable', 'string', 'url', 'max:2048'],
            'google_analytics_id' => ['nullable', 'string', 'max:50'],
            'custom_head_scripts' => ['nullable', 'string', 'max:5000'],
            'custom_body_scripts' => ['nullable', 'string', 'max:5000'],
        ]);

        WebSetting::updateOrCreate(
            ['tenant_id' => $this->tenant],
            $validated,
        );

        $this->dispatch('web-settings-saved');
    }
};
?>

<section class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="lg">{{ __('Configuración Web') }}</flux:heading>
            <flux:subheading>{{ __('Administra el SEO, redes sociales y branding de tu landing page.') }}</flux:subheading>
        </div>
    </div>

    @if (session()->has('message'))
        <flux:callout variant="success" icon="check-circle">
            {{ session('message') }}
        </flux:callout>
    @endif

    <form wire:submit="save" class="space-y-8">

        {{-- SEO --}}
        <div class="rounded-lg border border-neutral-200 p-6 dark:border-neutral-700">
            <flux:heading size="md" class="mb-4">{{ __('SEO') }}</flux:heading>
            <div class="space-y-4">
                <flux:input wire:model="meta_title" :label="__('Meta Title')" placeholder="Mi Landing Page" />
                <flux:textarea wire:model="meta_description" :label="__('Meta Description')" rows="3" placeholder="Descripción breve para motores de búsqueda..." />
                <flux:input wire:model="meta_keywords" :label="__('Meta Keywords')" placeholder="landing, negocio, empresa" />
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <flux:input wire:model="og_title" :label="__('OG Title')" placeholder="Título para redes sociales" />
                    <flux:input wire:model="og_image" :label="__('OG Image URL')" type="url" placeholder="https://ejemplo.com/imagen.jpg" />
                </div>
                <flux:textarea wire:model="og_description" :label="__('OG Description')" rows="2" placeholder="Descripción para redes sociales..." />
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <flux:input wire:model="canonical_url" :label="__('Canonical URL')" type="url" placeholder="https://ejemplo.com" />
                    <flux:input wire:model="robots" :label="__('Robots')" placeholder="index, follow" />
                </div>
                <flux:input wire:model="favicon" :label="__('Favicon URL')" type="url" placeholder="https://ejemplo.com/favicon.ico" />
            </div>
        </div>

        {{-- Social media --}}
        <div class="rounded-lg border border-neutral-200 p-6 dark:border-neutral-700">
            <flux:heading size="md" class="mb-4">{{ __('Redes Sociales') }}</flux:heading>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <flux:input wire:model="facebook_url" :label="__('Facebook')" type="url" placeholder="https://facebook.com/mi-pagina" icon="link" />
                <flux:input wire:model="instagram_url" :label="__('Instagram')" type="url" placeholder="https://instagram.com/mi-cuenta" icon="link" />
                <flux:input wire:model="twitter_url" :label="__('Twitter / X')" type="url" placeholder="https://x.com/mi-cuenta" icon="link" />
                <flux:input wire:model="linkedin_url" :label="__('LinkedIn')" type="url" placeholder="https://linkedin.com/in/mi-perfil" icon="link" />
                <flux:input wire:model="youtube_url" :label="__('YouTube')" type="url" placeholder="https://youtube.com/@mi-canal" icon="link" />
                <flux:input wire:model="tiktok_url" :label="__('TikTok')" type="url" placeholder="https://tiktok.com/@mi-cuenta" icon="link" />
                <flux:input wire:model="whatsapp_number" :label="__('WhatsApp')" placeholder="+52 1234567890" icon="phone" />
            </div>
        </div>

        {{-- Branding & colors --}}
        <div class="rounded-lg border border-neutral-200 p-6 dark:border-neutral-700">
            <flux:heading size="md" class="mb-4">{{ __('Branding y Colores') }}</flux:heading>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <flux:field>
                    <flux:label>{{ __('Color Primario') }}</flux:label>
                    <div class="flex items-center gap-2">
                        <input type="color" wire:model.live="primary_color" class="h-10 w-10 cursor-pointer rounded border border-neutral-300 dark:border-neutral-600" />
                        <flux:input wire:model.live="primary_color" placeholder="#3B82F6" class="flex-1" />
                    </div>
                    <flux:error name="primary_color" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Color Secundario') }}</flux:label>
                    <div class="flex items-center gap-2">
                        <input type="color" wire:model.live="secondary_color" class="h-10 w-10 cursor-pointer rounded border border-neutral-300 dark:border-neutral-600" />
                        <flux:input wire:model.live="secondary_color" placeholder="#10B981" class="flex-1" />
                    </div>
                    <flux:error name="secondary_color" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Color Acento') }}</flux:label>
                    <div class="flex items-center gap-2">
                        <input type="color" wire:model.live="accent_color" class="h-10 w-10 cursor-pointer rounded border border-neutral-300 dark:border-neutral-600" />
                        <flux:input wire:model.live="accent_color" placeholder="#F59E0B" class="flex-1" />
                    </div>
                    <flux:error name="accent_color" />
                </flux:field>
            </div>
            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                <flux:input wire:model="logo" :label="__('Logo URL')" type="url" placeholder="https://ejemplo.com/logo.png" />
                <flux:input wire:model="logo_dark" :label="__('Logo Dark URL')" type="url" placeholder="https://ejemplo.com/logo-dark.png" />
            </div>
        </div>

        {{-- Analytics & scripts --}}
        <div class="rounded-lg border border-neutral-200 p-6 dark:border-neutral-700">
            <flux:heading size="md" class="mb-4">{{ __('Analytics y Scripts') }}</flux:heading>
            <div class="space-y-4">
                <flux:input wire:model="google_analytics_id" :label="__('Google Analytics ID')" placeholder="G-XXXXXXXXXX" />
                <flux:textarea wire:model="custom_head_scripts" :label="__('Scripts en Head')" rows="3" placeholder="<!-- Scripts personalizados para el <head> -->" />
                <flux:textarea wire:model="custom_body_scripts" :label="__('Scripts en Body')" rows="3" placeholder="<!-- Scripts personalizados para el <body> -->" />
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex items-center gap-4">
            <flux:button variant="primary" type="submit">
                {{ __('Guardar Configuración') }}
            </flux:button>

            <x-action-message class="me-3" on="web-settings-saved">
                {{ __('Guardado.') }}
            </x-action-message>
        </div>
    </form>
</section>
