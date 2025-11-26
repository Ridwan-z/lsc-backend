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
        Schema::create('transcript_segments', function (Blueprint $table) {
            $table->char('segment_id', 36)->primary();
            $table->char('transcript_id', 36);
            $table->text('text');
            $table->decimal('start_time', 10, 2); // seconds with 2 decimals
            $table->decimal('end_time', 10, 2);
            $table->string('speaker', 50)->nullable();
            $table->decimal('confidence', 3, 2)->nullable();
            $table->integer('sequence_number');
            $table->timestamps();

            $table->foreign('transcript_id')->references('transcript_id')->on('transcripts')->onDelete('cascade');
            $table->index(['transcript_id', 'sequence_number']);
            $table->index('start_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transcript_segments');
    }
};
