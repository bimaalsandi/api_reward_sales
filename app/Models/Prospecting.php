<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Prospecting extends Model
{
    protected $table = 'prospecting';
    protected $fillable = [
        'user_id',
        'customer_id',
        'status',
        'created_by',
        'updated_by',
    ];

    public function getTotalCount($user_id, $status)
    {

        $query = DB::table('prospecting')
            ->where('user_id', $user_id);
        if ($status == 'prospect') {
            $query->where('status', 'prospect');
        } elseif ($status == 'follow-up') {
            $query->where('status', 'follow-up');
        } elseif ($status == 'negosiasi') {
            $query->where('status', 'negosiasi');
        } elseif ($status == 'dealing') {
            $query->where('status', 'dealing');
        } elseif ($status == 'cancel') {
            $query->where('status', 'cancel');
        }
        $total = $query->count();
        if ($total > 0) {
            return $total;
        } else {
            return 0;
        }
    }

    public function getProsespecting($user_id, $status = null)
    {
        $query = DB::table('prospecting')
            ->select(
                'prospecting.id',
                'prospecting.customer_id',
                'users.name',
                'ms_customer.name as customer_name',
                'ms_customer.phone_number',
                'prospecting.note',
                'prospecting.status',
            )
            ->leftJoin('users', 'users.id', '=', 'prospecting.user_id')
            ->leftJoin('ms_customer', 'ms_customer.id', '=', 'prospecting.customer_id')
            ->where('prospecting.user_id', $user_id);
        if ($status == 'prospect') {
            $query->where('prospecting.status', 'prospect');
        } elseif ($status == 'follow-up') {
            $query->where('prospecting.status', 'follow-up');
        } elseif ($status == 'negosiasi') {
            $query->where('prospecting.status', 'negosiasi');
        } elseif ($status == 'dealing') {
            $query->where('prospecting.status', 'dealing');
        } elseif ($status == 'cancel') {
            $query->where('prospecting.status', 'cancel');
        }
        return $query->get();
    }
}
