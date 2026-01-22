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
        Schema::create('document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
            $table->integer('version_number');
            
            // File information
            $table->string('file_path', 500);
            $table->string('file_name', 255);
            $table->string('file_type', 50);
            $table->bigInteger('file_size')->comment('Size in bytes');
            $table->string('file_hash', 64)->comment('SHA-256 hash for integrity');
            
            // Version metadata
            $table->text('change_summary')->nullable();
            $table->enum('change_type', ['initial', 'minor', 'major', 'correction'])->default('initial');
            
            // Status
            $table->boolean('is_current')->default(false);
            
            // Audit
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->unique(['document_id', 'version_number']);
            $table->index(['document_id', 'is_current']);
            $table->index('file_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_versions');
    }
};
