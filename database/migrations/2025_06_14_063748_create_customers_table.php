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
        Schema::create('customers', function (Blueprint $table) {
            $table->increments('customer_id')->comment('顧客ID');
            $table->unsignedInteger('store_id')->comment('店舗ID');
            $table->string('name')->comment('名前');
            $table->string('staff')->nullable()->comment('担当者名');
            $table->string('phone_number', 15)->comment('電話番号');
            $table->text('address')->comment('住所');
            $table->text('delivery_location')->nullable()->comment('配達条件等');
            $table->date('registration_date')->comment('登録日');
            $table->text('remarks')->nullable()->comment('備考');
            $table->boolean('deletion_flag')->default(false)->comment('削除フラグ');
            $table->comment('顧客テーブル');

            // 外部キー制約
            $table->foreign('store_id')->references('store_id')->on('stores');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};