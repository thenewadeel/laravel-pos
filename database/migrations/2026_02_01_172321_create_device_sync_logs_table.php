<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('device_id');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action'); // order_created_offline, order_synced, conflict_detected, etc.
            $table->enum('status', ['success', 'failed', 'conflict'])->default('success');
            $table->text('details')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamp('sync_timestamp')->nullable();
            $table->timestamps();
            
            $table->index('device_id');
            $table->index(['device_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_sync_logs');
    }
};
