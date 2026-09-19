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
            $table->string('mobile_number')->unique()->nullable()->after('email');
            $table->string('preferred_role')->nullable()->after('mobile_number');
            $table->string('profile_photo_path')->nullable()->after('preferred_role');
            $table->timestamp('profile_completed_at')->nullable()->after('email_verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'mobile_number',
                'preferred_role',
                'profile_photo_path',
                'profile_completed_at',
            ]);
        });
    }
};
