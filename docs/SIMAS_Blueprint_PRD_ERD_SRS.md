# SIMAS --- Blueprint Produk & Teknis

## Sistem Informasi Manajemen Absensi Siswa

**Versi dokumen:** 1.0\
**Status:** Rancangan awal untuk diaudit dan disesuaikan dengan kondisi
repository\
**Target:** Proyek portofolio full-stack yang profesional, aman, dan
dapat didemonstrasikan

------------------------------------------------------------------------

# 1. Ringkasan Eksekutif

SIMAS adalah aplikasi web untuk membantu sekolah mengelola data siswa,
guru, kelas, mata pelajaran, jadwal, dan kehadiran. Produk memiliki tiga
peran utama: Admin, Guru, dan Siswa. Tujuan portofolio bukan hanya
menampilkan UI menarik, tetapi menunjukkan pemahaman tentang database
relasional, autentikasi, otorisasi, validasi, pelaporan, pengujian, dan
pengalaman pengguna.

**Prinsip utama produk:** 1. Fitur harus benar-benar berfungsi dan
membaca/menulis data database. 2. UI konsisten dan terasa seperti produk
SaaS, bukan template CRUD default. 3. Hak akses wajib ditegakkan di
server, bukan hanya menyembunyikan tombol. 4. Setiap tahap harus bisa
diuji sebelum melanjutkan. 5. Jangan menimpa atau menghapus kode yang
sudah berfungsi sebelum melakukan audit. 6. Keputusan arsitektur harus
sesuai versi Laravel dan struktur repository yang benar-benar ada.

## 1.1 Tujuan

-   Mengelola data akademik dasar dalam satu aplikasi.
-   Mempermudah guru mencatat kehadiran kelas.
-   Memberi siswa akses aman ke jadwal dan riwayat kehadiran pribadi.
-   Menyediakan rekap yang dapat difilter dan diekspor.
-   Menjadi demonstrasi kemampuan pengembangan aplikasi web end-to-end.

## 1.2 Bukan tujuan tahap awal

-   Integrasi ke sistem akademik eksternal.
-   Aplikasi mobile native.
-   Face recognition atau biometrik.
-   Pembayaran sekolah.
-   Notifikasi WhatsApp berbayar.
-   Multi-sekolah/tenant. Fitur tersebut bisa dipertimbangkan setelah
    MVP stabil.

------------------------------------------------------------------------

# 2. PRD --- Product Requirements Document

## 2.1 Pengguna dan kebutuhan

### Admin

**Kebutuhan:** mengelola akun dan data akademik, memantau ringkasan
kehadiran, dan membuat laporan.\
**Sukses jika:** data master konsisten, dashboard akurat, dan tindakan
admin tercatat/terkendali.

### Guru

**Kebutuhan:** melihat jadwal yang ditugaskan, mencatat kehadiran satu
kelas dengan cepat, serta memperbaiki catatan sesuai izin.\
**Sukses jika:** pencatatan massal mudah, tidak ada duplikasi absensi
untuk siswa dan sesi yang sama, dan rekap kelas benar.

### Siswa

**Kebutuhan:** melihat jadwal, profil, ringkasan kehadiran, dan riwayat
miliknya sendiri.\
**Sukses jika:** informasi mudah ditemukan dan siswa tidak dapat membaca
atau mengubah data siswa lain.

## 2.2 Ruang lingkup MVP

### P0 --- wajib untuk versi pertama

-   Login dan logout.
-   Role Admin, Guru, Siswa.
-   Status akun aktif/nonaktif.
-   Dashboard berdasarkan role.
-   CRUD pengguna sesuai kewenangan.
-   CRUD kelas, mata pelajaran, dan jadwal.
-   Relasi guru, siswa, kelas, dan jadwal.
-   Pencatatan absensi: Hadir, Izin, Sakit, Alpa.
-   Satu catatan absensi per siswa per jadwal/pertemuan.
-   Riwayat absensi dan rekap berdasarkan periode.
-   Pencarian, filter, pagination.
-   Otorisasi server-side dan validasi.
-   Seeder akun demo dan data contoh non-sensitif.
-   Pengujian alur utama dan dokumentasi instalasi.

