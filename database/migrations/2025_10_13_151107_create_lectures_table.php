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
        Schema::create('lectures', function (Blueprint $table) {
            $table->char('lecture_id', 36)->primary();
            $table->char('user_id', 36);
            $table->char('category_id', 36)->nullable();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->text('audio_url');
            $table->string('audio_format', 10)->default('mp3');
            $table->bigInteger('file_size')->default(0);
            $table->integer('duration')->default(0); // seconds
            $table->date('recording_date');
            $table->boolean('is_favorite')->default(false);
            $table->string('share_token', 50)->unique()->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('category_id')->references('category_id')->on('categories')->onDelete('set null');

            $table->index(['user_id', 'created_at']);
            $table->index('is_favorite');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lectures');
    }
};
