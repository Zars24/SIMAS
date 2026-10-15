<x-layouts.landing>
    {{-- ===================== HERO ===================== --}}
    <section class="relative overflow-hidden">
        <div class="pointer-events-none absolute -right-40 -top-40 h-[32rem] w-[32rem] rounded-full bg-primary-200/40 blur-3xl dark:bg-primary-500/10" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -left-40 top-40 h-[28rem] w-[28rem] rounded-full bg-gold-200/30 blur-3xl dark:bg-gold-400/5" aria-hidden="true"></div>

        <div class="relative mx-auto grid w-full max-w-7xl items-center gap-14 px-4 pb-20 pt-14 sm:px-6 lg:grid-cols-2 lg:gap-10 lg:px-8 lg:pb-28 lg:pt-20">
            <div class="reveal">
                <p class="inline-flex items-center gap-2 rounded-full border border-primary-200 bg-white/70 px-3.5 py-1.5 text-xs font-semibold text-primary-700 dark:border-primary-700 dark:bg-primary-900/60 dark:text-primary-200">
                    <span class="h-1.5 w-1.5 rounded-full bg-gold-400" aria-hidden="true"></span>
                    Sistem Informasi Manajemen Absensi Siswa
                </p>

                <h1 class="mt-5 text-balance text-4xl font-extrabold leading-[1.1] tracking-tight text-primary-900 dark:text-white sm:text-5xl lg:text-[3.4rem]">
                    Absensi Lebih Mudah,
                    <span class="bg-gradient-to-r from-primary-500 to-gold-500 bg-clip-text text-transparent dark:from-primary-300 dark:to-gold-300">Pengelolaan Sekolah Lebih Teratur.</span>
                </h1>

                <p class="mt-5 max-w-xl text-lg leading-relaxed text-slate-600 dark:text-slate-300">
                    Catat kehadiran satu kelas dalam hitungan detik, pantau rekap dan tren kehadiran secara otomatis,
                    dan beri siswa akses aman ke jadwal serta riwayat mereka sendiri.
                </p>

                <div class="mt-8 flex flex-wrap items-center gap-3">
                    <a href="{{ route('login') }}" class="btn-primary px-6 py-3 text-base">
                        Coba Akun Demo
                        <x-icon name="arrow-right-on-rectangle" class="h-5 w-5" />
                    </a>
                    <a href="#fitur" class="btn-outline px-6 py-3 text-base">Lihat Fitur</a>
                </div>

                <dl class="mt-10 grid max-w-lg grid-cols-3 gap-4">
                    <div class="reveal">
                        <dt class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Peran</dt>
                        <dd class="mt-1 text-2xl font-bold text-primary-900 dark:text-white">3</dd>
                        <dd class="text-xs text-slate-500 dark:text-slate-400">Admin · Guru · Siswa</dd>
                    </div>
                    <div class="reveal" style="transition-delay: 80ms">
                        <dt class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Status</dt>
                        <dd class="mt-1 text-2xl font-bold text-primary-900 dark:text-white">4</dd>
                        <dd class="text-xs text-slate-500 dark:text-slate-400">Hadir · Izin · Sakit · Alpa</dd>
                    </div>
                    <div class="reveal" style="transition-delay: 160ms">
                        <dt class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Rekap</dt>
                        <dd class="mt-1 text-2xl font-bold text-primary-900 dark:text-white">CSV</dd>
                        <dd class="text-xs text-slate-500 dark:text-slate-400">Ekspor sesuai filter</dd>
                    </div>
                </dl>
            </div>

            {{-- Mockup dashboard --}}
            <div class="reveal relative" style="transition-delay: 120ms">
                <div class="absolute -left-6 top-10 z-10 hidden animate-fade-up rounded-2xl border border-primary-900/[0.08] bg-white p-3.5 shadow-pop dark:border-white/10 dark:bg-primary-900 lg:block" style="animation-delay: 500ms">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300">
                            <x-icon name="check-circle" class="h-5 w-5" />
                        </span>
                        <div>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Kehadiran bulan ini</p>
                            <p class="text-sm font-bold text-primary-900 dark:text-white">92,4%</p>
                        </div>
                    </div>
                </div>

                <div class="absolute -right-4 bottom-12 z-10 hidden animate-fade-up rounded-2xl border border-primary-900/[0.08] bg-white p-3.5 shadow-pop dark:border-white/10 dark:bg-primary-900 lg:block" style="animation-delay: 700ms">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gold-100 text-gold-600 dark:bg-gold-400/15 dark:text-gold-300">
                            <x-icon name="clipboard-document-check" class="h-5 w-5" />
                        </span>
                        <div>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Sesi hari ini</p>
                            <p class="text-sm font-bold text-primary-900 dark:text-white">12 tersimpan</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-primary-900/[0.08] bg-white p-2 shadow-pop dark:border-white/10 dark:bg-primary-900">
                    <div class="grid min-h-[24rem] grid-cols-[6.5rem_1fr] overflow-hidden rounded-2xl bg-cream dark:bg-primary-950 sm:grid-cols-[9rem_1fr]">
                        {{-- Sidebar mock --}}
                        <div class="bg-gradient-to-b from-primary-900 to-primary-950 p-3 text-white sm:p-4">
                            <div class="flex items-center gap-2">
                                <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-gold-400 text-primary-950">
                                    <x-icon name="academic-cap" class="h-3.5 w-3.5" />
                                </span>
                                <span class="text-xs font-bold">SIMAS</span>
                            </div>

                            <div class="mt-4 space-y-1.5">
                                <div class="flex items-center gap-2 rounded-lg bg-white/15 px-2 py-1.5 text-[10px] font-medium">
                                    <x-icon name="squares-2x2" class="h-3 w-3" /> Dashboard
                                </div>
                                <div class="flex items-center gap-2 px-2 py-1.5 text-[10px] text-primary-100/60">
                                    <x-icon name="academic-cap" class="h-3 w-3" /> Siswa
                                </div>
                                <div class="flex items-center gap-2 px-2 py-1.5 text-[10px] text-primary-100/60">
                                    <x-icon name="calendar-days" class="h-3 w-3" /> Jadwal
                                </div>
                                <div class="flex items-center gap-2 px-2 py-1.5 text-[10px] text-primary-100/60">
                                    <x-icon name="table-cells" class="h-3 w-3" /> Rekap
                                </div>
                            </div>
                        </div>

                        {{-- Konten mock --}}
                        <div class="p-3 sm:p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-bold text-primary-900 dark:text-white sm:text-sm">Dashboard Admin</p>
                                    <p class="text-[10px] text-slate-500 dark:text-slate-400">Ringkasan kehadiran sekolah</p>
                                </div>
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-primary-500 text-[9px] font-bold text-white">AS</span>
                            </div>

                            <div class="mt-3 grid grid-cols-3 gap-2">
                                <div class="rounded-xl bg-white p-2.5 shadow-card dark:bg-primary-900">
                                    <p class="text-[9px] text-slate-500 dark:text-slate-400">Siswa</p>
                                    <p class="text-sm font-bold text-primary-900 dark:text-white">36</p>
                                </div>
                                <div class="rounded-xl bg-white p-2.5 shadow-card dark:bg-primary-900">
                                    <p class="text-[9px] text-slate-500 dark:text-slate-400">Guru</p>
                                    <p class="text-sm font-bold text-primary-900 dark:text-white">9</p>
                                </div>
                                <div class="rounded-xl bg-white p-2.5 shadow-card dark:bg-primary-900">
                                    <p class="text-[9px] text-slate-500 dark:text-slate-400">Kelas</p>
                                    <p class="text-sm font-bold text-primary-900 dark:text-white">3</p>
                                </div>
                            </div>

                            <div class="mt-2 rounded-xl bg-white p-2.5 shadow-card dark:bg-primary-900">
                                <div class="flex items-center justify-between">
                                    <p class="text-[10px] font-semibold text-primary-900 dark:text-white">Tren kehadiran 14 hari</p>
                                    <span class="text-[9px] text-emerald-600 dark:text-emerald-400">▲ 92%</span>
                                </div>
                                <div class="mt-2 flex h-16 items-end gap-1" aria-hidden="true">
                                    @foreach ([55, 70, 62, 80, 74, 88, 66, 90, 78, 84, 70, 92, 86, 95] as $height)
                                        <div class="flex-1 rounded-t-sm {{ $loop->last ? 'bg-gold-400' : 'bg-primary-400/70 dark:bg-primary-500' }}" style="height: {{ $height }}%"></div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mt-2 rounded-xl bg-white p-2.5 shadow-card dark:bg-primary-900">
                                <p class="text-[10px] font-semibold text-primary-900 dark:text-white">Rekap hari ini</p>
                                <div class="mt-2 space-y-1.5">
                                    <div class="flex items-center justify-between text-[10px]">
                                        <span class="text-slate-500 dark:text-slate-400">X-1 · Matematika</span>
                                        <span class="badge-hadir !px-1.5 !py-0 text-[9px]">Hadir 10</span>
                                    </div>
                                    <div class="flex items-center justify-between text-[10px]">
                                        <span class="text-slate-500 dark:text-slate-400">X-2 · Bahasa Inggris</span>
                                        <span class="badge-izin !px-1.5 !py-0 text-[9px]">Izin 2</span>
                                    </div>
                                    <div class="flex items-center justify-between text-[10px]">
                                        <span class="text-slate-500 dark:text-slate-400">XI-1 · Fisika</span>
                                        <span class="badge-belum !px-1.5 !py-0 text-[9px]">Belum diisi</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== FITUR ===================== --}}
    <section id="fitur" class="scroll-mt-20 border-t border-primary-900/[0.06] bg-white py-20 dark:border-white/10 dark:bg-primary-950/40 lg:py-24">
        <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="reveal mx-auto max-w-2xl text-center">
                <p class="text-sm font-bold uppercase tracking-widest text-gold-600 dark:text-gold-400">Fitur</p>
                <h2 class="mt-3 text-balance text-3xl font-bold tracking-tight text-primary-900 dark:text-white sm:text-4xl">
                    Semua yang sekolah butuhkan untuk absensi rapi
                </h2>
                <p class="mt-4 text-slate-600 dark:text-slate-300">
                    Bukan sekadar CRUD — setiap fitur membaca dan menulis data sungguhan dengan validasi, otorisasi, dan transaksi yang benar.
                </p>
            </div>

            <div class="mt-14 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['icon' => 'bolt', 'title' => 'Catat Sekali Klik', 'text' => 'Isi status seluruh kelas dengan tombol cepat "Tandai semua hadir", lalu sesuaikan pengecualian. Semua tersimpan dalam satu transaksi.'],
                    ['icon' => 'shield-check', 'title' => 'Hak Akses Berlapis', 'text' => 'Admin, Guru, dan Siswa melihat hal yang berbeda. Otorisasi ditegakkan di server melalui middleware dan policy — bukan sekadar menyembunyikan menu.'],
                    ['icon' => 'clipboard-document-check', 'title' => 'Anti Absensi Ganda', 'text' => 'Satu siswa hanya bisa memiliki satu status per sesi. Constraint unik di database memastikan aturan ini tidak bisa dilanggar.'],
                    ['icon' => 'presentation-chart-line', 'title' => 'Rekap & Grafik Otomatis', 'text' => 'Persentase kehadiran, distribusi status, dan tren harian dihitung langsung dari database dengan filter periode yang konsisten.'],
                    ['icon' => 'clock', 'title' => 'Koreksi Terkendali', 'text' => 'Guru dapat memperbaiki sesi dalam jendela waktu yang wajar. Setiap koreksi tercatat di audit log — siapa, kapan, dan apa yang berubah.'],
                    ['icon' => 'table-cells', 'title' => 'Laporan Siap Ekspor', 'text' => 'Filter rekap per kelas, mata pelajaran, guru, atau status, lalu ekspor CSV untuk kebutuhan administrasi sekolah.'],
                ] as $feature)
                    <article class="reveal card-pad group transition duration-200 ease-out-soft hover:-translate-y-0.5 hover:shadow-card-hover">
                        <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-primary-100 text-primary-600 transition group-hover:bg-primary-500 group-hover:text-white dark:bg-primary-500/10 dark:text-primary-300 dark:group-hover:bg-primary-500 dark:group-hover:text-white">
                            <x-icon :name="$feature['icon']" class="h-6 w-6" />
                        </span>
                        <h3 class="mt-4 text-base font-semibold text-primary-900 dark:text-white">{{ $feature['title'] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ $feature['text'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===================== CARA KERJA ===================== --}}
    <section id="cara-kerja" class="scroll-mt-20 py-20 lg:py-24">
        <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="reveal mx-auto max-w-2xl text-center">
                <p class="text-sm font-bold uppercase tracking-widest text-gold-600 dark:text-gold-400">Cara Kerja</p>
                <h2 class="mt-3 text-balance text-3xl font-bold tracking-tight text-primary-900 dark:text-white sm:text-4xl">
                    Tiga langkah, absensi selesai
                </h2>
            </div>

            <ol class="mt-14 grid gap-6 lg:grid-cols-3">
                @foreach ([
                    ['step' => '01', 'icon' => 'identification', 'title' => 'Admin menyiapkan data', 'text' => 'Kelola akun, kelas, mata pelajaran, dan jadwal. Validasi mencegah jadwal bentrok untuk guru maupun kelas.'],
                    ['step' => '02', 'icon' => 'clipboard-document-check', 'title' => 'Guru mengisi absensi', 'text' => 'Pilih jadwal dan tanggal, tandai status siswa, simpan. Sistem menolak sesi ganda dan hanya menerima kelas yang ditugaskan.'],
                    ['step' => '03', 'icon' => 'presentation-chart-line', 'title' => 'Semua memantau hasilnya', 'text' => 'Admin melihat rekap sekolah, guru melihat kelasnya, siswa melihat riwayat dan persentase kehadiran miliknya sendiri.'],
                ] as $step)
                    <li class="reveal relative rounded-2xl border border-primary-900/[0.08] bg-white p-6 shadow-card dark:border-white/10 dark:bg-primary-900/40">
                        <span class="absolute right-5 top-5 text-4xl font-extrabold tracking-tight text-primary-100 dark:text-white/10" aria-hidden="true">{{ $step['step'] }}</span>
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gold-100 text-gold-700 dark:bg-gold-400/15 dark:text-gold-300">
                            <x-icon :name="$step['icon']" class="h-6 w-6" />
                        </span>
                        <h3 class="mt-4 text-lg font-semibold text-primary-900 dark:text-white">{{ $step['title'] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ $step['text'] }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- ===================== PERAN ===================== --}}
    <section id="peran" class="scroll-mt-20 border-t border-primary-900/[0.06] bg-primary-900 py-20 text-white dark:border-white/10 dark:bg-primary-900/60 lg:py-24">
        <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="reveal mx-auto max-w-2xl text-center">
                <p class="text-sm font-bold uppercase tracking-widest text-gold-400">Peran Pengguna</p>
                <h2 class="mt-3 text-balance text-3xl font-bold tracking-tight sm:text-4xl">
                    Satu aplikasi, tiga pengalaman yang tepat sasaran
                </h2>
            </div>

            <div class="mt-14 grid gap-6 lg:grid-cols-3">
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
                ] as $role)
                    <article class="reveal flex flex-col rounded-2xl border border-white/10 bg-white/5 p-6 backdrop-blur transition duration-200 ease-out-soft hover:-translate-y-0.5 hover:bg-white/10">
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gold-400 text-primary-950">
                            <x-icon :name="$role['icon']" class="h-6 w-6" />
                        </span>
                        <h3 class="mt-4 text-lg font-semibold">{{ $role['role'] }}</h3>
                        <p class="mt-0.5 font-mono text-xs text-primary-100/70">{{ $role['email'] }}</p>

                        <ul class="mt-5 space-y-2.5 text-sm text-primary-100/90">
                            @foreach ($role['points'] as $point)
                                <li class="flex items-start gap-2.5">
                                    <x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0 text-gold-400" />
                                    {{ $point }}
                                </li>
                            @endforeach
                        </ul>
                    </article>
                @endforeach
            </div>

            <p class="reveal mt-10 text-center text-sm text-primary-100/70">
                Semua akun demo memakai kata sandi <code class="kbd !border-white/20 !bg-white/10 !text-white">password</code> — coba langsung dari halaman masuk.
            </p>
        </div>
    </section>

    {{-- ===================== FAQ ===================== --}}
    <section id="faq" class="scroll-mt-20 py-20 lg:py-24">
        <div class="mx-auto w-full max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="reveal text-center">
                <p class="text-sm font-bold uppercase tracking-widest text-gold-600 dark:text-gold-400">FAQ</p>
                <h2 class="mt-3 text-balance text-3xl font-bold tracking-tight text-primary-900 dark:text-white sm:text-4xl">
                    Pertanyaan yang sering muncul
                </h2>
            </div>

            <div class="mt-12 space-y-3">
                @foreach ([
                    ['q' => 'Apakah data siswa di sini nyata?', 'a' => 'Tidak. Seluruh data (nama, nomor induk, jadwal, dan absensi) adalah contoh yang dihasilkan seeder dan dibuat khusus untuk demo portofolio.'],
                    ['q' => 'Bagaimana keamanan aksesnya?', 'a' => 'Autentikasi memakai hash password dan pembatasan percobaan login. Otorisasi diperiksa di server lewat middleware, policy, dan query yang selalu dibatasi sesuai peran. Siswa tidak memiliki endpoint untuk mengubah absensi dan tidak dapat membuka data siswa lain meskipun URL dimanipulasi.'],
                    ['q' => 'Bagaimana aturan absensi gandanya?', 'a' => 'Tabel attendance_sessions memiliki kunci unik (jadwal + tanggal) dan tabel attendances memiliki kunci unik (sesi + siswa). Database menolak duplikasi, dan form hanya menampilkan siswa dari kelas yang ditugaskan.'],
                    ['q' => 'Apa yang terjadi jika guru salah mengisi?', 'a' => 'Guru dapat mengoreksi sesi dalam jendela waktu yang dikonfigurasi (7 hari terakhir). Setiap koreksi dicatat di audit log lengkap dengan perubahan statusnya.'],
                    ['q' => 'Bagaimana persentase kehadiran dihitung?', 'a' => 'Hadir dibagi jumlah seluruh catatan absensi yang tercatat dikali 100. Jika belum ada catatan sama sekali, aplikasi menampilkan "Belum ada data" alih-alih angka menyesatkan.'],
                ] as $index => $faq)
                    <div class="reveal card overflow-hidden" x-data="{ open: {{ $index === 0 ? 'true' : 'false' }} }">
                        <button type="button" @click="open = !open"
                                class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left transition hover:bg-primary-50/70 dark:hover:bg-white/[0.03]"
                                :aria-expanded="open">
                            <span class="text-sm font-semibold text-primary-900 dark:text-white sm:text-base">{{ $faq['q'] }}</span>
                            <x-icon name="chevron-down" class="h-5 w-5 shrink-0 text-primary-400 transition duration-200" ::class="open ? 'rotate-180' : ''" />
                        </button>

                        <div x-show="open" x-collapse.duration.200ms>
                            <p class="px-5 pb-4 text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ $faq['a'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===================== CTA ===================== --}}
    <section class="pb-20 lg:pb-28">
        <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="reveal relative overflow-hidden rounded-3xl bg-gradient-to-br from-primary-700 via-primary-800 to-primary-950 px-6 py-14 text-center shadow-pop sm:px-12">
                <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-gold-400/15 blur-2xl" aria-hidden="true"></div>
                <div class="pointer-events-none absolute -bottom-24 -left-24 h-72 w-72 rounded-full bg-primary-400/10 blur-2xl" aria-hidden="true"></div>

                <h2 class="relative mx-auto max-w-2xl text-balance text-3xl font-bold tracking-tight text-white sm:text-4xl">
                    Siap mencoba SIMAS sekarang?
                </h2>
                <p class="relative mx-auto mt-4 max-w-xl text-primary-100/80">
                    Masuk dengan akun demo sebagai Admin, Guru, atau Siswa dan rasakan alur lengkapnya — dari data master sampai rekap absensi.
                </p>

                <div class="relative mt-8 flex flex-wrap items-center justify-center gap-3">
                    <a href="{{ route('login') }}" class="btn-gold px-6 py-3 text-base">
                        Masuk dengan Akun Demo
                        <x-icon name="arrow-right-on-rectangle" class="h-5 w-5" />
                    </a>
                    <a href="#fitur" class="btn px-6 py-3 text-base text-primary-100 ring-1 ring-inset ring-white/25 transition hover:bg-white/10 hover:text-white">
                        Pelajari Fitur
                    </a>
                </div>
            </div>
        </div>
    </section>
</x-layouts.landing>
