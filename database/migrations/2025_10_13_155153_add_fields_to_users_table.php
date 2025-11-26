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
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('avatar_url')->nullable()->after('phone');
            $table->string('institution', 100)->nullable()->after('avatar_url');
            $table->string('major', 100)->nullable()->after('institution');
            $table->enum('subscription_type', ['free', 'premium', 'pro'])->default('free')->after('major');
            $table->bigInteger('storage_used')->default(0)->after('subscription_type');
            $table->bigInteger('storage_limit')->default(1073741824)->after('storage_used'); // 1GB
            $table->boolean('is_active')->default(true)->after('storage_limit');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'avatar_url',
                'institution',
                'major',
                'subscription_type',
                'storage_used',
                'storage_limit',
                'is_active'
            ]);
        });
    }
};
