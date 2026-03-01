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
            $table->unsignedBigInteger('type_id')->default(PostType::Page->value);

            $table->string('title')->nullable();
            $table->text('content_head')->nullable();
            $table->text('content_body')->nullable();
            $table->text('content_css')->nullable();
            $table->text('content_js')->nullable();
            $table->json('cdns')->nullable();// Array de URLs de CDNs para este post example: {"styles": ["https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"], "scripts": ["https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"] }

            $table->string('excerpt')->nullable();
            $table->string('status')->default(PostStatus::Draft->value);
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
