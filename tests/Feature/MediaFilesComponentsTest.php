<?php

namespace Tests\Feature;

use App\Models\MediaFile;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class MediaFilesComponentsTest extends TestCase
{
    use RefreshDatabase;

    private function createTenantWithOwner(): array
    {
        $user = User::factory()->create();
        $tenant = Tenant::create(['id' => 'tenant-media']);
        $tenant->users()->attach($user->id, ['role' => 'owner']);

        tenancy()->initialize($tenant);

        return [$user, $tenant];
    }

    public function test_upload_page_is_accessible_for_authenticated_tenant_user(): void
    {
        [$user] = $this->createTenantWithOwner();

        $this->actingAs($user)
            ->get(route('tenants.media.upload'))
            ->assertOk();
    }

    public function test_can_upload_images_and_pdfs_to_s3_and_store_metadata(): void
    {
        Storage::fake('s3');
        [$user, $tenant] = $this->createTenantWithOwner();

        $image = UploadedFile::fake()->image('hero.jpg');
        $pdf = UploadedFile::fake()->create('manual.pdf', 120, 'application/pdf');

        Livewire::actingAs($user)
            ->test('pages::dashboard.media.upload-files')
            ->set('files', [$image, $pdf])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('media_files', 2);

        $this->assertDatabaseHas('media_files', [
            'tenant_id' => $tenant->id,
            'file_name' => 'hero.jpg',
            'file_type' => 'image',
        ]);

        $this->assertDatabaseHas('media_files', [
            'tenant_id' => $tenant->id,
            'file_name' => 'manual.pdf',
            'file_type' => 'pdf',
        ]);

        $storedFiles = MediaFile::query()->get();

        $this->assertTrue(Storage::disk('s3')->exists((string) $storedFiles[0]->storage_path));
        $this->assertTrue(Storage::disk('s3')->exists((string) $storedFiles[1]->storage_path));
    }

    public function test_uploaded_files_component_filters_by_type(): void
    {
        [$user, $tenant] = $this->createTenantWithOwner();

        MediaFile::create([
            'tenant_id' => $tenant->id,
            'file_name' => 'cover-image.jpg',
            'file_type' => 'image',
            'file_size' => 1000,
            'file_url' => 'https://example.com/cover-image.jpg',
            'storage_path' => 'tenants/'.$tenant->id.'/media/cover-image.jpg',
        ]);

        MediaFile::create([
            'tenant_id' => $tenant->id,
            'file_name' => 'catalog.pdf',
            'file_type' => 'pdf',
            'file_size' => 2000,
            'file_url' => 'https://example.com/catalog.pdf',
            'storage_path' => 'tenants/'.$tenant->id.'/media/catalog.pdf',
        ]);

        Livewire::actingAs($user)
            ->test('pages::dashboard.media.uploaded-files')
            ->set('typeFilter', 'pdf')
            ->assertSee('catalog.pdf')
            ->assertDontSee('cover-image.jpg');
    }
}
