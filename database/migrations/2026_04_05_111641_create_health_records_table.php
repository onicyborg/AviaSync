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
        Schema::create('health_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('crew_id')->constrained('crews')->cascadeOnDelete();
            $table->date('checkup_date');
            $table->string('medical_examiner');
            $table->enum('status', ['fit', 'unfit', 'restricted'])->default('fit');
            $table->text('notes')->nullable();
            $table->date('next_checkup_date')->nullable();
            $table->string('attachment_path')->nullable(); // Menambahkan kolom lampiran
            
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
        Schema::dropIfExists('health_records');
    }
};
