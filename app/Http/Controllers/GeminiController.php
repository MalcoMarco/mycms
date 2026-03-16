<?php

namespace App\Http\Controllers;

use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Models\Post;
use App\Models\WebSetting;
use Gemini\Data\GenerationConfig;
use Gemini\Enums\ResponseMimeType;
use Gemini\Laravel\Facades\Gemini;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GeminiController extends Controller
{
    public function generateLandingData(Request $request): JsonResponse
    {
        $webSetting = WebSetting::where('tenant_id', tenant('id'))->firstOrFail();

        $siteContext = $this->buildSiteContext($webSetting);
        $prompt = $this->buildPrompt($siteContext);

        $result = Gemini::generativeModel(model: 'gemini-2.5-flash')
            ->withGenerationConfig(new GenerationConfig(
                responseMimeType: ResponseMimeType::APPLICATION_JSON,
            ))
            ->generateContent($prompt);

        $data = $result->json(associative: true);

        $this->updateWebSetting($webSetting, $data);
        $this->updateHomePage($data);

        return response()->json($data);
    }

    private function buildSiteContext(WebSetting $webSetting): string
    {
        $lines = ["Website Name: {$webSetting->meta_title}"];

        $fields = [
            'meta_description' => 'Description',
            'meta_keywords' => 'Keywords',
            'primary_color' => 'Primary Color',
            'secondary_color' => 'Secondary Color',
            'accent_color' => 'Accent Color',
            'facebook_url' => 'Facebook',
            'instagram_url' => 'Instagram',
            'twitter_url' => 'Twitter/X',
            'linkedin_url' => 'LinkedIn',
            'youtube_url' => 'YouTube',
            'tiktok_url' => 'TikTok',
            'whatsapp_number' => 'WhatsApp',
        ];

        foreach ($fields as $field => $label) {
            if ($webSetting->{$field}) {
                $lines[] = "{$label}: {$webSetting->{$field}}";
            }
        }

        return implode("\n", $lines);
    }

    private function buildPrompt(string $siteContext): string
    {
        return <<<PROMPT
        You are an expert web designer. Generate a complete, professional, modern landing page for the following website.

        === WEBSITE INFO ===
        {$siteContext}

        === DESIGN REQUIREMENTS ===
        - Use Tailwind CSS v4 utility classes exclusively for styling.
        - For colors, use semantic classes: text-primary, bg-primary, border-primary, text-secondary, bg-secondary, border-secondary, text-accent, bg-accent, border-accent. These are defined via Tailwind's @theme — do NOT include color definitions.
        - The page must be fully responsive (mobile-first) with a clean, modern layout.
        - Include these sections: hero with CTA, features/services, about, and a footer with social links (only the ones provided above).
        - Use placeholder image URLs from https://placehold.co (e.g. https://placehold.co/600x400).
        - All text content must match the website's language and industry inferred from its name and description.
        - Add smooth scroll behavior and subtle hover transitions.

        === OUTPUT FORMAT ===
        Return a single JSON object with exactly these keys:
        {
            "meta_title": "SEO-optimized page title (50-60 chars)",
            "meta_description": "SEO meta description summarizing the page (150-160 chars)",
            "meta_keywords": "comma-separated relevant SEO keywords (8-12 keywords)",
            "content_head": "Extra <head> content: meta tags, preconnect links, etc. Do NOT include <title> or charset.",
            "content_body": "content of <body>, start with <body> tag. Must be a single self-contained landing page using only Tailwind CSS v4 classes. Include all sections listed above.",
            "content_css": "Any custom CSS beyond Tailwind utilities (minimal, only if strictly needed). Don't comment the code. Leave empty string if not needed. don't include <style> tags, just the CSS code.",
            "content_js": "Vanilla JavaScript for interactivity. You MAY include comments. IMPORTANT: The code must be properly formatted with real newline characters escaped as '\\n'. Do NOT return the code in a single compressed line. Ensure that single-line comments (//) are always followed by an escaped newline (\\n) so they do not comment out the rest of the code. Do NOT include <script> tags.",
            "cdns": {
                "styles": ["CDN URLs for external stylesheets or fonts if needed, e.g. Google Fonts"],
                "scripts": ["CDN URLs for external scripts if needed. Avoid unnecessary libraries. add cdn of tailwind 4 here (https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4)"]
            }
        }
        PROMPT;
    }

    private function updateWebSetting(WebSetting $webSetting, array $data): void
    {
        $webSetting->update([
            'meta_title' => $data['meta_title'] ?? $webSetting->meta_title,
            'meta_description' => $data['meta_description'] ?? $webSetting->meta_description,
            'meta_keywords' => $data['meta_keywords'] ?? $webSetting->meta_keywords,
        ]);
    }

    private function updateHomePage(array $data): void
    {
        Post::updateOrCreate(
            [
                'slug' => 'home',
                'tenant_id' => tenant('id'),
            ],
            [
                'type_id' => PostType::Page,
                'title' => $data['meta_title'] ?? 'Landing Page',
                'content_head' => $data['content_head'] ?? null,
                'content_body' => $data['content_body'] ?? null,
                'content_css' => $data['content_css'] ?? null,
                'content_js' => $data['content_js'] ?? null,
                'cdns' => $data['cdns'] ?? null,
                'status' => PostStatus::Published,
            ]
        );
    }
}
