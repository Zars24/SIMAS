<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            // Menghapus sesi akan menghapus baris absensi miliknya (tidak ada UI hapus di MVP).
            $table->foreignId('attendance_session_id')->constrained('attendance_sessions')->cascadeOnDelete();
            // Riwayat absensi siswa tidak boleh terhapus otomatis saat siswa dihapus.
            $table->foreignId('student_id')->constrained('students')->restrictOnDelete();
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alpa']);
            $table->text('note')->nullable();
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('recorded_at')->nullable();
            $table->timestamps();

            // Satu siswa hanya boleh punya satu baris per sesi (mencegah absensi ganda).
            $table->unique(['attendance_session_id', 'student_id']);
            $table->index(['student_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
