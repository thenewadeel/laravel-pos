<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Add table_id foreign key to link orders directly to tables
            $table->foreignId('table_id')
                ->nullable()
                ->constrained('restaurant_tables')
                ->onDelete('set null');
                
            // Make shop_id explicitly nullable (already is)
            $table->unsignedBigInteger('shop_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['table_id']);
            $table->dropColumn('table_id');
        });
    }
};