### P1 --- setelah P0 stabil

-   Ekspor CSV.
-   Grafik tren kehadiran.
-   Audit log untuk perubahan absensi.
-   Filter lanjutan dan ringkasan per kelas.
-   Preferensi tema terang/gelap.
-   Notifikasi dalam aplikasi.

### P2 --- opsional

-   Cetak laporan PDF.
-   Impor data siswa dari CSV dengan validasi.
-   Notifikasi email.
-   Kalender akademik dan hari libur.

## 2.3 Fitur per modul

### A. Autentikasi dan akun

-   Login menggunakan email atau username jika sesuai struktur yang
    sudah ada.
-   Password wajib di-hash.
-   Logout mengakhiri sesi dengan benar.
-   Akun nonaktif tidak boleh login.
-   Redirect setelah login sesuai role.
-   Validasi input dan pesan error yang ramah.
-   Batasi percobaan login bila sesuai kemampuan stack yang tersedia.

### B. Admin --- pengguna

-   Melihat daftar akun dengan pencarian, filter role/status, dan
    pagination.
-   Membuat, melihat, mengubah, mengaktifkan/menonaktifkan akun.
-   Reset password melalui alur yang aman jika fitur disediakan.
-   Mencegah username/email duplikat.
-   Mencegah admin utama dihapus atau dinonaktifkan secara tidak
    sengaja.
-   Tidak menyimpan password dalam teks biasa.

### C. Admin --- data akademik

-   CRUD guru dan siswa.
-   CRUD kelas dan penetapan wali kelas.
-   CRUD mata pelajaran.
-   Pengelolaan jadwal yang menghubungkan kelas, mata pelajaran, guru,
    hari, jam mulai, dan jam selesai.
-   Validasi agar jam selesai setelah jam mulai.
-   Cegah konflik jadwal guru/kelas jika aturan ini masuk ke cakupan
    MVP.

### D. Guru --- absensi

-   Guru hanya melihat kelas/jadwal yang menjadi tanggung jawabnya.
-   Memilih tanggal dan sesi jadwal.
-   Melihat daftar siswa kelas tersebut.
-   Mengisi status massal dengan default yang aman; jangan otomatis
    menganggap semua siswa hadir tanpa konfirmasi.
-   Status: `hadir`, `izin`, `sakit`, `alpa`.
-   Catatan bersifat opsional.
-   Koreksi hanya untuk sesi yang boleh diubah dan harus tervalidasi.
-   Simpan perubahan secara transaksional untuk menghindari data
    parsial.
-   Cegah duplikasi berdasarkan siswa dan pertemuan.
-   Tampilkan ringkasan jumlah status setelah penyimpanan.

### E. Siswa --- portal pribadi

-   Melihat profil sendiri.
-   Melihat jadwal kelasnya.
-   Melihat jumlah Hadir/Izin/Sakit/Alpa.
-   Melihat persentase kehadiran dengan rumus yang dijelaskan secara
    konsisten.
-   Memfilter riwayat berdasarkan tanggal/bulan/status.
-   Tidak memiliki endpoint untuk membuat, mengubah, atau menghapus
    absensi.
-   Semua query riwayat dibatasi ke siswa yang sedang login, bukan hanya
    berdasarkan ID dari URL.

### F. Dashboard dan laporan

-   Data berasal dari database, bukan angka statis.
-   Admin: jumlah siswa, guru, kelas, rekap kehadiran hari ini/periode,
    grafik, aktivitas terbaru jika audit log tersedia.
-   Guru: jadwal hari ini, kelas yang diampu, status kelengkapan
    absensi.
-   Siswa: ringkasan pribadi, jadwal terdekat, riwayat terbaru.
-   Semua grafik memiliki empty state saat data belum tersedia.
-   Filter periode harus diterapkan pada angka ringkasan dan tabel
    secara konsisten.

## 2.4 Aturan bisnis

1.  Setiap akun memiliki satu role utama pada MVP: `admin`, `guru`, atau
    `siswa`.
2.  Akun nonaktif tidak dapat login.
3.  Email dan username unik.
4.  Siswa terkait dengan satu kelas aktif pada model MVP. Jika riwayat
    perpindahan kelas dibutuhkan, gunakan tabel riwayat kelas pada tahap
    lanjutan.
