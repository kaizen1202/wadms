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
        Schema::table('accreditation_evaluations', function (Blueprint $table) {
           // Add status column (default to pending)
            $table->string('status')->default('pending')->after('evaluated_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accreditation_evaluations', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
