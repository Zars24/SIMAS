# Status Implementasi SIMAS

Dokumen ini memetakan **blueprint** (`docs/SIMAS_Blueprint_PRD_ERD_SRS.md`) dengan
implementasi yang benar-benar ada di repositori, serta mencatat penyimpangan
yang disengaja beserta alasannya.

Blueprint asli sudah memuat PRD, ERD, SRS, dan rencana tahapan secara lengkap,
sehingga dokumen tersebut **tidak diduplikasi**. Berkas ini hanya menambahkan
status pelaksanaan dan keputusan teknis aktual.

---

## 1. Ringkasan Teknologi

| Aspek | Blueprint | Implementasi | Catatan |
| --- | --- | --- | --- |
| Framework | Laravel (versi terbaru) | **Laravel 10.50** | PHP lokal 8.1.10 membatasi ke v10 |
| PHP | 8.1+ | 8.1.10 | — |
| Basis data | MySQL/PostgreSQL | **MySQL/MariaDB** | Dipilih pengguna (XAMPP) |
| Frontend | Blade + Tailwind + Alpine | Blade + Tailwind 3 + Alpine 3 + Chart.js | Sesuai |
| Aset | Vite | Vite 4 | Konfigurasi CommonJS (bukan ESM) |
| Font | Inter | Inter Variable (self-hosted) | Tidak bergantung CDN |
| Ikon | — | Heroicons outline (generator) | `scripts/generate-icons.cjs` |

### Penyimpangan yang disengaja

1. **Laravel 10, bukan versi terbaru.** PHP 8.1.10 pada lingkungan pengembangan
   membatasi versi framework. Menaikkan Laravel berarti menaikkan PHP lebih
   dahulu.
2. **Tidak ada alur reset kata sandi via email.** Blueprint menempatkannya di
   prioritas P2. Admin dapat mereset kata sandi pengguna dari panel admin.
3. **Admin bersifat read-only pada absensi.** Koreksi hanya dapat dilakukan
   guru pemilik jadwal. Policy `AttendanceSessionPolicy` tetap mengizinkan admin
   secara teknis agar jalur audit admin terbuka bila diperlukan nanti.

---

## 2. Status per Tahap

| Tahap | Cakupan | Status |
| --- | --- | --- |
| 0 | Audit repositori & lingkungan | ✅ |
| 1 | Fondasi proyek & design system | ✅ |
| 2 | Autentikasi, RBAC, data master | ✅ |
| 3 | Panel admin | ✅ |
| 4 | Absensi guru | ✅ |
| 5 | Portal siswa | ✅ |
| 6 | Laporan & dashboard | ✅ |
| 7 | QA, keamanan, polish | ✅ |

### Tahap 0 — Audit

- Lingkungan diverifikasi: PHP 8.1.10, Composer, Node 20, Git, MySQL/MariaDB.
- Struktur proyek dipetakan; tidak ada kode lama yang perlu dipertahankan
  (repositori masih kosong), sehingga tidak ada risiko kehilangan data.
- ERD rancangan diselaraskan dengan migrasi aktual (lihat bagian 3).

### Tahap 1 — Fondasi & design system

- Layout Blade berbasis komponen: `x-layouts.app`, `x-layouts.auth`,
  `x-layouts.landing`, `x-layouts.error`.
- Komponen: `stat-card`, `status-badge`, `empty-state`, `icon`.
- Design system Tailwind (palet `primary`, `cream`, `sage`, `gold`), mode gelap
  berbasis kelas + `localStorage`.
- Komponen Alpine di `resources/js/app.js`: store tema & sidebar,
  `confirmDialog`, `attendanceForm`, `countUp`, toast, reveal.
- Landing page dan halaman login.

### Tahap 2 — Autentikasi, RBAC, data master

- Login dengan **email atau username**; `RateLimiter` 5 percobaan/60 detik.
- Akun nonaktif ditolak saat login (`LoginController`) dan dipaksa logout saat
  sesi berjalan (`EnsureAccountIsActive`).
