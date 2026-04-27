<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProspectingPipeline extends Model
{
    protected $table = 'prospecting_pipeline';
    protected $fillable = [
        'prospecting_id',
        'status',
        'note',
    ];
}