5.  Jadwal menghubungkan satu kelas, satu mata pelajaran, dan satu guru.
6.  Satu sesi absensi adalah kombinasi jadwal + tanggal/pertemuan.
7.  Dalam satu sesi, setiap siswa hanya memiliki satu status absensi.
8.  Status yang diperbolehkan hanya Hadir, Izin, Sakit, Alpa.
9.  Guru hanya boleh mencatat absensi kelas yang ditugaskan.
10. Siswa hanya boleh melihat data miliknya sendiri.
11. Persentase kehadiran MVP:
    `jumlah hadir / jumlah catatan absensi yang tercatat × 100`. Jika
    denominator nol, tampilkan 0% atau "Belum ada data"; gunakan satu
    kebijakan yang konsisten. Bila sekolah menginginkan rumus lain,
    dokumentasikan sebelum implementasi.
12. Absensi yang belum dicatat tidak otomatis dianggap Alpa; tampilkan
    sebagai belum diisi sampai sesi ditutup sesuai kebijakan.
13. Penghapusan data master yang sudah memiliki relasi perlu dicegah
    atau ditangani dengan soft delete/aturan yang eksplisit.
14. Semua tanggal/jam menggunakan zona waktu aplikasi yang dikonfigurasi
    dan ditampilkan konsisten.

## 2.5 Kriteria penerimaan utama

-   Login tiap role menuju dashboard yang tepat.
-   Akun nonaktif ditolak.
-   Admin dapat membuat data akademik dan data tersimpan.
-   Guru tidak dapat mencatat absensi kelas yang tidak ditugaskan.
-   Sistem menolak absensi ganda untuk siswa pada sesi yang sama.
-   Perubahan absensi mengubah ringkasan dan grafik.
-   Siswa A tidak bisa melihat data Siswa B dengan mengganti ID/URL atau
    parameter request.
-   Input tidak valid menampilkan pesan yang jelas dan tidak merusak
    database.
-   UI dapat dipakai di desktop dan layar ponsel.
-   Test inti lulus dan README menjelaskan cara menjalankan aplikasi.

------------------------------------------------------------------------

# 3. SRS --- Spesifikasi Kebutuhan Sistem

## 3.1 Kebutuhan fungsional

-   FR-01: Sistem menyediakan autentikasi.
-   FR-02: Sistem memverifikasi role dan status akun.
-   FR-03: Sistem menyediakan dashboard sesuai role.
-   FR-04: Admin mengelola akun dan data akademik.
-   FR-05: Admin mengelola jadwal kelas.
-   FR-06: Guru melihat jadwal yang ditugaskan.
-   FR-07: Guru mencatat/memperbarui absensi sesuai izin.
-   FR-08: Sistem mencegah absensi ganda per siswa per sesi.
-   FR-09: Sistem menyediakan riwayat dan rekap absensi.
-   FR-10: Siswa hanya melihat profil, jadwal, dan absensi sendiri.
-   FR-11: Sistem menyediakan pencarian, filter, dan pagination.
-   FR-12: Sistem menyediakan ekspor CSV setelah fitur dasar stabil.
-   FR-13: Sistem menyediakan data demo melalui seeder.
-   FR-14: Sistem menampilkan validasi, konfirmasi, loading, empty,
    success, dan error state.

## 3.2 Kebutuhan nonfungsional

-   Keamanan: otorisasi server-side, CSRF, hashing password, validasi,
    mass-assignment protection, dan pengelolaan rahasia lewat `.env`.
-   Privasi: data siswa tidak boleh terbuka lintas akun.
-   Kinerja: query menggunakan pagination dan menghindari N+1 dengan
    eager loading yang sesuai.
-   Aksesibilitas: label form, fokus keyboard yang terlihat, kontras
    yang memadai, dan reduced motion.
-   Responsivitas: layout bekerja di desktop, tablet, dan ponsel.
-   Maintainability: gunakan pola MVC Laravel, validasi terpisah bila
    sesuai, penamaan konsisten, dan hindari controller terlalu besar.
