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
        Schema::table('accreditation_infos', function (Blueprint $table) {
            $table->dropUnique('accreditation_infos_year_accreditation_body_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accreditation_infos', function (Blueprint $table) {
            $table->unique(['year', 'accreditation_body_id']);
        });
    }
};
