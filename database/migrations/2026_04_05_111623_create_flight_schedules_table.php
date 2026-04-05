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
        Schema::create('flight_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('flight_number')->unique();
            $table->string('origin');
            $table->string('destination');
            $table->timestamp('departure_time');
            $table->timestamp('arrival_time');
            $table->enum('status', ['scheduled', 'active', 'completed', 'cancelled'])->default('scheduled');
            
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flight_schedules');
    }
};
