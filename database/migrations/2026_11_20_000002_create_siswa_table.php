<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('siswa', function (Blueprint $table) {
            $table->id();
            // VARCHAR agar angka nol di awal tetap terjaga (PRD 5.2).
            $table->string('nis', 30)->unique();
            $table->string('nama', 150)->index();
            $table->string('jenis_kelamin', 1);
            $table->text('alamat');
            // Nullable karena relasi dilepas saat Siswa masuk Trash (PRD 5.7, 21.1).
            // Aturan "Siswa aktif wajib punya Kelas" dijaga di lapisan aplikasi.
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswa');
    }
};
