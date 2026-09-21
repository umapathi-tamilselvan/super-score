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
        Schema::create('overs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('innings_id')->constrained('innings')->cascadeOnDelete();
            $table->unsignedSmallInteger('over_number');
            $table->foreignId('bowler_id')->constrained('players')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['innings_id', 'over_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('overs');
    }
};
