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
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('over_id')->constrained('overs')->cascadeOnDelete();
            $table->foreignId('innings_id')->constrained('innings')->cascadeOnDelete();
            $table->foreignId('striker_id')->constrained('players')->cascadeOnDelete();
            $table->foreignId('non_striker_id')->constrained('players')->cascadeOnDelete();
            $table->foreignId('bowler_id')->constrained('players')->cascadeOnDelete();
            $table->unsignedTinyInteger('runs_off_bat')->default(0);
            $table->string('extra_type')->nullable();
            $table->unsignedTinyInteger('extra_runs')->default(0);
            $table->boolean('is_wicket')->default(false);
            $table->string('dismissal_type')->nullable();
            $table->foreignId('dismissed_player_id')->nullable()->constrained('players')->nullOnDelete();
            $table->foreignId('fielder_id')->nullable()->constrained('players')->nullOnDelete();
            $table->boolean('is_legal_delivery')->default(true);
            $table->boolean('is_free_hit')->default(false);
            $table->unsignedInteger('sequence_in_innings');
            $table->timestamps();

            $table->unique(['innings_id', 'sequence_in_innings']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
