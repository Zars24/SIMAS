<x-layouts.landing>
    {{-- ===================== HERO ===================== --}}
    <section class="lp-hero">
        <div class="lp-container">
            <div class="grid gap-14 pb-16 pt-14 lg:grid-cols-12 lg:items-center lg:gap-10 lg:pb-24 lg:pt-20 xl:pb-28 xl:pt-24">
                <div class="lg:col-span-6">
                    <p class="lp-eyebrow" data-reveal>Sistem Informasi Manajemen Absensi</p>

                    <h1 class="lp-h1 lp-display mt-6" data-reveal style="--reveal-delay: 60ms">
                        Absensi yang tertib,
                        <span class="block">administrasi yang <em class="text-[color:var(--lp-accent)]">tenang</em>.</span>
                    </h1>

                    <p class="lp-lead mt-6 max-w-xl" data-reveal style="--reveal-delay: 110ms">
                        SIMAS mencatat kehadiran siswa dalam hitungan detik, merangkumnya otomatis,
                        dan memberi setiap peran akses yang tepat — tanpa tumpukan rekap manual.
                    </p>

                    <div class="mt-9 flex flex-wrap items-center gap-3" data-reveal style="--reveal-delay: 160ms">
                        <a href="{{ route('login') }}" class="lp-btn lp-btn-primary">
                            Coba Akun Demo
                            <x-icon name="arrow-right-on-rectangle" class="lp-arrow h-4 w-4" />
                        </a>
                        <a href="#fitur" class="lp-btn lp-btn-secondary">Lihat Fitur</a>
                    </div>

                    <p class="lp-hero-note mt-7 max-w-md" data-reveal style="--reveal-delay: 210ms">
                        <x-icon name="lock-closed" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-[color:var(--lp-accent)]" />
                        Akun demo untuk Admin, Guru, dan Siswa tersedia di halaman masuk.
                    </p>
                </div>

                <div class="lg:col-span-6 lg:pl-2">
                    <figure data-reveal style="--reveal-delay: 140ms" aria-label="Pratinjau lembar absensi kelas X-1">
                        <div class="lp-ledger" aria-hidden="true">
                            <div class="lp-ledger-head">
                                <div class="lp-ledger-top">
                                    <div class="lp-ledger-meta">
                                        <span class="lp-label">Lembar absensi</span>
                                        <span class="lp-ledger-sep"></span>
                                        <span class="lp-label">Kamis, 26 Nov</span>
                                    </div>
                                    <span class="lp-saved">
                                        <span class="lp-saved-dot"></span>
                                        Tersimpan
                                    </span>
                                </div>

                                <div class="lp-ledger-title">
                                    <div>
                                        <p class="lp-label">Matematika · Kelas X-1</p>
                                        <h2>Daftar hadir</h2>
                                    </div>
                                    <div class="lp-ledger-count">
                                        <b>34</b>
                                        <span>Siswa</span>
                                    </div>
                                </div>
                            </div>

                            <div class="lp-table-head">
                                <span>Nama siswa</span>
                                <span>Status</span>
                            </div>

                            <div class="lp-row">
                                <div>
                                    <p class="lp-row-name">Andi Pratama</p>
                                    <p class="lp-row-nis">NIS 2024001</p>
                                </div>
                                <div class="lp-marks">
                                    <span class="lp-mark is-hadir">H</span>
                                    <span class="lp-mark">I</span>
                                    <span class="lp-mark">S</span>
                                    <span class="lp-mark">A</span>
                                </div>
                            </div>

                            <div class="lp-row">
                                <div>
                                    <p class="lp-row-name">Bella Anggraini</p>
                                    <p class="lp-row-nis">NIS 2024002</p>
                                </div>
                                <div class="lp-marks">
                                    <span class="lp-mark is-hadir">H</span>
                                    <span class="lp-mark">I</span>
                                    <span class="lp-mark">S</span>
                                    <span class="lp-mark">A</span>
                                </div>
                            </div>

                            <div class="lp-row">
                                <div>
                                    <p class="lp-row-name">Cahyo Saputra</p>
                                    <p class="lp-row-nis">NIS 2024003</p>
                                </div>
                                <div class="lp-marks">
                                    <span class="lp-mark">H</span>
                                    <span class="lp-mark is-izin">I</span>
                                    <span class="lp-mark">S</span>
                                    <span class="lp-mark">A</span>
                                </div>
                            </div>

                            <div class="lp-row">
                                <div>
                                    <p class="lp-row-name">Dinda Maharani</p>
                                    <p class="lp-row-nis">NIS 2024004</p>
                                </div>
                                <div class="lp-marks">
                                    <span class="lp-mark is-hadir">H</span>
                                    <span class="lp-mark">I</span>
                                    <span class="lp-mark">S</span>
                                    <span class="lp-mark">A</span>
                                </div>
                            </div>

                            <div class="lp-row">
                                <div>
                                    <p class="lp-row-name">Eko Nugroho</p>
                                    <p class="lp-row-nis">NIS 2024005</p>
                                </div>
                                <div class="lp-marks">
                                    <span class="lp-mark">H</span>
                                    <span class="lp-mark">I</span>
                                    <span class="lp-mark is-sakit">S</span>
                                    <span class="lp-mark">A</span>
                                </div>
                            </div>

                            <div class="lp-row">
                                <div>
                                    <p class="lp-row-name">Fitri Kusuma</p>
                                    <p class="lp-row-nis">NIS 2024006</p>
                                </div>
                                <div class="lp-marks">
                                    <span class="lp-mark">H</span>
                                    <span class="lp-mark">I</span>
                                    <span class="lp-mark">S</span>
                                    <span class="lp-mark is-alpa">A</span>
                                </div>
                            </div>

                            <div class="lp-row lp-row-more">
                                <span>28 siswa lainnya</span>
                                <span aria-hidden="true">···</span>
                            </div>

                            <div class="lp-ledger-summary">
                                <div class="lp-summary-counts">
                                    <span class="lp-summary-count"><b>30</b>Hadir</span>
                                    <span class="lp-summary-count"><b>2</b>Izin</span>
                                    <span class="lp-summary-count"><b>1</b>Sakit</span>
                                    <span class="lp-summary-count"><b>1</b>Alpa</span>
                                </div>
                                <div class="lp-summary-bar">
                                    <span style="width: 88.2%; background: #2b4739"></span>
                                    <span style="width: 5.9%; background: #c9a25a"></span>
                                    <span style="width: 2.9%; background: #557a8c"></span>
                                    <span style="width: 2.9%; background: #9c5548"></span>
                                </div>
                            </div>
                        </div>

                        <figcaption class="lp-ledger-caption">
                            <span>Pratinjau antarmuka</span>
                            <span>Data demo</span>
                        </figcaption>
                    </figure>
                </div>
            </div>

            <div class="lp-stats" data-reveal>
                <div class="lp-stats-grid">
                    <div class="lp-stat">
                        <p class="lp-stat-label">Peran pengguna</p>
                        <p class="lp-stat-value">3</p>
                        <p class="lp-stat-note">Admin, guru, siswa</p>
                    </div>
                    <div class="lp-stat">
                        <p class="lp-stat-label">Status kehadiran</p>
                        <p class="lp-stat-value">4</p>
                        <p class="lp-stat-note">Hadir, izin, sakit, alpa</p>
                    </div>
                    <div class="lp-stat">
                        <p class="lp-stat-label">Catatan per sesi</p>
                        <p class="lp-stat-value">1</p>
                        <p class="lp-stat-note">Tidak ada absensi ganda</p>
                    </div>
                    <div class="lp-stat">
                        <p class="lp-stat-label">Rekap siap ekspor</p>
                        <p class="lp-stat-value">CSV</p>
                        <p class="lp-stat-note">Sesuai filter periode</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== FITUR ===================== --}}
    <section id="fitur" class="lp-section lp-section-soft scroll-mt-24">
        <div class="lp-container">
            <div class="grid gap-12 lg:grid-cols-12 lg:gap-16">
                <div class="lg:col-span-4">
                    <div class="lg:sticky lg:top-28 lg:self-start" data-reveal>
                        <p class="lp-eyebrow">Fitur</p>
                        <h2 class="lp-h2 lp-display mt-5">Dibangun untuk alur kerja sekolah yang sebenarnya.</h2>
                        <p class="lp-lead mt-5 text-[0.95rem]">
                            Setiap fitur bekerja dengan validasi, otorisasi, dan integritas data —
                            bukan sekadar tampilan di atas formulir.
                        </p>
                        <a href="#cara-kerja" class="lp-text-link mt-7">
                            Lihat cara kerjanya
                            <x-icon name="chevron-right" class="h-4 w-4" />
                        </a>
                    </div>
                </div>

                <div class="lg:col-span-8">
                    <div class="lp-feature-grid grid gap-x-12 sm:grid-cols-2">
                        @foreach ([
                            ['title' => 'Catat satu kelas dalam hitungan detik', 'text' => 'Tandai semua hadir, lalu ubah pengecualian. Satu sesi tersimpan utuh dalam satu transaksi.'],
                            ['title' => 'Hak akses yang benar-benar berlapis', 'text' => 'Admin, guru, dan siswa melihat serta mengubah hanya yang menjadi kewenangannya. Diperiksa di server, bukan sekadar disembunyikan di antarmuka.'],
                            ['title' => 'Satu catatan per siswa, per sesi', 'text' => 'Kunci unik di database mencegah satu siswa memiliki dua status pada sesi yang sama, bahkan saat data disimpan bersamaan.'],
                            ['title' => 'Rekap dan tren tanpa hitung manual', 'text' => 'Persentase kehadiran, distribusi status, dan tren harian dihitung langsung dari data dengan filter periode yang konsisten.'],
                            ['title' => 'Koreksi terkendali dan terlacak', 'text' => 'Perbaikan sesi dibatasi jendela waktu yang wajar. Setiap perubahan tercatat di audit log: siapa, kapan, dan apa yang berubah.'],
                            ['title' => 'Laporan siap diarsipkan', 'text' => 'Filter rekap per kelas, mata pelajaran, guru, atau status, lalu ekspor CSV untuk kebutuhan administrasi sekolah.'],
                        ] as $index => $feature)
                            <article class="lp-feature" data-reveal style="--reveal-delay: {{ ($index % 2) * 80 }}ms">
                                <p class="lp-feature-num">0{{ $index + 1 }}</p>
                                <h3 class="lp-feature-title">{{ $feature['title'] }}</h3>
                                <p class="lp-feature-text">{{ $feature['text'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== CARA KERJA ===================== --}}
    <section id="cara-kerja" class="lp-section scroll-mt-24">
        <div class="lp-container">
            <div class="grid gap-8 lg:grid-cols-12 lg:items-end lg:gap-16">
                <div class="lg:col-span-8" data-reveal>
                    <p class="lp-eyebrow">Cara kerja</p>
                    <h2 class="lp-h2 lp-display mt-5">Tiga langkah. Satu catatan yang utuh.</h2>
                </div>
                <div class="lg:col-span-4 lg:text-right" data-reveal style="--reveal-delay: 80ms">
                    <p class="text-sm leading-relaxed text-[color:var(--lp-muted)]">
                        Dari data master sampai rekap akhir, alurnya tetap sederhana.
                    </p>
                </div>
            </div>

            <ol class="mt-14 grid gap-10 lg:grid-cols-3 lg:gap-12">
                @foreach ([
                    ['title' => 'Admin menyiapkan data', 'text' => 'Kelola akun, kelas, mata pelajaran, dan jadwal. Validasi mencegah jadwal bentrok untuk guru maupun kelas.'],
                    ['title' => 'Guru mengisi absensi', 'text' => 'Pilih jadwal dan tanggal, tandai status siswa, lalu simpan. Sistem menolak sesi ganda dan hanya menerima kelas yang ditugaskan.'],
                    ['title' => 'Semua memantau hasilnya', 'text' => 'Admin melihat rekap sekolah, guru melihat kelasnya, siswa melihat riwayat dan persentase kehadiran miliknya sendiri.'],
                ] as $index => $step)
                    <li class="lp-step" data-reveal style="--reveal-delay: {{ $index * 90 }}ms">
                        <p class="lp-step-num">0{{ $index + 1 }}</p>
                        <h3 class="lp-step-title">{{ $step['title'] }}</h3>
                        <p class="lp-step-text">{{ $step['text'] }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- ===================== PERAN ===================== --}}
    <section id="peran" class="lp-band lp-section scroll-mt-24">
        <div class="lp-container lp-on-dark">
            <div class="grid gap-12 lg:grid-cols-12 lg:gap-16">
                <div class="lg:col-span-4">
                    <div class="lg:sticky lg:top-28 lg:self-start" data-reveal>
                        <p class="lp-eyebrow">Peran pengguna</p>
                        <h2 class="lp-h2 lp-display mt-5 text-[color:var(--lp-band-ink)]">
                            Satu sistem, tiga pengalaman yang tepat sasaran.
                        </h2>
                        <p class="mt-5 text-sm leading-relaxed text-[color:var(--lp-band-muted)]">
                            Setiap peran masuk ke ruang kerjanya sendiri. Hak akses diperiksa di server,
                            bukan hanya disembunyikan di antarmuka.
                        </p>
                    </div>
                </div>

                <div class="lg:col-span-8">
                    @foreach ([
                        ['icon' => 'users', 'role' => 'Admin', 'email' => 'admin@simas.test', 'points' => [
                            'Kelola pengguna, guru, siswa, kelas, mapel, dan jadwal',
                            'Rekap kehadiran seluruh sekolah dengan filter periode',
                            'Ekspor laporan CSV & pantau audit log perubahan',
                        ]],
                        ['icon' => 'briefcase', 'role' => 'Guru', 'email' => 'budi.santoso@simas.test', 'points' => [
                            'Lihat hanya jadwal dan kelas yang ditugaskan',
                            'Isi absensi massal dengan validasi lengkap',
                            'Koreksi sesi dalam jendela waktu — tercatat di audit log',
                        ]],
                        ['icon' => 'academic-cap', 'role' => 'Siswa', 'email' => 'andi.pratama@siswa.simas.test', 'points' => [
                            'Jadwal kelas dan profil pribadi',
                            'Riwayat absensi dengan filter bulan & status',
                            'Persentase kehadiran — tanpa akses ke data teman',
                        ]],
                    ] as $index => $role)
                        <article class="lp-role" data-reveal style="--reveal-delay: {{ $index * 80 }}ms">
                            <div class="grid gap-6 lg:grid-cols-12 lg:gap-8">
                                <div class="lg:col-span-5">
                                    <div class="flex items-center gap-2.5">
                                        <x-icon :name="$role['icon']" class="h-4 w-4 text-[color:var(--lp-accent-soft)]" />
                                        <span class="lp-role-index">0{{ $index + 1 }}</span>
                                    </div>
                                    <h3 class="lp-role-name mt-2">{{ $role['role'] }}</h3>
                                    <p class="lp-role-email">{{ $role['email'] }}</p>
                                </div>

                                <ul class="space-y-2.5 lg:col-span-7">
                                    @foreach ($role['points'] as $point)
                                        <li class="lp-role-point">
                                            <x-icon name="check" class="h-4 w-4" />
                                            {{ $point }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </article>
                    @endforeach

                    <p class="mt-8 text-sm text-[color:var(--lp-band-muted)]" data-reveal>
                        Semua akun demo memakai kata sandi <code class="lp-kbd">password</code> — coba langsung dari halaman masuk.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== FAQ ===================== --}}
    <section id="faq" class="lp-section scroll-mt-24">
        <div class="lp-container">
            <div class="grid gap-12 lg:grid-cols-12 lg:gap-16">
                <div class="lg:col-span-4">
                    <div class="lg:sticky lg:top-28 lg:self-start" data-reveal>
                        <p class="lp-eyebrow">FAQ</p>
                        <h2 class="lp-h2 lp-display mt-5">Pertanyaan yang sering muncul.</h2>
                        <p class="mt-5 text-sm leading-relaxed text-[color:var(--lp-muted)]">
                            Jawaban singkat tentang data, keamanan, dan cara kerja absensi di SIMAS.
                        </p>
                    </div>
                </div>

                <div class="lg:col-span-8">
                    @foreach ([
                        ['q' => 'Apakah data siswa di sini nyata?', 'a' => 'Tidak. Seluruh data (nama, nomor induk, jadwal, dan absensi) adalah contoh yang dihasilkan seeder dan dibuat khusus untuk demo portofolio.'],
                        ['q' => 'Bagaimana keamanan aksesnya?', 'a' => 'Autentikasi memakai hash password dan pembatasan percobaan login. Otorisasi diperiksa di server lewat middleware, policy, dan query yang selalu dibatasi sesuai peran. Siswa tidak memiliki endpoint untuk mengubah absensi dan tidak dapat membuka data siswa lain meskipun URL dimanipulasi.'],
                        ['q' => 'Bagaimana aturan absensi gandanya?', 'a' => 'Tabel attendance_sessions memiliki kunci unik (jadwal + tanggal) dan tabel attendances memiliki kunci unik (sesi + siswa). Database menolak duplikasi, dan form hanya menampilkan siswa dari kelas yang ditugaskan.'],
                        ['q' => 'Apa yang terjadi jika guru salah mengisi?', 'a' => 'Guru dapat mengoreksi sesi dalam jendela waktu yang dikonfigurasi (7 hari terakhir). Setiap koreksi dicatat di audit log lengkap dengan perubahan statusnya.'],
                        ['q' => 'Bagaimana persentase kehadiran dihitung?', 'a' => 'Hadir dibagi jumlah seluruh catatan absensi yang tercatat dikali 100. Jika belum ada catatan sama sekali, aplikasi menampilkan "Belum ada data" alih-alih angka menyesatkan.'],
                    ] as $index => $faq)
                        <div class="lp-faq-item" x-data="{ open: {{ $index === 0 ? 'true' : 'false' }} }" data-reveal style="--reveal-delay: {{ $index * 50 }}ms">
                            <button type="button" @click="open = !open"
                                    class="lp-faq-trigger"
                                    aria-controls="faq-panel-{{ $index }}"
                                    :aria-expanded="open ? 'true' : 'false'">
                                <span class="flex min-w-0 items-baseline gap-4">
                                    <span class="lp-faq-num">0{{ $index + 1 }}</span>
                                    <span>{{ $faq['q'] }}</span>
                                </span>
                                <span class="lp-faq-icon" :class="open ? 'rotate-180' : ''">
                                    <x-icon name="chevron-down" class="h-4 w-4" />
                                </span>
                            </button>

                            <div id="faq-panel-{{ $index }}" x-show="open" x-collapse.duration.200ms>
                                <p class="lp-faq-answer">{{ $faq['a'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== CTA ===================== --}}
    <section class="lp-cta">
        <div class="lp-container">
            <div class="grid gap-10 py-16 lg:grid-cols-12 lg:items-center lg:py-20">
                <div class="lg:col-span-7" data-reveal>
                    <p class="lp-eyebrow">Mulai</p>
                    <h2 class="lp-h2 lp-display mt-5 max-w-xl">Siap mencoba SIMAS sekarang?</h2>
                    <p class="mt-4 max-w-xl text-sm leading-relaxed text-[color:var(--lp-muted)]">
                        Masuk dengan akun demo sebagai Admin, Guru, atau Siswa dan rasakan alur lengkapnya —
                        dari data master sampai rekap absensi.
                    </p>
                </div>

                <div class="lg:col-span-5 lg:justify-self-end" data-reveal style="--reveal-delay: 80ms">
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('login') }}" class="lp-btn lp-btn-primary">
                            Masuk dengan Akun Demo
                            <x-icon name="arrow-right-on-rectangle" class="lp-arrow h-4 w-4" />
                        </a>
                        <a href="#fitur" class="lp-btn lp-btn-secondary">Pelajari Fitur</a>
                    </div>
                    <p class="mt-4 text-xs text-[color:var(--lp-faint)]">
                        Tidak perlu registrasi. Seluruh data adalah contoh.
                    </p>
                </div>
            </div>
        </div>
    </section>
</x-layouts.landing>
