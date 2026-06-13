<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->time('jam');
            $table->string('kategori', 100);
            $table->text('kegiatan');
            $table->string('status', 50)->nullable();
            $table->text('pic_internal')->nullable();
            $table->text('pic_external')->nullable();
            $table->string('link_nm', 500)->nullable();
            $table->text('hasil')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
