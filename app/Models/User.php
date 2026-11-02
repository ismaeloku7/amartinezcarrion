<?php

namespace App\Models;

use App\Models\Cards\CardsEnrollment;
use App\Models\Mdp\MdpEnrollment;
use App\Models\Tests\TestTake;
use App\Services\AccessService;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use NotificationChannels\WebPush\HasPushSubscriptions;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use Billable, HasFactory, HasPushSubscriptions, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'locale', 'timezone', 'is_admin'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function entitlements(): HasMany
    {
        return $this->hasMany(Entitlement::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function mdpEnrollment(): HasOne
    {
        return $this->hasOne(MdpEnrollment::class);
    }

    public function cardsEnrollment(): HasOne
    {
        return $this->hasOne(CardsEnrollment::class);
    }

    public function testTakes(): HasMany
    {
        return $this->hasMany(TestTake::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return (bool) $this->is_admin;
    }

    public function hasAccessTo(string $productKey): bool
    {
        return app(AccessService::class)->hasAccess($this, $productKey);
    }
}
