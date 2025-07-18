<?php

// app/Models/Customer.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customers extends Model
{
    protected $table = 'customers'; // 明示的にテーブル名を指定（任意）

    protected $primaryKey = 'customer_id'; // 主キーが id 以外の場合

    public $timestamps = false; // created_at / updated_at カラムがない場合

    protected $fillable = [
        'store_id',
        'name',
        'staff',
        'phone_number',
        'address',
        'delivery_location',
        'registration_date',
        'remarks',
        'deletion_flag',
    ];
}

