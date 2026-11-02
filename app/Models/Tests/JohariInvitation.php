<?php

namespace App\Models\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JohariInvitation extends Model
{
    protected $fillable = ['take_id', 'token', 'label', 'max_uses', 'uses', 'expires_at', 'revoked_at'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'revoked_at' => 'datetime'];
    }

    public function take(): BelongsTo
    {
        return $this->belongsTo(TestTake::class, 'take_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(JohariResponse::class, 'invitation_id');
    }

    public function isUsable(): bool
    {
        return $this->revoked_at === null
            && $this->expires_at->isFuture()
            && $this->uses < $this->max_uses
            && $this->take->isOpen();
    }

    public function url(): string
    {
        return route('johari.respond', ['token' => $this->token]);
    }
}
