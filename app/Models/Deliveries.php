<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deliveries extends Model
{
    protected $table = 'deliveries';

    protected $primaryKey = 'delivery_id';

    public $timestamps = false; 

    protected $fillable = [
        'customer_id', 
        'delivery_date', 
        'remarks'
    ];

    public function customer()
    {
        return $this->belongsTo(customers::class, 'customer_id', 'customer_id');
    }

    public function deliveryDetails()
    {
        return $this->hasMany(DeliveryDetails::class, 'delivery_id');
    }
}
