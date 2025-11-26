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
        Schema::create('summaries', function (Blueprint $table) {
            $table->char('summary_id', 36)->primary();
            $table->char('lecture_id', 36);
            $table->enum('summary_type', ['brief', 'standard', 'detailed'])->default('standard');
            $table->text('content');
            $table->json('key_points')->nullable();
            $table->json('keywords')->nullable();
            $table->json('action_items')->nullable();
            $table->string('ai_model', 50)->default('gpt-4');
            $table->timestamps();

            $table->foreign('lecture_id')->references('lecture_id')->on('lectures')->onDelete('cascade');
            $table->index(['lecture_id', 'summary_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('summaries');
    }
};
