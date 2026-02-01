<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_sync_queues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('device_id');
            $table->string('local_order_id');
            $table->enum('sync_type', ['create', 'update', 'delete'])->default('create');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'conflict'])->default('pending');
            $table->integer('retry_count')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->json('conflict_data')->nullable();
            $table->timestamps();
            
            $table->index(['device_id', 'status']);
            $table->index(['order_id', 'status']);
            $table->index('local_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_sync_queues');
    }
};
