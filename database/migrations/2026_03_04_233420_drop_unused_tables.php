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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('accreditation_info_level');
        Schema::dropIfExists('accreditation_level_program');
        Schema::dropIfExists('area_evaluations');
        Schema::dropIfExists('area_evaluation_files');
        Schema::dropIfExists('program_final_verdicts');
        Schema::dropIfExists('ratings');
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