- Middleware `role:admin|guru|siswa` + policy:
  `SchedulePolicy`, `AttendanceSessionPolicy`.
- 9 model dengan relasi, scope, dan konstanta; 9 factory; 2 seeder
  (`AdminUserSeeder`, `DemoSchoolSeeder`).

### Tahap 3 — Panel admin

- CRUD pengguna, guru, siswa, kelas, mata pelajaran, jadwal.
- `ScheduleConflictChecker` mencegah bentrok jadwal guru dan kelas.
- Penghapusan diblokir bila masih ada relasi/riwayat (`restrictOnDelete`) dengan
  saran menonaktifkan.
- Admin utama (`config('simas.primary_admin_email')`) dilindungi dari hapus dan
  nonaktif.
- Rekap absensi (filter kelas, mapel, status, rentang tanggal) + ekspor CSV.
- Audit log dengan filter aksi.
- Dashboard: statistik nyata + grafik tren 14 hari.

### Tahap 4 — Absensi guru

- Daftar jadwal per tanggal; hanya jadwal milik guru yang login.
- Form absensi massal dengan tombol isi cepat, catatan per siswa, dan
  penghitung langsung (`attendanceForm`).
- **Tanpa status otomatis**: setiap siswa wajib dipilih statusnya.
- Validasi: tanggal harus sesuai hari jadwal, ≤ hari ini, maksimal 90 hari ke
  belakang, semua siswa aktif wajib berstatus.
- Penyimpanan dalam transaksi; audit log `attendance.session_created`.
- Koreksi dalam jendela 7 hari (`config('simas.attendance.edit_window_days')`)
  dengan audit log `attendance.session_corrected`.

### Tahap 5 — Portal siswa

- Dashboard pribadi (ringkasan status, persentase, grafik distribusi, jadwal
  terdekat, riwayat terbaru).
- Profil, jadwal kelas, dan riwayat absensi dengan filter bulan/status/mapel.
- **Isolasi data**: seluruh query dibatasi ke siswa yang login; tidak ada
  parameter URL yang dapat dipakai membuka data siswa lain.

### Tahap 6 — Laporan & dashboard

- Seluruh angka berasal dari query database (`AttendanceStats`, agregasi model).
- Grafik tren kehadiran 14 hari (admin & guru), grafik distribusi (siswa).
- Rekap per kelas dan periode + ekspor CSV (mengikuti filter aktif).
- Persentase = `hadir / total`; bila kosong ditampilkan **"Belum ada data"**.

### Tahap 7 — QA, keamanan, polish

- **60 test** fungsional & keamanan lulus (`php artisan test`).
- Test khusus performa mendeteksi **N+1** pada halaman dashboard admin, rekap
  absensi, detail sesi guru, riwayat siswa, dan daftar siswa.
- Halaman error kustom: 403, 404, 419, 500.
- Responsif (sidebar/drawer, tabel dapat digulir), aksesibilitas (label, `aria-*`,
  `focus-visible`, navigasi keyboard, `prefers-reduced-motion`).
- README memuat instalasi, akun demo, aturan bisnis, dan panduan test.

### Bug nyata yang ditemukan & diperbaiki saat QA

Pengujian di browser sungguhan (Chrome headless, bukan hanya status HTTP)
menemukan empat cacat yang tidak terdeteksi oleh test HTTP biasa:

