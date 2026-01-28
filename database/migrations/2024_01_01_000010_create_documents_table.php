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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_number', 100)->unique();
            $table->string('title', 255);
            $table->text('description')->nullable();
            
            // Classification
            $table->foreignId('document_type_id')->constrained('document_types')->onDelete('restrict');
            $table->foreignId('document_category_id')->nullable()->constrained('document_categories')->onDelete('set null');
            
            // Organizational ownership
            $table->foreignId('directorate_id')->nullable()->constrained('directorates')->onDelete('set null');
            $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('set null');
            
            // Document metadata
            $table->date('effective_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->date('review_date')->nullable()->comment('Next review date');
            $table->integer('retention_days')->nullable();
            
            // Status workflow
            $table->enum('status', ['draft', 'pending_review', 'pending_approval', 'approved', 'published', 'expired', 'archived', 'rejected'])
                  ->default('draft');
            $table->text('rejection_reason')->nullable();
            
            // Version tracking
            $table->integer('current_version')->default(1);
            $table->boolean('is_locked')->default(false)->comment('Prevent edits while in approval');
            
            // Confidentiality
            $table->enum('confidentiality', ['public', 'internal', 'confidential', 'restricted'])->default('internal');
            
            // Keywords for search
            $table->text('keywords')->nullable();
            
            // Audit trail
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('published_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index('status');
            $table->index('document_type_id');
            $table->index('directorate_id');
            $table->index('unit_id');
            $table->index('effective_date');
            $table->index('expiry_date');
            $table->index('created_by');
            $table->index('confidentiality');
            if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                $table->fullText(['title', 'description', 'keywords']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
