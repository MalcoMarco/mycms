<?php

namespace Database\Factories;

use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => 'test-tenant',
            'title' => fake()->sentence(3),
            'slug' => fake()->unique()->slug(2),
            'type_id' => PostType::Page->value,
            'status' => PostStatus::Draft->value,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PostStatus::Published->value,
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PostStatus::Archived->value,
        ]);
    }

    public function ofType(PostType $type): static
    {
        return $this->state(fn (array $attributes): array => [
            'type_id' => $type->value,
        ]);
    }
}
