<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            // Unik termasuk terhadap data di Trash (PRD 13.3): index unik biasa, bukan parsial.
            $table->string('nama_kelas', 100)->unique();
            $table->string('tingkat', 3)->index();
            $table->string('jurusan', 100)->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};
