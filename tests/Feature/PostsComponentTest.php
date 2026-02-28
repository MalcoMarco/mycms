<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Models\Post;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PostsComponentTest extends TestCase
{
    use RefreshDatabase;

    private function createTenantWithOwner(): array
    {
        $user = User::factory()->create();
        $tenant = Tenant::create(['id' => 'test-tenant']);
        $tenant->users()->attach($user->id, ['role' => 'owner']);

        tenancy()->initialize($tenant);

        return [$user, $tenant];
    }

    private function createComponent(string $tenantId, array $overrides = []): Post
    {
        return Post::factory()->ofType(PostType::Component)->create(array_merge([
            'tenant_id' => $tenantId,
        ], $overrides));
    }

    public function test_components_page_is_accessible_by_authenticated_user(): void
    {
        [$user, $tenant] = $this->createTenantWithOwner();

        $this->actingAs($user)
            ->get(route('tenants.posts-component.index', $tenant))
            ->assertOk();
    }

    public function test_components_page_redirects_guests(): void
    {
        $tenant = Tenant::create(['id' => 'test-tenant']);

        $this->get(route('tenants.posts-component.index', $tenant))
            ->assertRedirect();
    }

    public function test_can_create_a_component(): void
    {
        [$user, $tenant] = $this->createTenantWithOwner();

        Livewire::actingAs($user)
            ->test('pages::tenants.posts-component')
            ->call('openCreateModal')
            ->assertSet('showModal', true)
            ->set('formTitle', 'Header Component')
            ->set('formSlug', 'header')
            ->call('save')
            ->assertSet('showModal', false)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('posts', [
            'tenant_id' => $tenant->id,
            'title' => 'Header Component',
            'slug' => 'header',
            'type_id' => PostType::Component->value,
            'status' => PostStatus::Draft->value,
        ]);
    }

    public function test_can_update_a_component(): void
    {
        [$user, $tenant] = $this->createTenantWithOwner();
        $component = $this->createComponent($tenant->id, [
            'title' => 'Old Title',
            'slug' => 'old-slug',
        ]);

        Livewire::actingAs($user)
            ->test('pages::tenants.posts-component')
            ->call('openEditModal', $component->id)
            ->assertSet('editingComponentId', $component->id)
            ->assertSet('formTitle', 'Old Title')
            ->assertSet('formSlug', 'old-slug')
            ->set('formTitle', 'New Title')
            ->set('formSlug', 'new-slug')
            ->call('save')
            ->assertSet('showModal', false)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('posts', [
            'id' => $component->id,
            'title' => 'New Title',
            'slug' => 'new-slug',
        ]);
    }

    public function test_create_validates_required_fields(): void
    {
        [$user, $tenant] = $this->createTenantWithOwner();

        Livewire::actingAs($user)
            ->test('pages::tenants.posts-component')
            ->call('openCreateModal')
            ->set('formTitle', '')
            ->set('formSlug', '')
            ->call('save')
            ->assertHasErrors(['formTitle', 'formSlug']);
    }

    public function test_slug_must_be_unique_per_tenant_and_type(): void
    {
        [$user, $tenant] = $this->createTenantWithOwner();
        $this->createComponent($tenant->id, ['slug' => 'existing-slug']);

        Livewire::actingAs($user)
            ->test('pages::tenants.posts-component')
            ->call('openCreateModal')
            ->set('formTitle', 'New Component')
            ->set('formSlug', 'existing-slug')
            ->call('save')
            ->assertHasErrors(['formSlug']);
    }

    public function test_slug_format_validation(): void
    {
        [$user, $tenant] = $this->createTenantWithOwner();

        Livewire::actingAs($user)
            ->test('pages::tenants.posts-component')
            ->call('openCreateModal')
            ->set('formTitle', 'Test')
            ->set('formSlug', 'Invalid Slug!')
            ->call('save')
            ->assertHasErrors(['formSlug']);
    }

    public function test_can_update_component_status(): void
    {
        [$user, $tenant] = $this->createTenantWithOwner();
        $component = $this->createComponent($tenant->id);

        Livewire::actingAs($user)
            ->test('pages::tenants.posts-component')
            ->call('updateStatus', $component->id, 'published');

        $this->assertDatabaseHas('posts', [
            'id' => $component->id,
            'status' => 'published',
        ]);
    }

    public function test_can_duplicate_a_component(): void
    {
        [$user, $tenant] = $this->createTenantWithOwner();
        $component = $this->createComponent($tenant->id, [
            'title' => 'Original',
            'slug' => 'original',
        ]);

        Livewire::actingAs($user)
            ->test('pages::tenants.posts-component')
            ->call('duplicateComponent', $component->id);

        $this->assertDatabaseCount('posts', 2);
        $this->assertDatabaseHas('posts', [
            'tenant_id' => $tenant->id,
            'title' => 'Original (copia)',
            'type_id' => PostType::Component->value,
            'status' => PostStatus::Draft->value,
        ]);
    }

    public function test_can_delete_a_component(): void
    {
        [$user, $tenant] = $this->createTenantWithOwner();
        $component = $this->createComponent($tenant->id);

        Livewire::actingAs($user)
            ->test('pages::tenants.posts-component')
            ->call('deleteComponent', $component->id);

        $this->assertDatabaseMissing('posts', ['id' => $component->id]);
    }

    public function test_can_apply_bulk_action(): void
    {
        [$user, $tenant] = $this->createTenantWithOwner();
        $c1 = $this->createComponent($tenant->id);
        $c2 = $this->createComponent($tenant->id);

        Livewire::actingAs($user)
            ->test('pages::tenants.posts-component')
            ->set('selectedComponents', [$c1->id, $c2->id])
            ->set('bulkAction', 'publish')
            ->call('applyBulkAction');

        $this->assertDatabaseHas('posts', ['id' => $c1->id, 'status' => 'published']);
        $this->assertDatabaseHas('posts', ['id' => $c2->id, 'status' => 'published']);
    }

    public function test_only_shows_component_type_posts(): void
    {
        [$user, $tenant] = $this->createTenantWithOwner();
        $this->createComponent($tenant->id, ['title' => 'My Component']);
        Post::factory()->ofType(PostType::Page)->create([
            'tenant_id' => $tenant->id,
            'title' => 'My Page',
        ]);

        Livewire::actingAs($user)
            ->test('pages::tenants.posts-component')
            ->assertSee('My Component')
            ->assertDontSee('My Page');
    }

    public function test_search_filters_components(): void
    {
        [$user, $tenant] = $this->createTenantWithOwner();
        $this->createComponent($tenant->id, ['title' => 'Header Widget']);
        $this->createComponent($tenant->id, ['title' => 'Footer Section']);

        Livewire::actingAs($user)
            ->test('pages::tenants.posts-component')
            ->set('search', 'Header')
            ->assertSee('Header Widget')
            ->assertDontSee('Footer Section');
    }

    public function test_status_filter_works(): void
    {
        [$user, $tenant] = $this->createTenantWithOwner();
        $this->createComponent($tenant->id, ['title' => 'Published One', 'status' => PostStatus::Published->value]);
        $this->createComponent($tenant->id, ['title' => 'Draft One', 'status' => PostStatus::Draft->value]);

        Livewire::actingAs($user)
            ->test('pages::tenants.posts-component')
            ->set('statusFilter', 'published')
            ->assertSee('Published One')
            ->assertDontSee('Draft One');
    }
}
