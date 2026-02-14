<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('sync_status')->default('synced')->after('state');
            $table->string('local_order_id')->nullable()->after('sync_status');
            $table->string('device_id')->nullable()->after('local_order_id');
            $table->timestamp('synced_at')->nullable()->after('device_id');
            
            $table->index('sync_status');
            $table->index('local_order_id');
            $table->index('device_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['sync_status']);
            $table->dropIndex(['local_order_id']);
            $table->dropIndex(['device_id']);
            $table->dropColumn(['sync_status', 'local_order_id', 'device_id', 'synced_at']);
        });
    }
};
