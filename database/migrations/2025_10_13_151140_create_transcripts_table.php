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
        Schema::create('transcripts', function (Blueprint $table) {
            $table->char('transcript_id', 36)->primary();
            $table->char('lecture_id', 36)->unique();
            $table->longText('full_text');
            $table->string('language', 10)->default('id'); // id/en
            $table->decimal('confidence_score', 3, 2)->nullable(); // 0.00-1.00
            $table->integer('word_count')->default(0);
            $table->integer('processing_time')->default(0); // seconds
            $table->string('stt_provider', 50)->default('whisper'); // whisper/google
            $table->timestamps();

            $table->foreign('lecture_id')->references('lecture_id')->on('lectures')->onDelete('cascade');

            // Full-text search index
            $table->fullText('full_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transcripts');
    }
};
