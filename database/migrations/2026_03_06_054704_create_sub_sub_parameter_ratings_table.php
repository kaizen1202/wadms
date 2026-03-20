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
        Schema::create('sub_sub_parameter_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_id')->constrained('accreditation_evaluations')->cascadeOnDelete();
            $table->foreignId('sub_subparameter_id')->constrained('sub_subparameters')->cascadeOnDelete();
            $table->foreignId('rating_option_id')->constrained()->cascadeOnDelete();
            $table->integer('score')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_subparameter_ratings');
    }
};
