<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetails extends Model
{
    protected $table = 'order_details';

    protected $primaryKey = 'order_detail_id';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'order_id', 
        'product_name',
        'unit_price',
        'quantity',
        'delivery_status',
        'delivery_date',
        'remarks',
        'cancell_flag'
    ];
    
}