| Bug | Gejala | Perbaikan |
| --- | --- | --- |
| Penyisipan absensi gagal | Pelanggaran NOT NULL pada `attendance_session_id` saat menyimpan sesi | Memakai `Attendance::insert()` dengan kolom sesi yang eksplisit |
| Konten tertimpa sidebar | `paddingLeft` = 0 pada layar ≥ 1024 px karena direktif `:class` berada di luar scope `x-data` | Menambahkan `x-data` pada pembungkus konten |
| Grafik tidak tampil | `Chart is not defined` — skrip inline berjalan sebelum modul ES dimuat | Antrean `window.SIMAS_onReady()` yang dijalankan setelah runtime siap |
| Overflow horizontal di ponsel | Tabel mendorong lebar halaman (mis. 711 px pada viewport 390 px) karena anak grid tidak dapat menyusut | `min-w-0` pada `.card`/`.table-wrap` dan pagination yang dapat membungkus |

Selain itu, kolom tanggal kini memakai cast `date:Y-m-d` agar format tanggal
konsisten antara MySQL dan SQLite (sebelumnya SQLite menyimpan
`2026-10-15 00:00:00`).

---

## 3. ERD Aktual

Sembilan tabel, sesuai ERD rancangan:

| Tabel | Kunci & Constraint penting |
| --- | --- |
| `users` | `username` unik, `email` unik, `role` enum(admin/guru/siswa), `is_active` |
| `teachers` | `user_id` unik → users (`restrictOnDelete`), `teacher_number` unik |
| `students` | `user_id` unik → users, `class_id` → classes (`nullOnDelete`), `student_number` unik |
| `classes` | `homeroom_teacher_id` → teachers (`nullOnDelete`), unik (`name`, `academic_year`) |
| `subjects` | `code` unik |
| `schedules` | unik (`class_id`, `teacher_id`, `subject_id`, `day_of_week`, `start_time`) |
| `attendance_sessions` | unik (**`schedule_id`, `session_date`**), index `session_date` |
| `attendances` | unik (**`attendance_session_id`, `student_id`**) |
| `audit_logs` | `entity_type`, `entity_id`, `metadata` (JSON), hanya `created_at` |

**Constraint anti-duplikasi** berada di level database sehingga tetap berlaku
meskipun ada permintaan ganda.

### Aturan integritas

- Riwayat absensi **tidak ikut terhapus** saat siswa/guru/kelas/mapel dihapus.
  Operasi hapus ditolak (`restrictOnDelete` atau pemeriksaan eksplisit) dan UI
  menyarankan **menonaktifkan** data tersebut.

---

## 4. Strategi Pengujian yang Dijalankan

| Area | Berkas test |
| --- | --- |
| Login valid/invalid, akun nonaktif, redirect role, rate limit | `Auth/AuthenticationTest` |
| Akses lintas role, tamu, siswa mencoba menulis absensi | `Auth/AuthorizationTest` |
| CRUD & validasi unique, bentrok jadwal, perlindungan admin utama | `Admin/MasterDataTest` |
| Dashboard, filter rekap, CSV, audit log, statistik & tren | `Admin/ReportTest` |
| Isi absensi, kepemilikan jadwal, wajib semua status, duplikasi, jendela koreksi | `Guru/AttendanceTest` |
| Isolasi data siswa, filter, ringkasan & persentase | `Siswa/StudentPortalTest` |
| Deteksi N+1 pada halaman utama | `Performance/QueryCountTest` |

Test berjalan pada **SQLite in-memory** (`phpunit.xml`) agar tidak menyentuh
basis data MySQL pengembangan. Seluruh query aplikasi dibuat portabel
(termasuk pengurutan hari melalui `Schedule::scopeOrderByDay`, menggantikan
`FIELD()` yang hanya tersedia di MySQL).

---

## 5. Catatan Operasional

- **Kredensial demo** hanya ada di README dan seeder; `.env` tidak di-commit.
- **Seeder deterministik** (`mt_srand(20260101)`) membuat jumlah dan isi data
  selalu sama pada setiap `migrate:fresh --seed`.
- **Screenshot** tersedia di `docs/screenshots/` (19 gambar: landing, login,
  dashboard tiap role, form absensi, mode gelap, tampilan ponsel, dan halaman
  error). Jalankan `node scripts/capture-screenshots.cjs` untuk membuat ulang.
