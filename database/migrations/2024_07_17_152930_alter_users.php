<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE `users` CHANGE `type` `type` ENUM('admin', 'cashier', 'order-taker', 'accountant', 'chef', 'stockBoy');");
        // Schema::table('users', function (Blueprint $table) {
        // $table->enum('type', ['admin', 'cashier', 'order-taker', 'accountant', 'chef', 'stockBoy'])->change();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
