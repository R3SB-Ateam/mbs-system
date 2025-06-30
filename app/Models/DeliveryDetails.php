<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryDetails extends Model
{
    
    protected $table = 'delivery_details';

    
    protected $primaryKey = 'delivery_detail_id';

    public $timestamps = false;

    
    protected $fillable = [
        'delivery_id', 
        'order_id', 
        'order_detail_id',
        'product_name',
        'unit_price',
        'delivery_quantity',
        'remarks',
        'return_flag'
    ];

    
    public function delivery()
    {
        return $this->belongsTo(Deliverys::class, 'delivery_id');
    }

    // app/Models/DeliveryDetail.php

    public function orderDetail()
    {
        return $this->belongsTo(OrderDetails::class, 'order_detail_id', 'order_detail_id');
    }

}
