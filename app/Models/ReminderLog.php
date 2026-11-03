<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReminderLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'key', 'local_date', 'sent_at'];

    protected function casts(): array
    {
        return ['local_date' => 'date', 'sent_at' => 'datetime'];
    }
}
