<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetails extends Model
{
    protected $table = 'order_details';

    protected $primaryKey = 'order_detail_id';

    public $incrementing = true;

    public $timestamps = false;

    protected $fillable = [
        'order_id', 
        'product_name',
        'unit_price',
        'quantity',
        'delivery_status',
        'remarks',
        'cancell_flag'
    ];
    
}

