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
        Schema::create('document_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
            $table->foreignId('document_version_id')->nullable()->constrained('document_versions')->onDelete('set null');
            
            // Approval workflow
            $table->integer('sequence')->default(1)->comment('Order in approval chain');
            $table->foreignId('approver_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('delegated_to')->nullable()->constrained('users')->onDelete('set null');
            
            // Status
            $table->enum('status', ['pending', 'approved', 'rejected', 'skipped', 'delegated'])->default('pending');
            $table->text('comments')->nullable();
            $table->timestamp('responded_at')->nullable();
            
            // Deadline
            $table->timestamp('due_date')->nullable();
            $table->boolean('is_overdue')->default(false);
            
            // Reminders
            $table->integer('reminder_count')->default(0);
            $table->timestamp('last_reminder_at')->nullable();
            
            $table->timestamps();

            // Indexes
            $table->index(['document_id', 'sequence']);
            $table->index(['approver_id', 'status']);
            $table->index('status');
            $table->index('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_approvals');
    }
};
