<?php

namespace App\Models;

// use Stancl\Tenancy\Contracts\TenantWithDatabase;
// use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant
{
    use HasDomains;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'data',
    ];

    /**
     * Sobreescribe para que use la misma conexión central.
     */
    public function database(): \Stancl\Tenancy\Database\TenantDatabaseManager
    {
        // No necesitamos gestión de base de datos separada
        return new class
        {
            public function getName(): string
            {
                return config('database.default');
            }
        };
    }

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Get the users that belong to this tenant.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<User>
     */
    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tenants_users')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get the owner of this tenant.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<User>
     */
    public function owners(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->users()->wherePivot('role', 'owner');
    }

    /**
     * Get webSettings.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<WebSetting>
     */
    public function webSettings(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(WebSetting::class);
    }
}
