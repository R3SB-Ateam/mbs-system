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
        Schema::create('deliveries', function (Blueprint $table) {
            $table->increments('delivery_id')->comment('納品ID');
            $table->unsignedInteger('customer_id')->comment('顧客ID');
            $table->date('delivery_date')->comment('納品日');
            $table->text('remarks')->nullable()->comment('備考');
            
            // 元のSQLには外部キー制約がありませんでしたが、
            // 必要であれば以下のコメントアウトを解除してください。
            // $table->foreign('customer_id')->references('customer_id')->on('customers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};