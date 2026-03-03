<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Models\WebSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WebSettingsEditTest extends TestCase
{
    use RefreshDatabase;

    private function createTenantWithOwner(): array
    {
        $user = User::factory()->create();
        $tenant = Tenant::create(['id' => 'test-tenant']);
        $tenant->users()->attach($user->id, ['role' => 'owner']);

        return [$user, $tenant];
    }

    public function test_web_settings_page_is_accessible_by_authenticated_user(): void
    {
        [$user, $tenant] = $this->createTenantWithOwner();

        $this->actingAs($user)
            ->get(route('tenants.web-settings.edit', $tenant))
            ->assertOk();
    }

    public function test_web_settings_page_redirects_guests(): void
    {
        $tenant = Tenant::create(['id' => 'test-tenant']);

        $this->get(route('tenants.web-settings.edit', $tenant))
            ->assertRedirect();
    }

    public function test_web_settings_can_be_saved(): void
    {
        [$user, $tenant] = $this->createTenantWithOwner();

        Livewire::actingAs($user)
            ->test('web-settings-edit', ['tenant' => $tenant])
            ->set('meta_title', 'Mi Landing Page')
            ->set('meta_description', 'Descripción de prueba')
            ->set('primary_color', '#FF5733')
            ->set('facebook_url', 'https://facebook.com/test')
            ->call('save')
            ->assertDispatched('web-settings-saved');

        $this->assertDatabaseHas('web_settings', [
            'tenant_id' => $tenant->id,
            'meta_title' => 'Mi Landing Page',
            'meta_description' => 'Descripción de prueba',
            'primary_color' => '#FF5733',
            'facebook_url' => 'https://facebook.com/test',
        ]);
    }

    public function test_web_settings_loads_existing_data(): void
    {
        [$user, $tenant] = $this->createTenantWithOwner();

        WebSetting::factory()->create([
            'tenant_id' => $tenant->id,
            'meta_title' => 'Existing Title',
            'primary_color' => '#123456',
        ]);

        Livewire::actingAs($user)
            ->test('web-settings-edit', ['tenant' => $tenant])
            ->assertSet('meta_title', 'Existing Title')
            ->assertSet('primary_color', '#123456');
    }

    public function test_web_settings_updates_existing_record(): void
    {
        [$user, $tenant] = $this->createTenantWithOwner();

        WebSetting::factory()->create([
            'tenant_id' => $tenant->id,
            'meta_title' => 'Old Title',
        ]);

        Livewire::actingAs($user)
            ->test('web-settings-edit', ['tenant' => $tenant])
            ->set('meta_title', 'New Title')
            ->call('save')
            ->assertDispatched('web-settings-saved');

        $this->assertDatabaseHas('web_settings', [
            'tenant_id' => $tenant->id,
            'meta_title' => 'New Title',
        ]);

        $this->assertDatabaseCount('web_settings', 1);
    }

    public function test_web_settings_validates_url_fields(): void
    {
        [$user, $tenant] = $this->createTenantWithOwner();

        Livewire::actingAs($user)
            ->test('web-settings-edit', ['tenant' => $tenant])
            ->set('facebook_url', 'not-a-valid-url')
            ->call('save')
            ->assertHasErrors(['facebook_url']);
    }

    public function test_web_settings_validates_max_length(): void
    {
        [$user, $tenant] = $this->createTenantWithOwner();

        Livewire::actingAs($user)
            ->test('web-settings-edit', ['tenant' => $tenant])
            ->set('meta_title', str_repeat('a', 256))
            ->call('save')
            ->assertHasErrors(['meta_title']);
    }

    public function test_web_settings_allows_empty_fields(): void
    {
        [$user, $tenant] = $this->createTenantWithOwner();

        Livewire::actingAs($user)
            ->test('web-settings-edit', ['tenant' => $tenant])
            ->call('save')
            ->assertHasNoErrors();
    }

    public function test_web_settings_can_save_global_cdn_urls(): void
    {
        [$user, $tenant] = $this->createTenantWithOwner();

        Livewire::actingAs($user)
            ->test('web-settings-edit', ['tenant' => $tenant])
            ->set('global_cdn_scripts', ['https://cdn.example.com/app.js'])
            ->set('global_cdn_styles', ['https://cdn.example.com/app.css'])
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('web-settings-saved');

        $setting = WebSetting::where('tenant_id', $tenant->id)->first();
        $cdnUrls = $setting->global_cdn_urls;

        $this->assertEquals(['https://cdn.example.com/app.js'], $cdnUrls['scripts']);
        $this->assertEquals(['https://cdn.example.com/app.css'], $cdnUrls['styles']);
    }

    public function test_web_settings_loads_existing_cdn_urls(): void
    {
        [$user, $tenant] = $this->createTenantWithOwner();

        WebSetting::factory()->create([
            'tenant_id' => $tenant->id,
            'global_cdn_urls' => [
                'scripts' => ['https://cdn.example.com/lib.js'],
                'styles' => ['https://cdn.example.com/lib.css'],
            ],
        ]);

        Livewire::actingAs($user)
            ->test('web-settings-edit', ['tenant' => $tenant])
            ->assertSet('global_cdn_scripts', ['https://cdn.example.com/lib.js'])
            ->assertSet('global_cdn_styles', ['https://cdn.example.com/lib.css']);
    }

    public function test_web_settings_validates_cdn_urls(): void
    {
        [$user, $tenant] = $this->createTenantWithOwner();

        Livewire::actingAs($user)
            ->test('web-settings-edit', ['tenant' => $tenant])
            ->set('global_cdn_scripts', ['not-a-valid-url'])
            ->call('save')
            ->assertHasErrors(['global_cdn_scripts.0']);
    }

    public function test_web_settings_can_add_and_remove_cdn_scripts(): void
    {
        [$user, $tenant] = $this->createTenantWithOwner();

        Livewire::actingAs($user)
            ->test('web-settings-edit', ['tenant' => $tenant])
            ->call('addCdnScript')
            ->assertSet('global_cdn_scripts', [''])
            ->call('addCdnScript')
            ->assertCount('global_cdn_scripts', 2)
            ->call('removeCdnScript', 0)
            ->assertCount('global_cdn_scripts', 1);
    }

    public function test_web_settings_can_add_and_remove_cdn_styles(): void
    {
        [$user, $tenant] = $this->createTenantWithOwner();

        Livewire::actingAs($user)
            ->test('web-settings-edit', ['tenant' => $tenant])
            ->call('addCdnStyle')
            ->assertSet('global_cdn_styles', [''])
            ->call('addCdnStyle')
            ->assertCount('global_cdn_styles', 2)
            ->call('removeCdnStyle', 0)
            ->assertCount('global_cdn_styles', 1);
    }
}
