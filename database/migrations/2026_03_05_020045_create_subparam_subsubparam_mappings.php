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
        Schema::create('subparam_subsubparam_mappings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parameter_subparameter_mapping_id');
            $table->unsignedBigInteger('sub_subparameter_id');
            $table->timestamps();

            $table->foreign('parameter_subparameter_mapping_id', 'psp_mapping_fk')
                ->references('id')
                ->on('parameter_subparameter_mappings')
                ->cascadeOnDelete();

            $table->foreign('sub_subparameter_id', 'ssp_id_fk')
                ->references('id')
                ->on('sub_subparameters')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subparam_subsubparam_mappings');
    }
};
