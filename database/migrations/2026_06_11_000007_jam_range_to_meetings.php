<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: rename jam → jam_mulai
        Schema::table('meetings', function (Blueprint $table) {
            $table->renameColumn('jam', 'jam_mulai');
        });

        // Step 2: add jam_selesai nullable
        Schema::table('meetings', function (Blueprint $table) {
            $table->time('jam_selesai')->nullable()->after('jam_mulai');
        });

        // Step 3: backfill jam_selesai = jam_mulai + 1 hour
        DB::statement("UPDATE meetings SET jam_selesai = (jam_mulai + interval '1 hour')::time");

        // Step 4: set NOT NULL
        Schema::table('meetings', function (Blueprint $table) {
            $table->time('jam_selesai')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropColumn('jam_selesai');
        });
        Schema::table('meetings', function (Blueprint $table) {
            $table->renameColumn('jam_mulai', 'jam');
        });
    }
};
