<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resources', function (Blueprint $table) {
            $table->enum('resource_type', [
                'url',
                'file',
                'video',
                'documentation',
                'assignment_instruction',
                'article',
                'repository',
                'other',
            ])->default('article')->change();
        });

        DB::table('resources')
            ->whereIn('resource_type', ['article', 'repository', 'other'])
            ->update(['resource_type' => 'url']);

        Schema::table('resources', function (Blueprint $table) {
            $table->text('url')->nullable()->change();
            $table->enum('resource_type', [
                'url',
                'file',
                'video',
                'documentation',
                'assignment_instruction',
            ])->default('url')->change();
            $table->string('file_path')->nullable()->after('url');
        });
    }

    public function down(): void
    {
        Schema::table('resources', function (Blueprint $table) {
            $table->enum('resource_type', [
                'url',
                'file',
                'video',
                'documentation',
                'assignment_instruction',
                'article',
                'repository',
                'other',
            ])->default('url')->change();
        });

        DB::table('resources')
            ->whereIn('resource_type', ['file', 'assignment_instruction'])
            ->update(['resource_type' => 'other']);
        DB::table('resources')
            ->where('resource_type', 'url')
            ->update(['resource_type' => 'article']);
        DB::table('resources')->whereNull('url')->update(['url' => '']);

        Schema::table('resources', function (Blueprint $table) {
            $table->dropColumn('file_path');
            $table->text('url')->nullable(false)->change();
            $table->enum('resource_type', [
                'documentation',
                'article',
                'repository',
                'video',
                'other',
            ])->default('article')->change();
        });
    }
};
