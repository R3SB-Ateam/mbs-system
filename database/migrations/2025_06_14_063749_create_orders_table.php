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
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('order_id')->comment('注文ID');
            $table->unsignedInteger('customer_id')->comment('顧客ID');
            $table->date('order_date')->comment('注文日');
            $table->text('remarks')->nullable()->comment('備考');
            $table->comment('注文テーブル');

            // 外部キー制約
            $table->foreign('customer_id')->references('customer_id')->on('customers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};