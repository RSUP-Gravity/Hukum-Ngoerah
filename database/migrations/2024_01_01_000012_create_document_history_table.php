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
        Schema::create('document_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
            $table->foreignId('document_version_id')->nullable()->constrained('document_versions')->onDelete('set null');
            
            // Action tracking
            $table->enum('action', [
                'created',
                'updated',
                'version_uploaded',
                'submitted_for_review',
                'reviewed',
                'submitted_for_approval',
                'approved',
                'rejected',
                'published',
                'unpublished',
                'archived',
                'restored',
                'deleted',
                'viewed',
                'downloaded',
                'printed',
                'shared',
                'locked',
                'unlocked',
                'comment_added',
                'status_changed'
            ]);
            
            // Details
            $table->string('old_status', 50)->nullable();
            $table->string('new_status', 50)->nullable();
            $table->text('notes')->nullable();
            $table->json('changes')->nullable()->comment('JSON of changed fields');
            
            // Actor
            $table->foreignId('performed_by')->constrained('users')->onDelete('restrict');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            
            $table->timestamps();

            // Indexes
            $table->index(['document_id', 'created_at']);
            $table->index('action');
            $table->index('performed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_history');
    }
};
