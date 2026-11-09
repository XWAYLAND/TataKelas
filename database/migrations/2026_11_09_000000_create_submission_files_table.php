<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submission_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->string('file_path');
            $table->string('original_name');
            $table->unsignedBigInteger('size')->nullable();
            $table->timestamps();
        });

        // Move existing single attachments into the new files table before
        // removing the old column so no uploaded work is lost.
        DB::table('submissions')
            ->whereNotNull('file_path')
            ->get()
            ->each(function (object $submission): void {
                DB::table('submission_files')->insert([
                    'submission_id' => $submission->id,
                    'file_path' => $submission->file_path,
                    'original_name' => basename($submission->file_path),
                    'size' => null,
                    'created_at' => $submission->created_at ?? now(),
                    'updated_at' => $submission->updated_at ?? now(),
                ]);
            });

        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn('file_path');
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->string('file_path')->nullable();
        });

        DB::table('submission_files')
            ->orderBy('id')
            ->get()
            ->each(function (object $file): void {
                DB::table('submissions')
                    ->where('id', $file->submission_id)
                    ->whereNull('file_path')
                    ->update(['file_path' => $file->file_path]);
            });

        Schema::dropIfExists('submission_files');
    }
};
