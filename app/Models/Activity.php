<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Activity extends Model
{
    protected $table = 'activity';
    protected $fillable = [
        'user_id',
        'prospect_id',
        'type',
        'note',
        'created_by',
        'updated_by',
    ];

    public function getActivity($user_id)
    {
        $query = DB::table('activity')
            ->select(
                'activity.id',
                'activity.prospect_id',
                'ms_customer.name as customer_name',
                'activity.type',
                'activity.note',
                'activity.activity_date',
                'ms_activity_status.name as activity_status',
                'activity.created_at',
                'activity.updated_at',
            )
            ->leftJoin('prospecting', 'prospecting.id', '=', 'activity.prospect_id')
            ->leftJoin('ms_customer', 'ms_customer.id', '=', 'prospecting.customer_id')
            ->leftJoin('ms_activity_status', 'ms_activity_status.id', '=', 'activity.activity_status')
            ->where('activity.user_id', $user_id)
            ->get();
        return $query;
    }
}