-   Reliability: operasi absensi massal menggunakan transaction;
    constraint database untuk mencegah duplikasi.
-   Testability: uji autentikasi, otorisasi, validasi, aturan absensi,
    dan query data siswa.
-   Dokumentasi: README instalasi, konfigurasi, akun demo, fitur, dan
    screenshot.

## 3.3 Asumsi dan keputusan yang perlu dikonfirmasi saat audit

-   Versi Laravel/PHP aktual.
-   Apakah autentikasi dan middleware role sudah dibuat.
-   Apakah tabel pengguna sudah memuat `username`, `role`, dan
    `is_active`.
-   Apakah model Guru dan Siswa terpisah dari tabel users atau profil
    terhubung.
-   Kebijakan absensi: per jadwal pelajaran atau satu kali per hari.
-   Apakah siswa boleh memiliki riwayat perpindahan kelas.
-   Apakah perlu persetujuan admin untuk koreksi absensi.
-   Format laporan yang dibutuhkan. Jangan membuat migrasi baru sebelum
    memeriksa migrasi dan data yang telah ada.

------------------------------------------------------------------------

# 4. ERD --- Rancangan Database

## 4.1 Entitas inti

Rancangan berikut adalah model konseptual awal. Sesuaikan dengan tabel
yang sudah ada di repository sebelum membuat migrasi.

-   `users`: akun login dan role.
-   `teachers`: profil guru yang terhubung ke akun.
-   `students`: profil siswa yang terhubung ke akun dan kelas.
-   `classes`: data kelas dan wali kelas.
-   `subjects`: mata pelajaran.
-   `schedules`: jadwal kelas, guru, dan mata pelajaran.
-   `attendance_sessions`: satu pertemuan absensi untuk jadwal dan
    tanggal tertentu.
-   `attendances`: status setiap siswa pada sesi tersebut.
-   `audit_logs`: catatan perubahan penting, direkomendasikan setelah
    alur inti stabil.

## 4.2 Diagram ERD (Mermaid)

``` mermaid
erDiagram
    USERS ||--o| TEACHERS : has_profile
    USERS ||--o| STUDENTS : has_profile
    CLASSES o|--o{ STUDENTS : contains
    TEACHERS o|--o{ CLASSES : homeroom_teacher
    CLASSES ||--o{ SCHEDULES : has
    TEACHERS ||--o{ SCHEDULES : teaches
    SUBJECTS ||--o{ SCHEDULES : assigned_to
    SCHEDULES ||--o{ ATTENDANCE_SESSIONS : creates
    ATTENDANCE_SESSIONS ||--o{ ATTENDANCES : records
    STUDENTS ||--o{ ATTENDANCES : receives
    USERS o|--o{ AUDIT_LOGS : performs

    USERS {
      bigint id PK
      string name
      string username UK
      string email UK
      string password
      enum role
      boolean is_active
      timestamp email_verified_at
      timestamps created_at_updated_at
    }
    TEACHERS {
      bigint id PK
      bigint user_id FK
      string teacher_number UK
      string phone
      string address
      timestamps created_at_updated_at
    }
    STUDENTS {
      bigint id PK
      bigint user_id FK
      bigint class_id FK
      string student_number UK
      date birth_date
      string phone
      string address
      timestamps created_at_updated_at
    }
    CLASSES {
      bigint id PK
      string name UK
      string academic_year
      bigint homeroom_teacher_id FK
      boolean is_active
      timestamps created_at_updated_at
    }
    SUBJECTS {
      bigint id PK
      string code UK
      string name
      text description
      timestamps created_at_updated_at
    }
    SCHEDULES {
      bigint id PK
      bigint class_id FK
      bigint teacher_id FK
      bigint subject_id FK
      enum day_of_week
      time start_time
      time end_time
      boolean is_active
      timestamps created_at_updated_at
    }
    ATTENDANCE_SESSIONS {
      bigint id PK
      bigint schedule_id FK
      date session_date
      string topic
      bigint created_by FK
      timestamp submitted_at
      timestamps created_at_updated_at
    }
    ATTENDANCES {
      bigint id PK
      bigint attendance_session_id FK
      bigint student_id FK
      enum status
      text note
      bigint recorded_by FK
      timestamp recorded_at
      timestamps created_at_updated_at
    }
    AUDIT_LOGS {
      bigint id PK
      bigint user_id FK
      string action
      string entity_type
      bigint entity_id
      json metadata
      string ip_address
      timestamp created_at
    }
```

