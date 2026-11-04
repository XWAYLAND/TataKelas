<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add indexes for the queries used by dashboards, folder tabs, and filters.
     */
    public function up(): void
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'folders_user_created_index');
        });

        Schema::table('resources', function (Blueprint $table) {
            $table->index(['folder_id', 'created_at'], 'resources_folder_created_index');
            $table->index('resource_type', 'resources_type_index');
            $table->index('read_status', 'resources_status_index');
        });

        Schema::table('notes', function (Blueprint $table) {
            $table->index(['folder_id', 'created_at'], 'notes_folder_created_index');
            $table->index('priority', 'notes_priority_index');
        });

        Schema::table('photos', function (Blueprint $table) {
            $table->index(['folder_id', 'created_at'], 'photos_folder_created_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('photos', function (Blueprint $table) {
            $table->dropIndex('photos_folder_created_index');
        });

        Schema::table('notes', function (Blueprint $table) {
            $table->dropIndex('notes_folder_created_index');
            $table->dropIndex('notes_priority_index');
        });

        Schema::table('resources', function (Blueprint $table) {
            $table->dropIndex('resources_folder_created_index');
            $table->dropIndex('resources_type_index');
            $table->dropIndex('resources_status_index');
        });

        Schema::table('folders', function (Blueprint $table) {
            $table->dropIndex('folders_user_created_index');
        });
    }
};
