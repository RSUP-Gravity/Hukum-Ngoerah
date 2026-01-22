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
        Schema::create('document_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
            
            // Access can be granted to user, role, unit, or directorate
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('role_id')->nullable()->constrained('roles')->onDelete('cascade');
            $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('cascade');
            $table->foreignId('directorate_id')->nullable()->constrained('directorates')->onDelete('cascade');
            
            // Permission type
            $table->enum('permission', ['view', 'download', 'edit', 'delete', 'approve', 'full'])->default('view');
            
            // Validity
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            
            // Granted by
            $table->foreignId('granted_by')->constrained('users')->onDelete('restrict');
            
            $table->timestamps();

            // Indexes
            $table->index(['document_id', 'user_id']);
            $table->index(['document_id', 'role_id']);
            $table->index(['document_id', 'unit_id']);
            $table->index(['document_id', 'directorate_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_access');
    }
};
