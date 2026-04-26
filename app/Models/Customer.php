<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Customer extends Model
{
    protected $table = 'ms_customer';
    protected $fillable = [
        'user_id',
        'company_id',
        'name',
        'alamat',
        'city_id',
        'email',
        'phone_number',
        'status',
        'note',
        'created_by',
        'updated_by',
    ];

    public function getCustomer($user_id)
    {
        $query = DB::table('ms_customer')
            ->select('ms_customer.*')
            ->where('user_id', $user_id)
            ->get();
        return $query;
    }

    public function getCustomerById($id, $user_id)
    {
        $query = DB::table('ms_customer')
            ->select('ms_customer.*')
            ->where('id', $id)
            ->where('user_id', $user_id)
            ->first();
        return $query;
    }
}
