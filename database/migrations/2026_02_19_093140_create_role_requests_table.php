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
        Schema::create('role_requests', function (Blueprint $table) {
            $table->id();

            // User who requested the role
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // Role being requested
            $table->foreignId('role_id')
                ->constrained()
                ->cascadeOnDelete();

            // Optional explanation for request
            $table->text('reason')->nullable();

            // Request status
            $table->string('status')->default('pending');

            // Admin who approved/rejected
            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_requests');
    }
};
