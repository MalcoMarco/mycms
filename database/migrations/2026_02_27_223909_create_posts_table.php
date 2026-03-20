<?php

use App\Enums\PostStatus;
use App\Enums\PostType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete()->cascadeOnUpdate();
            $table->index('tenant_id');

            $table->string('slug');
            $table->string('title')->nullable();
            $table->unsignedBigInteger('type_id')->default(PostType::Page->value);
            $table->text('content')->nullable();
            $table->text('css')->nullable();
            $table->text('js')->nullable();

            $table->string('status')->default(PostStatus::Draft->value);
            $table->string('password')->nullable();
            $table->timestamps();

            $table->foreign('type_id')->references('id')->on('posts_types')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
