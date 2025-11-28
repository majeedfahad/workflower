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
        Schema::create('transition_logs', function (Blueprint $table) {
            $table->id();
            $table->morphs('model');
            $table->foreignIdFor(\Majeedfahad\Workflower\Models\Transition::class)
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('performer_type')->nullable();
            $table->unsignedBigInteger('performer_id')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transition_logs');
    }
};
