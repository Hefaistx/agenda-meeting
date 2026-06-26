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
        Schema::create('konfigurasi_waktu', function (Blueprint $table) {
            $table->id();
            $table->string('kategori', 100)->unique();
            $table->time('waktu_mulai_min')->nullable();
            $table->time('waktu_selesai_max')->nullable();
            $table->timestamps();
        });

        DB::table('konfigurasi_waktu')->insert([
            ['kategori' => 'Internal', 'waktu_mulai_min' => null,    'waktu_selesai_max' => null,    'created_at' => now(), 'updated_at' => now()],
            ['kategori' => 'External', 'waktu_mulai_min' => '13:00', 'waktu_selesai_max' => null,    'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('konfigurasi_waktu');
    }
};
