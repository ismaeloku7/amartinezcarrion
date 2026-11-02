<?php

namespace App\Models\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JohariResponse extends Model
{
    protected $fillable = ['take_id', 'invitation_id', 'words', 'fingerprint'];

    protected function casts(): array
    {
        return ['words' => 'array'];
    }

    public function take(): BelongsTo
    {
        return $this->belongsTo(TestTake::class, 'take_id');
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(JohariInvitation::class, 'invitation_id');
    }
}
