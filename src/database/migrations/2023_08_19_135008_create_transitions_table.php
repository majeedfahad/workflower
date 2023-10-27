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
        Schema::create('transitions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\Majeedfahad\Workflower\Models\Workflow::class)
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('name');
            $table->string('label');
            $table->foreignIdFor(\Majeedfahad\Workflower\Models\State::class, 'from_state_id')->nullable();
            $table->foreignIdFor(\Majeedfahad\Workflower\Models\State::class, 'to_state_id');
            $table->boolean('start')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transitions');
    }
};
