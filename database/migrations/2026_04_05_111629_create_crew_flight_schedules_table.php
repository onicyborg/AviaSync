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
        Schema::create('crew_flight_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('crew_id')->constrained('crews')->cascadeOnDelete();
            $table->foreignUuid('flight_schedule_id')->constrained('flight_schedules')->cascadeOnDelete();
            $table->string('role_in_flight');
            $table->timestamp('assigned_at')->useCurrent();
            
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
        Schema::dropIfExists('crew_flight_schedules');
    }
};