## 4.3 Rincian kolom dan constraint

### `users`

-   `id`: primary key.
-   `name`: nama lengkap.
-   `username`: unik, opsional hanya jika aplikasi memang memakai login
    username.
-   `email`: unik.
-   `password`: hash password, tidak pernah teks biasa.
-   `role`: nilai terbatas `admin`, `guru`, `siswa`.
-   `is_active`: boolean.
-   Gunakan timestamps Laravel.
-   Jangan simpan API key atau rahasia di tabel ini.

### `teachers`

-   `user_id`: foreign key unik ke `users.id` agar satu akun hanya
    memiliki satu profil guru.
-   `teacher_number`: nomor induk guru, unik jika dipakai.
-   Data pribadi minimum yang memang diperlukan saja.

### `students`

-   `user_id`: foreign key unik ke `users.id`.
-   `class_id`: foreign key ke kelas; nullable dapat dipakai untuk siswa
    yang belum ditempatkan.
-   `student_number`: nomor induk siswa unik.
-   Hindari mengumpulkan data sensitif yang tidak diperlukan untuk
    aplikasi demo.

### `classes`

-   `name` tidak selalu unik secara global bila ada tahun ajaran;
    pertimbangkan unique gabungan `(name, academic_year)`.
-   `homeroom_teacher_id` boleh nullable.
-   Aturan penghapusan: jangan cascade menghapus riwayat absensi ketika
    kelas dihapus.

### `subjects`

-   `code` unik.
-   `name` wajib.
-   Pertimbangkan status aktif jika diperlukan.

### `schedules`

-   Foreign key ke kelas, guru, dan mata pelajaran.
-   `day_of_week` dibatasi pada nilai hari yang konsisten.
-   `end_time` harus lebih besar daripada `start_time`.
-   Tambahkan indeks pada foreign key dan kolom yang sering difilter.
-   Cegah bentrok jadwal melalui validasi aplikasi; constraint sederhana
    tidak cukup untuk semua aturan overlap.

### `attendance_sessions`

-   Mengacu pada satu jadwal dan satu tanggal.
-   Unique gabungan `(schedule_id, session_date)` bila kebijakan hanya
    mengizinkan satu sesi per jadwal per hari.
-   Jika ada lebih dari satu sesi untuk jadwal yang sama pada hari yang
    sama, gunakan nomor/pertemuan atau identitas sesi tambahan sebagai
    bagian unique key.
-   `created_by` mengacu pada user pembuat.

### `attendances`

-   Foreign key ke sesi dan siswa.
-   Unique gabungan `(attendance_session_id, student_id)` untuk mencegah
    catatan ganda.
-   `status` hanya `hadir`, `izin`, `sakit`, `alpa`.
-   `recorded_by` mengacu ke user yang mencatat.
-   Gunakan transaction untuk penyimpanan absensi massal.

### `audit_logs` (opsional P1)

-   Catat aktor, aksi, entitas, waktu, dan metadata yang tidak sensitif.
-   Jangan menyimpan password, token, atau data pribadi berlebihan dalam
    metadata.

## 4.4 Aturan penghapusan dan integritas

-   Gunakan `restrict`/`no action` untuk entitas yang memiliki riwayat
    penting, atau soft delete jika cocok.
-   Jangan gunakan cascade delete dari siswa ke absensi.
-   Pastikan siswa yang tidak aktif tetap memiliki riwayat yang bisa
    dilaporkan sesuai kebijakan.
-   Semua foreign key memakai tipe yang cocok dengan primary key.
-   Indeks untuk kolom foreign key, tanggal sesi, status, role, dan
    kolom pencarian yang benar-benar digunakan.

------------------------------------------------------------------------

# 5. UI/UX Specification

## 5.1 Identitas visual

