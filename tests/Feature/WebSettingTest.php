<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\WebSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_web_setting_belongs_to_tenant(): void
    {
        $tenant = Tenant::create(['id' => 'test-tenant']);
        $setting = WebSetting::factory()->create(['tenant_id' => $tenant->id]);

        $this->assertInstanceOf(Tenant::class, $setting->tenant);
        $this->assertEquals($tenant->id, $setting->tenant->id);
    }

    public function test_web_setting_can_be_created_with_seo_fields(): void
    {
        $tenant = Tenant::create(['id' => 'test-tenant']);

        $setting = WebSetting::factory()->create([
            'tenant_id' => $tenant->id,
            'meta_title' => 'My Landing Page',
            'meta_description' => 'A great landing page',
            'primary_color' => '#FF5733',
        ]);

        $this->assertDatabaseHas('web_settings', [
            'tenant_id' => $tenant->id,
            'meta_title' => 'My Landing Page',
            'meta_description' => 'A great landing page',
            'primary_color' => '#FF5733',
        ]);
    }

    public function test_web_setting_is_deleted_when_tenant_is_deleted(): void
    {
        $tenant = Tenant::create(['id' => 'cascade-tenant']);
        WebSetting::factory()->create(['tenant_id' => $tenant->id]);

        $this->assertDatabaseHas('web_settings', ['tenant_id' => $tenant->id]);

        $tenant->delete();

        $this->assertDatabaseMissing('web_settings', ['tenant_id' => 'cascade-tenant']);
    }

    public function test_web_setting_has_default_robots_value(): void
    {
        $tenant = Tenant::create(['id' => 'robots-tenant']);
        $setting = WebSetting::factory()->create([
            'tenant_id' => $tenant->id,
            'robots' => 'index, follow',
        ]);

        $this->assertEquals('index, follow', $setting->robots);
    }

    public function test_web_setting_social_media_fields_are_nullable(): void
    {
        $tenant = Tenant::create(['id' => 'nullable-tenant']);

        $setting = WebSetting::create([
            'tenant_id' => $tenant->id,
        ]);

        $this->assertNull($setting->facebook_url);
        $this->assertNull($setting->instagram_url);
        $this->assertNull($setting->twitter_url);
        $this->assertNull($setting->whatsapp_number);
    }
}
