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
        Schema::create('certifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('crew_id')->constrained('crews')->cascadeOnDelete();
            $table->string('certificate_name');
            $table->string('certificate_number');
            $table->date('issue_date');
            $table->date('expiry_date');
            $table->enum('status', ['valid', 'expired', 'revoked'])->default('valid');
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
        Schema::dropIfExists('certifications');
    }
};
