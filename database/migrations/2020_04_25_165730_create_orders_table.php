<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('table_number')->nullable();
            $table->string('waiter_name')->nullable();

            $table->enum('state', ['preparing', 'served', 'closed', 'wastage'])->default('preparing');
            $table->enum('type', ['dine-in', 'take-away', 'delivery'])->default('dine-in');

            $table->foreignId('customer_id')->nullable();
            $table->foreignId('user_id');
            $table->foreignId('shop_id')->nullable();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
