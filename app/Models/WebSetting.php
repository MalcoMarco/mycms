<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebSetting extends Model
{
    /** @use HasFactory<\Database\Factories\WebSettingFactory> */
    use BelongsToTenant, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_title',
        'og_description',
        'og_image',
        'canonical_url',
        'robots',
        'favicon',
        'facebook_url',
        'instagram_url',
        'twitter_url',
        'linkedin_url',
        'youtube_url',
        'tiktok_url',
        'whatsapp_number',
        'primary_color',
        'secondary_color',
        'accent_color',
        'logo',
        'logo_dark',
        'google_analytics_id',
        'custom_head_scripts',
        'custom_body_scripts',
    ];

    /**
     * Get the tenant that owns this web setting.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Tenant>
     */
    public function tenant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
