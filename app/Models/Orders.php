<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\OrderDetails; // ← ★ これを追加
use App\Models\Customers;    // ← これも必要（なければ）

class Orders extends Model
{
    // テーブル名を指定（必要に応じて）
    protected $table = 'orders'; 

    // プライマリキーが 'order_id' である場合、明示的に指定
    protected $primaryKey = 'order_id';

    // 自動インクリメントを無効にする場合
    public $incrementing = true;

    // タイムスタンプを無視（必要に応じて）
    public $timestamps = false;

    protected $fillable = [
        'order_id', 
        'customer_id',
        'order_date',
        'remarks',
    ];

    // 追加：顧客とのリレーション
    public function customer()
    {
        return $this->belongsTo(customers::class, 'customer_id', 'customer_id');
    }

    public function details()
    {
        return $this->hasMany(OrderDetails::class, 'order_id', 'order_id');
    }


    public function getDeliveryStatusAttribute()
    {
        if (!$this->relationLoaded('details')) {
            $this->load('details');
        }

        $validDetails = $this->details->where('cancell_flag', 0);

        if ($validDetails->isEmpty()) {
            return '不明';
        }

        $allDelivered = $validDetails->every(function ($detail) {
            $delivered = $detail->delivery_quantity ?? 0;
            return $delivered >= $detail->quantity;
        });

        return $allDelivered ? '納品済み' : '未納品';
    }

    
}

