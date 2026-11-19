<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guru', function (Blueprint $table) {
            $table->id();
            // Opsional; unik jika diisi. Index unik mengizinkan banyak NULL (MySQL & SQLite).
            $table->string('nip', 30)->nullable()->unique();
            $table->string('nama', 150)->index();
            $table->string('jenis_kelamin', 1);
            $table->text('alamat');
            $table->string('no_telepon', 20);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guru');
    }
};