Palet: - Primary green: `#537963` - Deep green: `#263C32` - Background:
`#F5F6F2` - Soft sage: `#E4EBE5` - Gold accent: `#D5A85C` - Teks utama:
warna slate/near-black dengan kontras yang cukup. Gunakan warna status
yang mudah dibedakan dan jangan mengandalkan warna saja; sertakan label
teks.

## 5.2 Landing page

1.  Navbar dengan logo SIMAS, navigasi fitur, dan tombol masuk.
2.  Hero dengan headline "Absensi Lebih Mudah, Pengelolaan Sekolah Lebih
    Teratur."
3.  Visual mockup dashboard yang konsisten dengan aplikasi sebenarnya.
4.  Bagian manfaat dan fitur.
5.  Alur penggunaan singkat.
6.  CTA untuk masuk.
7.  Footer profesional.

## 5.3 Dashboard

-   Sidebar dapat diciutkan dan menjadi drawer pada layar kecil.
-   Header berisi judul halaman, breadcrumb bila membantu, notifikasi,
    dan profil.
-   Kartu statistik memakai angka dari database.
-   Grafik selalu memiliki label, tooltip, dan empty state.
-   Tabel mendukung pencarian, filter, sort yang relevan, pagination,
    dan indikator jumlah hasil.
-   Form memiliki label, hint, validasi, dan pesan sukses/gagal.

## 5.4 Motion design

-   Fade/slide pendek saat bagian halaman masuk viewport.
-   Hover dan focus transition 150--250 ms.
-   Animasi angka hanya ketika membantu pemahaman.
-   Modal dan toast dengan transisi singkat.
-   Skeleton hanya ketika data memang sedang dimuat.
-   Hormati `prefers-reduced-motion`.
-   Hindari parallax berat, animasi looping berlebihan, dan efek yang
    menurunkan aksesibilitas/performa.

## 5.5 Halaman minimum

-   Landing page.
-   Login.
-   Dashboard Admin.
-   Manajemen Pengguna.
-   Manajemen Guru dan Siswa.
-   Manajemen Kelas.
-   Manajemen Mata Pelajaran.
-   Manajemen Jadwal.
-   Sesi dan Form Absensi Guru.
-   Rekap Absensi.
-   Dashboard Siswa.
-   Profil Siswa.
-   Jadwal Siswa.
-   Riwayat Absensi Siswa.
-   Halaman 403/404/500 yang konsisten.

------------------------------------------------------------------------

# 6. Matriks Hak Akses

  --------------------------------------------------------------------------
  Fitur                Admin             Guru              Siswa
  -------------------- ----------------- ----------------- -----------------
  Dashboard            Semua ringkasan   Ringkasan         Ringkasan pribadi
                                         tugasnya          

  Kelola akun          Ya, sesuai aturan Tidak             Tidak

  Kelola               Ya                Baca jadwal tugas Baca jadwal
  kelas/mapel/jadwal                                       sendiri

  Catat absensi        Opsional sesuai   Kelas yang        Tidak
                       kebijakan         ditugaskan        

  Koreksi absensi      Sesuai kebijakan  Sesi yang         Tidak
                       dan tercatat      ditugaskan dan    
                                         diizinkan         

  Lihat semua siswa    Ya                Hanya kelas yang  Tidak
                                         ditugaskan        

  Riwayat absensi      Ya untuk          Ya jika relevan   Ya
  pribadi              administrasi                        

  Riwayat siswa lain   Ya sesuai         Hanya kelas yang  Tidak
                       kebutuhan tugas   ditugaskan        

  Ekspor laporan       Ya                Rekap kelas yang  Tidak atau hanya
                                         diizinkan         data sendiri
  --------------------------------------------------------------------------

**Catatan keamanan:** matriks ini harus diimplementasikan pada route,
middleware, policy/gate, dan query. Menyembunyikan menu di frontend saja
tidak cukup.

------------------------------------------------------------------------

# 7. Rencana Implementasi Bertahap

## Tahap 0 --- Audit tanpa mengubah kode

-   Identifikasi versi Laravel/PHP.
-   Periksa routes, migrations, models, controllers, middleware, views,
    seeders, tests, dan konfigurasi.
-   Catat fitur yang sudah ada dan yang rusak.
-   Jangan menimpa file atau menjalankan migrasi destruktif.

