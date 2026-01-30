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
        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
            if (!Schema::hasColumn('users', 'fav_printer_ip')) {
                $table->string('fav_printer_ip')->nullable()->after('type');
            }
            if (!Schema::hasColumn('users', 'current_shop_id')) {
                $table->foreignId('current_shop_id')->nullable()->after('fav_printer_ip');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['current_shop_id']);
        });
    }
};
