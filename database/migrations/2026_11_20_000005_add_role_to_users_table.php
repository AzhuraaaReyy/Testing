<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Akun lama (Admin) otomatis ber-role admin.
            $table->string('role', 10)->default('admin')->index()->after('email');
            // Akun Guru/Siswa terhubung ke tepat satu profil (unik: satu profil = satu akun).
            $table->foreignId('siswa_id')->nullable()->unique()->after('role')->constrained('siswa')->nullOnDelete();
            $table->foreignId('guru_id')->nullable()->unique()->after('siswa_id')->constrained('guru')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('guru_id');
            $table->dropConstrainedForeignId('siswa_id');
            $table->dropColumn('role');
        });
    }
};