**Selesai jika:** ada laporan audit dan rencana yang sesuai kondisi
repository.

## Tahap 1 --- Fondasi dan design system

-   Tentukan layout, warna, typography, spacing, ikon, tombol, form,
    tabel, modal, toast.
-   Buat layout responsif dan navigasi sesuai role.
-   Bangun landing page.
-   Pastikan reduced motion dan mobile layout.

**Selesai jika:** halaman inti konsisten, responsif, dan tidak merusak
fitur lama.

## Tahap 2 --- Database dan autentikasi

-   Rekonsiliasi ERD dengan migrasi aktual.
-   Implementasi model, relasi, foreign key, seeder.
-   Login/logout, status akun, middleware/policy.
-   Uji akses berdasarkan role.

**Selesai jika:** role dan sesi berfungsi, serta akses tanpa izin
ditolak server.

## Tahap 3 --- Modul Admin

-   Pengguna, guru, siswa, kelas, mapel, jadwal.
-   Validasi, pencarian, filter, pagination.
-   Perlindungan akun admin utama dan integritas data.

**Selesai jika:** CRUD dan validasi lolos tes.

## Tahap 4 --- Absensi Guru

-   Daftar kelas/jadwal sesuai penugasan.
-   Buat sesi absensi.
-   Form absensi massal.
-   Simpan dengan transaction.
-   Cegah duplikasi dan batasi koreksi.

**Selesai jika:** data tersimpan benar dan tidak ada absensi ganda.

## Tahap 5 --- Portal Siswa

-   Dashboard pribadi.
-   Jadwal kelas.
-   Riwayat dan filter.
-   Ringkasan status serta persentase kehadiran.

**Selesai jika:** siswa tidak dapat melihat data siswa lain, termasuk
melalui URL/request yang dimanipulasi.

## Tahap 6 --- Laporan dan dashboard

-   Statistik dari query database.
-   Grafik tren.
-   Rekap per kelas dan periode.
-   Ekspor CSV jika P0 sudah stabil.

**Selesai jika:** angka tabel dan grafik konsisten dengan data sumber.

## Tahap 7 --- QA, keamanan, dan polish

-   Jalankan test suite.
-   Periksa responsivitas dan aksesibilitas.
-   Periksa query lambat/N+1.
-   Periksa validasi, error handling, dan halaman kosong.
-   Rapikan README, screenshot, akun demo, dan dokumentasi.

**Selesai jika:** alur utama teruji dan proyek dapat dijalankan ulang
berdasarkan README.

------------------------------------------------------------------------

# 8. Strategi Testing

## Pengujian fungsional

-   Login valid dan invalid.
-   Akun nonaktif ditolak.
-   Redirect dashboard berdasarkan role.
-   CRUD data master dan validasi unique.
-   Guru hanya melihat jadwal yang ditugaskan.
-   Absensi massal menyimpan semua baris secara konsisten.
-   Duplikasi sesi/absensi ditolak.
-   Filter tanggal/status menghasilkan data benar.
-   Statistik sesuai data absensi.

## Pengujian keamanan

-   Siswa A mencoba membuka riwayat Siswa B.
-   Siswa mencoba POST/PUT/DELETE ke endpoint absensi.
-   Guru mencoba memanipulasi ID kelas yang bukan tanggung jawabnya.
-   User tanpa login mengakses halaman privat.
-   Input tidak valid atau ID yang tidak ada.
-   Akun admin utama tidak dapat dihapus melalui jalur biasa.

## Pengujian UI

-   Desktop, tablet, ponsel.
-   Sidebar/drawer.
-   Form dengan pesan error.
-   Tabel kosong dan hasil pencarian kosong.
-   Loading, sukses, gagal, dan konfirmasi hapus.
-   Navigasi keyboard dan reduced motion.

------------------------------------------------------------------------

# 9. Struktur Dokumentasi Repository

Disarankan menambahkan folder `docs/` di repository setelah audit: -
`docs/PRD.md` --- tujuan, persona, ruang lingkup, fitur, aturan
bisnis. - `docs/ERD.md` --- entitas, relasi, constraint, dan diagram
Mermaid. - `docs/SRS.md` --- kebutuhan fungsional/nonfungsional dan
kriteria penerimaan. - `docs/UI-UX.md` --- design system, halaman,
navigasi, motion design. - `docs/IMPLEMENTATION-PLAN.md` --- tahapan dan
definisi selesai. - `README.md` --- instalasi, konfigurasi, migrasi,
seeder, akun demo, test, screenshot.

