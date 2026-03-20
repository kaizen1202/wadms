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
        Schema::table('accreditation_documents', function (Blueprint $table) {
            $table->foreignId('sub_sub_parameter_id')
            ->nullable()
            ->after('subparameter_id')
            ->constrained('sub_subparameters')
            ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accreditation_documents', function (Blueprint $table) {
            //
        });
    }
};
