<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement(
            "ALTER TABLE `document_history` MODIFY `action` ENUM(
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
                'status_changed',
                'version_restored',
                'access_granted',
                'access_revoked'
            ) NOT NULL"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement(
            "ALTER TABLE `document_history` MODIFY `action` ENUM(
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
            ) NOT NULL"
        );
    }
};
