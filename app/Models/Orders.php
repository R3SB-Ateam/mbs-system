<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    // テーブル名を指定（必要に応じて）
    protected $table = 'orders'; 

    // プライマリキーが 'order_id' である場合、明示的に指定
    protected $primaryKey = 'order_id';

    // 自動インクリメントを無効にする場合
    public $incrementing = false;

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
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }
}

