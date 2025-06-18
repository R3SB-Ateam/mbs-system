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
        Schema::create('delivery_details', function (Blueprint $table) {
            $table->increments('delivery_detail_id')->comment('納品詳細ID');
            $table->unsignedInteger('delivery_id')->comment('納品ID');
            $table->unsignedInteger('order_id')->comment('注文ID');
            $table->unsignedInteger('order_detail_id')->comment('注文詳細ID');
            $table->string('product_name')->comment('商品名');
            $table->decimal('unit_price', 10, 2)->comment('単価');
            $table->integer('delivery_quantity')->comment('納品数量');
            $table->text('remarks')->nullable()->comment('備考');
            $table->boolean('return_flag')->default(false)->comment('返品フラグ');

            // 外部キー制約
            $table->foreign('delivery_id')->references('delivery_id')->on('deliveries');
            $table->foreign('order_id')->references('order_id')->on('orders');
            $table->foreign('order_detail_id')->references('order_detail_id')->on('order_details');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_details');
    }
};