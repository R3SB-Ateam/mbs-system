<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_details', function (Blueprint $table) {
            $table->increments('order_detail_id')->comment('注文詳細ID');
            $table->unsignedInteger('order_id')->comment('注文ID');
            $table->string('product_name')->comment('商品名');
            $table->decimal('unit_price', 10, 2)->comment('単価');
            $table->integer('quantity')->comment('数量');
            $table->integer('delivery_quantity')->default(0)->comment('納品数量');
            $table->text('remarks')->nullable()->comment('備考');
            $table->boolean('cancell_flag')->default(false)->comment('キャンセルフラグ');
            $table->comment('注文明細テーブル');

            // 外部キー制約
            $table->foreign('order_id')->references('order_id')->on('orders');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_details');
    }
};