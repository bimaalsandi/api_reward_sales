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
                'prospecting.kode',
                'prospecting.updated_at',
                'ms_customer.name as customer_name',
                'ms_pipeline.name as status',
            )
            ->leftJoin('users', 'users.id', '=', 'prospecting.user_id')
            ->leftJoin('ms_customer', 'ms_customer.id', '=', 'prospecting.customer_id')
            ->leftJoin('ms_pipeline', 'ms_pipeline.id', '=', 'prospecting.status')
            ->where('prospecting.user_id', $user_id);
        if ($status) {
            $query->where('prospecting.status', $status);
        }
        return $query->get();
    }

    public function getProspectinById($id)
    {
        $query = DB::table('prospecting')
            ->select(
                'prospecting.id',
                'prospecting.customer_id',
                'prospecting.kode',
                'ms_pipeline.name as status',
                'ms_customer.name as customer_name',
                'ms_customer.phone_number',
                'ms_customer.email',
                'ms_customer.alamat',
                'ms_customer.city_id',
                'prospecting.created_at',
                'prospecting.updated_at',

            )
            ->leftJoin('ms_customer', 'ms_customer.id', '=', 'prospecting.customer_id')
            ->leftJoin('ms_pipeline', 'ms_pipeline.id', '=', 'prospecting.status')
            ->where('prospecting.id', $id)
            ->first();
        return $query;
    }
}
