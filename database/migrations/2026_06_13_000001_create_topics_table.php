<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->timestamps();
        });

        DB::table('topics')->insert([
            ['nama' => 'HnM',                                          'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Pembahasan konsep, alur, dan simulasi',        'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Review alur dan simulasi',                     'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Review PRD dan timeline',                      'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Review sistem pra-production',                 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Evaluasi sistem post-production',              'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('topics');
    }
};
