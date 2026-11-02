<?php

namespace App\Models\Tests;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestTake extends Model
{
    public const STATUS_SELF_PENDING = 'self_pending';

    public const STATUS_COLLECTING = 'collecting';

    public const STATUS_RESULTS = 'results';

    public const STATUS_CLOSED = 'closed';

    protected $fillable = ['user_id', 'test_id', 'status', 'data', 'result', 'started_at', 'completed_at', 'closed_at'];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'result' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(JohariInvitation::class, 'take_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(JohariResponse::class, 'take_id');
    }

    public function data(string $key, mixed $default = null): mixed
    {
        return data_get($this->data, $key, $default);
    }

    public function isOpen(): bool
    {
        return $this->status !== self::STATUS_CLOSED;
    }
}
