<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        // ── meetings ──────────────────────────────────────────────
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->string('meeting_code')->nullable()->unique();
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->string('kategori', 100);
            $table->text('kegiatan');
            $table->string('status', 50)->nullable();
            $table->text('pic_internal')->nullable();
            $table->text('pic_external')->nullable();
            $table->string('link_nm', 500)->nullable();
            $table->string('nm_file', 255)->nullable();
            $table->text('hasil')->nullable();
            $table->jsonb('reschedule_history')->nullable();
            $table->timestamps();
        });

        // ── rooms ─────────────────────────────────────────────────
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('lokasi', 200)->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // ── topics ────────────────────────────────────────────────
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 200)->unique();
            $table->timestamps();
        });

        // ── FK columns on meetings ─────────────────────────────────
        Schema::table('meetings', function (Blueprint $table) {
            $table->foreignId('ruangan_id')->nullable()->constrained('rooms')->nullOnDelete()->after('meeting_code');
            $table->foreignId('topik_id')->nullable()->constrained('topics')->nullOnDelete()->after('ruangan_id');
        });

        // ── Seed topik awal ────────────────────────────────────────
        DB::table('topics')->insert([
            ['nama' => 'HnM',                                   'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Pembahasan konsep, alur, dan simulasi', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Review alur dan simulasi',              'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Review PRD dan timeline',               'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Review sistem pra-production',          'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Evaluasi sistem post-production',       'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('topik_id');
            $table->dropConstrainedForeignId('ruangan_id');
        });
        Schema::dropIfExists('topics');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('meetings');
    }
};
