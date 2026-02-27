<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WebSetting>
 */
class WebSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'meta_title' => fake()->sentence(4),
            'meta_description' => fake()->paragraph(),
            'meta_keywords' => implode(', ', fake()->words(5)),
            'og_title' => fake()->sentence(4),
            'og_description' => fake()->paragraph(),
            'og_image' => fake()->imageUrl(1200, 630),
            'canonical_url' => fake()->url(),
            'robots' => 'index, follow',
            'favicon' => fake()->imageUrl(32, 32),
            'facebook_url' => 'https://facebook.com/' . fake()->userName(),
            'instagram_url' => 'https://instagram.com/' . fake()->userName(),
            'twitter_url' => 'https://x.com/' . fake()->userName(),
            'linkedin_url' => 'https://linkedin.com/in/' . fake()->userName(),
            'youtube_url' => 'https://youtube.com/@' . fake()->userName(),
            'tiktok_url' => 'https://tiktok.com/@' . fake()->userName(),
            'whatsapp_number' => fake()->e164PhoneNumber(),
            'primary_color' => fake()->hexColor(),
            'secondary_color' => fake()->hexColor(),
            'accent_color' => fake()->hexColor(),
            'logo' => fake()->imageUrl(200, 60),
            'logo_dark' => fake()->imageUrl(200, 60),
            'google_analytics_id' => 'G-' . fake()->bothify('??########'),
            'custom_head_scripts' => null,
            'custom_body_scripts' => null,
        ];
    }
}
