<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CreateTenantWizardTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_tenant_page_is_accessible_by_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('tenants.create'))
            ->assertOk();
    }

    public function test_create_tenant_page_redirects_guests(): void
    {
        $this->get(route('tenants.create'))
            ->assertRedirect();
    }

    public function test_step_one_validates_required_fields(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('pages::dashboard.create-tenant')
            ->call('nextStep')
            ->assertHasErrors(['tenant_id', 'site_name']);
    }

    public function test_step_one_validates_tenant_id_format(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('pages::dashboard.create-tenant')
            ->set('site_name', 'Mi Sitio')
            ->set('tenant_id', 'INVALID ID!')
            ->call('nextStep')
            ->assertHasErrors(['tenant_id']);
    }

    public function test_step_one_validates_tenant_id_min_length(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('pages::dashboard.create-tenant')
            ->set('site_name', 'Mi Sitio')
            ->set('tenant_id', 'ab')
            ->call('nextStep')
            ->assertHasErrors(['tenant_id']);
    }

    public function test_step_one_advances_with_valid_data(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('pages::dashboard.create-tenant')
            ->set('site_name', 'Mi Sitio')
            ->set('tenant_id', 'mi-sitio')
            ->call('nextStep')
            ->assertSet('step', 2)
            ->assertHasNoErrors();
    }

    public function test_step_two_validates_required_seo_fields(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('pages::dashboard.create-tenant')
            ->set('step', 2)
            ->call('nextStep')
            ->assertHasErrors(['meta_title', 'meta_description']);
    }

    public function test_step_two_validates_meta_title_max_length(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('pages::dashboard.create-tenant')
            ->set('step', 2)
            ->set('meta_title', str_repeat('a', 71))
            ->set('meta_description', 'Descripción válida')
            ->call('nextStep')
            ->assertHasErrors(['meta_title']);
    }

    public function test_step_two_advances_with_valid_data(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('pages::dashboard.create-tenant')
            ->set('step', 2)
            ->set('meta_title', 'Mi Negocio - Servicios')
            ->set('meta_description', 'Una descripción válida del negocio')
            ->call('nextStep')
            ->assertSet('step', 3)
            ->assertHasNoErrors();
    }

    public function test_step_three_validates_color_format(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('pages::dashboard.create-tenant')
            ->set('step', 3)
            ->set('primary_color', 'not-a-color')
            ->call('nextStep')
            ->assertHasErrors(['primary_color']);
    }

    public function test_step_three_advances_with_valid_colors(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('pages::dashboard.create-tenant')
            ->set('step', 3)
            ->set('primary_color', '#4f46e5')
            ->set('secondary_color', '#0d9488')
            ->set('accent_color', '#f59e0b')
            ->call('nextStep')
            ->assertSet('step', 4)
            ->assertHasNoErrors();
    }

    public function test_previous_step_goes_back(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('pages::dashboard.create-tenant')
            ->set('step', 3)
            ->call('previousStep')
            ->assertSet('step', 2);
    }

    public function test_previous_step_does_not_go_below_one(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('pages::dashboard.create-tenant')
            ->set('step', 1)
            ->call('previousStep')
            ->assertSet('step', 1);
    }

    public function test_go_to_step_only_allows_previous_steps(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('pages::dashboard.create-tenant')
            ->set('step', 2)
            ->call('goToStep', 1)
            ->assertSet('step', 1);
    }

    public function test_go_to_step_does_not_skip_ahead(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('pages::dashboard.create-tenant')
            ->set('step', 2)
            ->call('goToStep', 4)
            ->assertSet('step', 2);
    }

    public function test_create_site_creates_tenant_and_related_records(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('pages::dashboard.create-tenant')
            ->set('step', 4)
            ->set('tenant_id', 'nuevo-sitio')
            ->set('site_name', 'Nuevo Sitio Web')
            ->set('meta_title', 'Nuevo Sitio - Bienvenidos')
            ->set('meta_description', 'La mejor página web del mundo')
            ->set('meta_keywords', 'sitio, web, nuevo')
            ->set('primary_color', '#4f46e5')
            ->set('secondary_color', '#0d9488')
            ->set('accent_color', '#f59e0b')
            ->set('facebook_url', 'https://facebook.com/nuevo-sitio')
            ->set('whatsapp_number', '+1234567890')
            ->call('createSite')
            ->assertRedirect(route('tenants.index'));

        $this->assertDatabaseHas('tenants', [
            'id' => 'nuevo-sitio',
        ]);

        $this->assertDatabaseHas('domains', [
            'domain' => 'nuevo-sitio.'.parse_url(config('app.url'), PHP_URL_HOST),
        ]);

        $this->assertDatabaseHas('tenants_users', [
            'user_id' => $user->id,
            'tenant_id' => 'nuevo-sitio',
            'role' => 'owner',
        ]);

        $this->assertDatabaseHas('web_settings', [
            'tenant_id' => 'nuevo-sitio',
            'meta_title' => 'Nuevo Sitio - Bienvenidos',
            'meta_description' => 'La mejor página web del mundo',
            'primary_color' => '#4f46e5',
            'secondary_color' => '#0d9488',
            'accent_color' => '#f59e0b',
            'facebook_url' => 'https://facebook.com/nuevo-sitio',
            'whatsapp_number' => '+1234567890',
        ]);

        $this->assertDatabaseHas('posts', [
            'tenant_id' => 'nuevo-sitio',
            'slug' => 'home',
            'type_id' => PostType::Page->value,
            'title' => 'Nuevo Sitio Web',
            'status' => PostStatus::Draft->value,
        ]);
    }

    public function test_create_site_validates_unique_tenant_id(): void
    {
        $user = User::factory()->create();

        \App\Models\Tenant::create(['id' => 'existing-tenant']);

        Livewire::actingAs($user)
            ->test('pages::dashboard.create-tenant')
            ->set('step', 4)
            ->set('tenant_id', 'existing-tenant')
            ->set('site_name', 'Test')
            ->set('meta_title', 'Test Title')
            ->set('meta_description', 'Test description')
            ->set('primary_color', '#4f46e5')
            ->set('secondary_color', '#0d9488')
            ->set('accent_color', '#f59e0b')
            ->call('createSite')
            ->assertHasErrors(['tenant_id']);
    }

    public function test_create_site_with_social_urls_validates(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('pages::dashboard.create-tenant')
            ->set('step', 4)
            ->set('tenant_id', 'test-social')
            ->set('site_name', 'Social Test')
            ->set('meta_title', 'Social Test')
            ->set('meta_description', 'Testing social URLs')
            ->set('primary_color', '#4f46e5')
            ->set('secondary_color', '#0d9488')
            ->set('accent_color', '#f59e0b')
            ->set('facebook_url', 'not-a-url')
            ->call('createSite')
            ->assertHasErrors(['facebook_url']);
    }

    public function test_create_site_with_minimal_data(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('pages::dashboard.create-tenant')
            ->set('step', 4)
            ->set('tenant_id', 'minimal-site')
            ->set('site_name', 'Sitio Mínimo')
            ->set('meta_title', 'Sitio Mínimo')
            ->set('meta_description', 'Solo los campos obligatorios')
            ->set('primary_color', '#4f46e5')
            ->set('secondary_color', '#0d9488')
            ->set('accent_color', '#f59e0b')
            ->call('createSite')
            ->assertRedirect(route('tenants.index'));

        $this->assertDatabaseHas('tenants', ['id' => 'minimal-site']);
        $this->assertDatabaseHas('posts', ['tenant_id' => 'minimal-site', 'slug' => 'home']);
    }
}
