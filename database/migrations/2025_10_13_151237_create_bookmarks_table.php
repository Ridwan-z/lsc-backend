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
        Schema::create('bookmarks', function (Blueprint $table) {
            $table->char('bookmark_id', 36)->primary();
            $table->char('lecture_id', 36);
            $table->decimal('timestamp', 10, 2); // seconds with 2 decimals
            $table->string('title', 255)->nullable();
            $table->text('note')->nullable();
            $table->enum('priority', ['high', 'medium', 'low'])->default('medium');
            $table->string('color', 7)->default('#FFD700'); // Hex color
            $table->boolean('is_resolved')->default(false);
            $table->timestamps();

            $table->foreign('lecture_id')->references('lecture_id')->on('lectures')->onDelete('cascade');
            $table->index(['lecture_id', 'timestamp']);
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookmarks');
    }
};
