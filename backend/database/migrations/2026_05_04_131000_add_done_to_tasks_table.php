<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('tasks', 'done')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->boolean('done')->default(false)->after('title');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('tasks', 'done')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->dropColumn('done');
            });
        }
    }
};
