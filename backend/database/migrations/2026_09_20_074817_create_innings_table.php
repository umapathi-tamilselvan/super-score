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
        Schema::create('innings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('innings_number');
            $table->unsignedSmallInteger('target')->nullable();
            $table->string('status')->default('in_progress');
            $table->foreignId('current_striker_id')->nullable()->constrained('players')->nullOnDelete();
            $table->foreignId('current_non_striker_id')->nullable()->constrained('players')->nullOnDelete();
            $table->foreignId('current_bowler_id')->nullable()->constrained('players')->nullOnDelete();
            $table->timestamps();

            $table->unique(['match_id', 'innings_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('innings');
    }
};
