<?php

namespace App\Models;

use App\Enums\PostStatus;
use App\Enums\PostType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Post extends Model
{
    use HasFactory, BelongsToTenant;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'slug',
        'type_id',
        'title',
        'content_head',
        'content_body',
        'content_css',
        'content_js',
        'excerpt',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type_id' => PostType::class,
            'status' => PostStatus::class,
        ];
    }

    /**
     * Get the tenant that owns the post.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope posts by type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Post>  $query
     */
    public function scopeOfType(\Illuminate\Database\Eloquent\Builder $query, PostType $type): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('type_id', $type->value);
    }
}
