<?php

namespace Database\Seeders;

use App\Enums\PostType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PostsTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (PostType::cases() as $type) {
            DB::table('posts_types')->updateOrInsert(
                ['id' => $type->value],
                ['name' => strtolower($type->name)],
            );
        }
    }
}
