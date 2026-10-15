<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Admin\AttendanceRecapController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ScheduleController as AdminScheduleController;
use App\Http\Controllers\Admin\SchoolClassController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Guru\AttendanceController as GuruAttendanceController;
use App\Http\Controllers\Guru\DashboardController as GuruDashboardController;
use App\Http\Controllers\Guru\ScheduleController as GuruScheduleController;
use App\Http\Controllers\Siswa\AttendanceController as SiswaAttendanceController;
use App\Http\Controllers\Siswa\DashboardController as SiswaDashboardController;
use App\Http\Controllers\Siswa\ProfileController as SiswaProfileController;
use App\Http\Controllers\Siswa\ScheduleController as SiswaScheduleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Publik
|--------------------------------------------------------------------------
*/

Route::get('/', LandingController::class)->name('landing');

/*
|--------------------------------------------------------------------------
| Autentikasi
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/masuk', [LoginController::class, 'show'])->name('login');
    Route::post('/masuk', [LoginController::class, 'login'])->middleware('throttle:10,1');
});

Route::post('/keluar', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::get('/dashboard', DashboardRedirectController::class)
    ->middleware('auth')
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| Area Admin
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Manajemen pengguna + aksi khusus.
    Route::patch('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
    Route::put('/users/{user}/password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::resource('users', UserController::class);

    Route::resource('teachers', TeacherController::class)->except(['show']);
    Route::resource('students', StudentController::class);
    Route::resource('classes', SchoolClassController::class)
        ->except(['show'])
        ->parameters(['classes' => 'schoolClass']);
    Route::resource('subjects', SubjectController::class)->except(['show']);
    Route::resource('schedules', AdminScheduleController::class)->except(['show']);

    // Rekap absensi & ekspor CSV.
    Route::get('/attendance', [AttendanceRecapController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/export', [AttendanceRecapController::class, 'export'])->name('attendance.export');
    Route::get('/attendance/sessions/{session}', [AttendanceRecapController::class, 'show'])->name('attendance.sessions.show');

    // Audit log.
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
});

/*
|--------------------------------------------------------------------------
| Area Guru
|--------------------------------------------------------------------------
*/

Route::prefix('guru')->name('guru.')->middleware(['auth', 'role:guru'])->group(function () {
    Route::get('/dashboard', [GuruDashboardController::class, 'index'])->name('dashboard');
    Route::get('/schedules', [GuruScheduleController::class, 'index'])->name('schedules.index');

    // Absensi: pilih jadwal & tanggal → isi → simpan → lihat ringkasan.
    Route::get('/attendance', [GuruAttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/schedules/{schedule}/fill', [GuruAttendanceController::class, 'fill'])->name('attendance.fill');
    Route::post('/attendance/schedules/{schedule}', [GuruAttendanceController::class, 'store'])->name('attendance.store');
    Route::get('/attendance/sessions/{session}', [GuruAttendanceController::class, 'show'])->name('attendance.show');
    Route::put('/attendance/sessions/{session}', [GuruAttendanceController::class, 'update'])->name('attendance.update');
});

/*
|--------------------------------------------------------------------------
| Area Siswa
|--------------------------------------------------------------------------
*/

Route::prefix('siswa')->name('siswa.')->middleware(['auth', 'role:siswa'])->group(function () {
    Route::get('/dashboard', [SiswaDashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [SiswaProfileController::class, 'show'])->name('profile');
    Route::get('/schedule', [SiswaScheduleController::class, 'index'])->name('schedule');
    Route::get('/attendance', [SiswaAttendanceController::class, 'index'])->name('attendance');
});
