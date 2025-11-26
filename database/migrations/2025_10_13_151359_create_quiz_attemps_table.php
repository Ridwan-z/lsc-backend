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
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->char('attempt_id', 36)->primary();
            $table->char('quiz_id', 36);
            $table->char('user_id', 36);
            $table->text('user_answer');
            $table->boolean('is_correct')->default(false);
            $table->integer('time_taken')->nullable(); // seconds
            $table->integer('score')->nullable();
            $table->timestamp('attempted_at')->useCurrent();

            $table->foreign('quiz_id')->references('quiz_id')->on('quizzes')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'attempted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_attemps');
    }
};
