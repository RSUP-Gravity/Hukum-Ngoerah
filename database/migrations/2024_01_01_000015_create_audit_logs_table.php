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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            
            // Actor
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('username', 50)->nullable()->comment('Stored in case user is deleted');
            
            // Action details
            $table->string('action', 50);
            $table->string('module', 50)->comment('documents, users, master_data, auth, admin');
            $table->string('entity_type', 50)->nullable()->comment('Model class name');
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('entity_name', 255)->nullable()->comment('Human readable identifier');
            
            // Change details
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('description')->nullable();
            
            // Request context
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('url', 500)->nullable();
            $table->string('method', 10)->nullable();
            
            // Session
            $table->string('session_id', 100)->nullable();
            
            $table->timestamp('created_at');

            // Indexes for querying
            $table->index('user_id');
            $table->index('action');
            $table->index('module');
            $table->index(['entity_type', 'entity_id']);
            $table->index('created_at');
            $table->index('ip_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
