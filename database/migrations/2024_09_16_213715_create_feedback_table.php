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
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');

            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');

            //FEEDBACK FORM
            //Customer Info
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            // Food:
            $table->integer('presentation_and_plating')->nullable()->constrained()->default(1);
            $table->integer('taste_and_quality')->nullable()->constrained()->default(1);

            // Service:
            $table->integer('friendliness')->nullable()->constrained()->default(1);
            $table->integer('service')->nullable()->constrained()->default(1);
            $table->integer('knowledge_and_recommendations')->nullable()->constrained()->default(1);

            // Ambiance:
            $table->integer('atmosphere')->nullable()->constrained()->default(1);
            $table->integer('cleanliness')->nullable()->constrained()->default(1);

            // Value for Money:
            $table->integer('overall_experience')->nullable()->constrained()->default(1);

            // Comments
            $table->text('comments')->nullable();

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};