Jangan membuat salinan dokumen secara membabi buta bila repository sudah
memiliki dokumentasi setara. Audit terlebih dahulu lalu gabungkan atau
perbarui secara terkontrol.

------------------------------------------------------------------------

# 10. Prompt Audit untuk OpenCode

Salin prompt ini setelah membuka folder repository SIMAS yang benar.

> Bertindaklah sebagai Senior Laravel Architect dan UI/UX Designer. Baca
> `docs/` jika sudah ada, lalu audit seluruh repository sebelum mengubah
> file.
>
> Gunakan blueprint SIMAS sebagai arah produk, tetapi jangan
> mengasumsikan bahwa rancangan ERD cocok langsung dengan migrasi yang
> ada. Bandingkan rancangan dengan struktur aktual.
>
> Laporkan: 1. Versi PHP/Laravel dan struktur proyek. 2. Fitur yang
> sudah ada, belum ada, dan bermasalah. 3. Perbedaan ERD rancangan
> dengan migrasi/model aktual. 4. Risiko kehilangan data atau perubahan
> yang tidak kompatibel. 5. Masalah autentikasi, otorisasi, validasi,
> dan privasi. 6. Rencana tahap implementasi dengan file yang mungkin
> terdampak. 7. Tes yang perlu dijalankan.
>
> Jangan mengubah file, menjalankan migrasi, menghapus data, atau
> melakukan upgrade dependensi pada tahap audit. Jangan menampilkan
> rahasia dari `.env`. Berhenti setelah laporan audit dan tunggu
> instruksi.

------------------------------------------------------------------------

# 11. Aturan Hemat Token untuk Coding Agent

1.  Minta audit dan rencana terlebih dahulu.
2.  Kerjakan satu tahap atau satu modul setiap kali.
3.  Beri konteks hanya file yang relevan; hindari meminta AI membaca
    ulang seluruh proyek berulang kali.
4.  Setelah perubahan, minta ringkasan file yang berubah dan tes yang
    dijalankan.
5.  Jangan meminta "buat semuanya sekaligus" atau "ulang dari awal"
    untuk masalah kecil.
6.  Simpan checkpoint Git pada tahap yang berhasil.
7.  Minta AI menjelaskan asumsi dan risiko sebelum perubahan database.
8.  Jika terjadi error, kirim pesan error lengkap dan langkah
    reproduksi, bukan hanya "masih error".
9.  Jangan pernah menaruh API key/token di prompt, source control,
    screenshot, atau README.
10. Pantau penggunaan token pada dashboard provider; jumlah aktual
    bergantung pada model, konteks, dan cara provider menghitung
    penggunaan.

------------------------------------------------------------------------

# 12. Definisi "Siap Dipamerkan"

SIMAS baru layak dipamerkan ketika: - \[ \] Landing page memiliki
identitas visual konsisten. - \[ \] Login dan role bekerja. - \[ \]
Admin dapat mengelola data master. - \[ \] Guru dapat mencatat
absensi. - \[ \] Siswa hanya dapat melihat data pribadi. - \[ \] Rekap
dan grafik mengambil data database. - \[ \] Validasi dan error state
jelas. - \[ \] Tidak ada error utama saat menjalankan alur demo. - \[ \]
Test inti lulus. - \[ \] README menjelaskan instalasi dari awal. - \[ \]
Akun demo tersedia tanpa menggunakan data pribadi nyata. - \[ \]
Screenshot desktop dan mobile siap untuk portofolio. - \[ \] File `.env`
dan kredensial tidak ikut di-commit.

**Catatan akhir:** dokumen ini adalah rancangan awal yang sengaja cukup
lengkap. ERD dan aturan bisnis harus diselaraskan dengan kode SIMAS yang
benar-benar ada sebelum migrasi baru dibuat. Prioritas pertama adalah
audit repository, lalu implementasi bertahap.
