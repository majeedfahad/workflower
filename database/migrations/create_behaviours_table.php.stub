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
        Schema::create('behaviours', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('class');
            $table->json('parameters')->nullable();
            $table->timestamps();
        });

        Schema::create('behaviour_transition', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\Majeedfahad\Workflower\Models\Transition::class)
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignIdFor(\Majeedfahad\Workflower\Models\Behaviour::class)
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('behaviour_transition');
        Schema::dropIfExists('behaviours');
    }
};
