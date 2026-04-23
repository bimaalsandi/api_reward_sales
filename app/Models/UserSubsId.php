<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSubsId extends Model
{
    protected $table = 'user_subs_id';
    protected $fillable = [
        'user_id',
        'subs_id',
        'device_type',
        'last_active_at',
    ];
}
