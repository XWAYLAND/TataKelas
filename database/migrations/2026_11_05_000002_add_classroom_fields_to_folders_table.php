<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->foreignId('classroom_id')
                ->nullable()
                ->after('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->enum('visibility', ['private', 'class_material', 'assignment'])
                ->default('private')
                ->after('classroom_id');
            $table->dateTime('due_date')->nullable()->after('visibility');

            $table->index(['classroom_id', 'visibility']);
        });
    }

    public function down(): void
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->dropIndex(['classroom_id', 'visibility']);
            $table->dropConstrainedForeignId('classroom_id');
            $table->dropColumn(['visibility', 'due_date']);
        });
    }
};
