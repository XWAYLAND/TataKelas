<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resource_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_id')->constrained('resources')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('original_name');
            $table->unsignedBigInteger('size')->nullable();
            $table->timestamps();

            $table->index(['resource_id', 'created_at']);
        });

        // Move existing single attachments into the new files table before
        // removing the old column so no uploaded material is lost.
        DB::table('resources')
            ->whereNotNull('file_path')
            ->get()
            ->each(function (object $resource): void {
                DB::table('resource_files')->insert([
                    'resource_id' => $resource->id,
                    'file_path' => $resource->file_path,
                    'original_name' => basename($resource->file_path),
                    'size' => null,
                    'created_at' => $resource->created_at ?? now(),
                    'updated_at' => $resource->updated_at ?? now(),
                ]);
            });

        Schema::table('resources', function (Blueprint $table) {
            $table->dropColumn('file_path');
        });
    }

    public function down(): void
    {
        Schema::table('resources', function (Blueprint $table) {
            $table->string('file_path')->nullable();
        });

        DB::table('resource_files')
            ->orderBy('id')
            ->get()
            ->each(function (object $file): void {
                DB::table('resources')
                    ->where('id', $file->resource_id)
                    ->whereNull('file_path')
                    ->update(['file_path' => $file->file_path]);
            });

        Schema::dropIfExists('resource_files');
    }
};
