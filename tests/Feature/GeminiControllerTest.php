<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Http\Controllers\GeminiController;
use App\Models\Post;
use App\Models\Tenant;
use App\Models\WebSetting;
use Database\Seeders\PostsTypesSeeder;
use Gemini\Laravel\Facades\Gemini;
use Gemini\Responses\GenerativeModel\GenerateContentResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Stancl\Tenancy\Contracts\Tenant as TenantContract;
use Tests\TestCase;

class GeminiControllerTest extends TestCase
{
    use RefreshDatabase;

    private function setUpTenant(): Tenant
    {
        $this->seed(PostsTypesSeeder::class);
        $tenant = Tenant::create(['id' => 'test-tenant']);
        app()->instance(TenantContract::class, $tenant);

        return $tenant;
    }

    private function fakeGeminiResponse(array $data): void
    {
        Gemini::fake([
            GenerateContentResponse::fake([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => json_encode($data)],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);
    }

    private function sampleLandingData(array $overrides = []): array
    {
        return array_merge([
            'meta_title' => 'My Test Site - Landing',
            'meta_description' => 'Welcome to My Test Site',
            'meta_keywords' => 'test, site, landing',
            'content_head' => '<meta name="test">',
            'content_body' => '<div class="bg-primary">Hello</div>',
            'content_css' => '',
            'content_js' => 'console.log("init");',
            'cdns' => ['styles' => [], 'scripts' => []],
        ], $overrides);
    }

    public function test_generate_landing_data_returns_json_response(): void
    {
        $tenant = $this->setUpTenant();

        WebSetting::factory()->create([
            'tenant_id' => $tenant->id,
            'meta_title' => 'My Test Site',
            'meta_description' => 'A test site description',
            'primary_color' => '#FF0000',
        ]);

        $landing = $this->sampleLandingData();
        $this->fakeGeminiResponse($landing);

        $controller = new GeminiController;
        $response = $controller->generateLandingData(new Request);

        $this->assertEquals(200, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertEquals('My Test Site - Landing', $data['meta_title']);
        $this->assertEquals('Welcome to My Test Site', $data['meta_description']);
        $this->assertArrayHasKey('content_body', $data);
        $this->assertArrayHasKey('cdns', $data);
        $this->assertArrayHasKey('styles', $data['cdns']);
        $this->assertArrayHasKey('scripts', $data['cdns']);
    }

    public function test_generate_landing_data_updates_web_setting(): void
    {
        $tenant = $this->setUpTenant();

        WebSetting::factory()->create([
            'tenant_id' => $tenant->id,
            'meta_title' => 'Old Title',
            'meta_description' => 'Old description',
            'meta_keywords' => 'old',
        ]);

        $this->fakeGeminiResponse($this->sampleLandingData([
            'meta_title' => 'New Generated Title',
            'meta_description' => 'New generated description',
            'meta_keywords' => 'new, generated',
        ]));

        $controller = new GeminiController;
        $controller->generateLandingData(new Request);

        $this->assertDatabaseHas('web_settings', [
            'tenant_id' => $tenant->id,
            'meta_title' => 'New Generated Title',
            'meta_description' => 'New generated description',
            'meta_keywords' => 'new, generated',
        ]);
    }

    public function test_generate_landing_data_creates_home_post(): void
    {
        $tenant = $this->setUpTenant();

        WebSetting::factory()->create([
            'tenant_id' => $tenant->id,
            'meta_title' => 'My Site',
        ]);

        $landing = $this->sampleLandingData();
        $this->fakeGeminiResponse($landing);

        $controller = new GeminiController;
        $controller->generateLandingData(new Request);

        $this->assertDatabaseHas('posts', [
            'tenant_id' => $tenant->id,
            'slug' => 'home',
            'type_id' => PostType::Page->value,
            'title' => $landing['meta_title'],
            'content_body' => $landing['content_body'],
            'status' => PostStatus::Published->value,
        ]);
    }

    public function test_generate_landing_data_updates_existing_home_post(): void
    {
        $tenant = $this->setUpTenant();

        WebSetting::factory()->create([
            'tenant_id' => $tenant->id,
            'meta_title' => 'My Site',
        ]);

        Post::factory()->create([
            'tenant_id' => $tenant->id,
            'slug' => 'home',
            'type_id' => PostType::Page,
            'title' => 'Old Landing',
            'content_body' => '<div>Old</div>',
        ]);

        $this->fakeGeminiResponse($this->sampleLandingData([
            'meta_title' => 'Updated Landing',
            'content_body' => '<div>New Content</div>',
        ]));

        $controller = new GeminiController;
        $controller->generateLandingData(new Request);

        $this->assertCount(1, Post::where('tenant_id', $tenant->id)->where('slug', 'home')->get());
        $this->assertDatabaseHas('posts', [
            'tenant_id' => $tenant->id,
            'slug' => 'home',
            'title' => 'Updated Landing',
            'content_body' => '<div>New Content</div>',
        ]);
    }

    public function test_generate_landing_data_fails_without_web_setting(): void
    {
        $this->setUpTenant();

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $controller = new GeminiController;
        $controller->generateLandingData(new Request);
    }

    public function test_generate_landing_data_with_optional_social_fields(): void
    {
        $tenant = $this->setUpTenant();

        WebSetting::factory()->create([
            'tenant_id' => $tenant->id,
            'meta_title' => 'Social Site',
            'facebook_url' => 'https://facebook.com/test',
            'instagram_url' => 'https://instagram.com/test',
            'meta_description' => null,
            'meta_keywords' => null,
        ]);

        $this->fakeGeminiResponse($this->sampleLandingData());

        $controller = new GeminiController;
        $response = $controller->generateLandingData(new Request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('content_body', $response->getData(true));
    }
}
