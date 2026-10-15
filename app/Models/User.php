<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_GURU = 'guru';
    public const ROLE_SISWA = 'siswa';

    /** Label role untuk tampilan. */
    public const ROLES = [
        self::ROLE_ADMIN => 'Administrator',
        self::ROLE_GURU => 'Guru',
        self::ROLE_SISWA => 'Siswa',
    ];

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    /* ---------------- Relasi ---------------- */

    public function teacher(): HasOne
    {
        return $this->hasOne(Teacher::class);
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function createdAttendanceSessions(): HasMany
    {
        return $this->hasMany(AttendanceSession::class, 'created_by');
    }

    /* ---------------- Helper role ---------------- */

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isGuru(): bool
    {
        return $this->role === self::ROLE_GURU;
    }

    public function isSiswa(): bool
    {
        return $this->role === self::ROLE_SISWA;
    }

    public function roleLabel(): string
    {
        return self::ROLES[$this->role] ?? ucfirst($this->role);
    }

    /** Nama route dashboard sesuai role. */
    public function dashboardRouteName(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN => 'admin.dashboard',
            self::ROLE_GURU => 'guru.dashboard',
            default => 'siswa.dashboard',
        };
    }

    /**
     * Admin utama (dilindungi dari penghapusan/penonaktifan lewat UI).
     */
    public function isPrimaryAdmin(): bool
    {
        return $this->isAdmin()
            && strcasecmp($this->email, (string) config('simas.primary_admin_email')) === 0;
    }

    /**
     * Apakah akun ini memiliki jejak aktivitas absensi?
     * Digunakan untuk mencegah penghapusan data yang memiliki riwayat penting.
     */
    public function hasAttendanceActivity(): bool
    {
        return $this->createdAttendanceSessions()->exists()
            || Attendance::query()->where('recorded_by', $this->id)->exists();
    }

    /* ---------------- Scope ---------------- */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeRole(Builder $query, string $role): Builder
    {
        return $query->where('role', $role);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $like = '%'.$term.'%';

            $q->where('name', 'like', $like)
                ->orWhere('username', 'like', $like)
                ->orWhere('email', 'like', $like);
        });
    }
}
