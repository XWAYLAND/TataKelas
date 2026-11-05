<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="TataKelas membantu murid mengatur workspace belajar pribadi, mengakses materi kelas, mengumpulkan tugas, dan memantau hasil belajar.">
    <title>{{ config('app.name') }} - Workspace Murid &amp; Sistem Pembelajaran</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daisyui@5/daisyui.css">
        <script>
            tailwind.config = { darkMode: 'class' }
        </script>
    @endif

    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script>
        (function () {
            var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            var isDark = localStorage.theme === 'dark' || (!('theme' in localStorage) && prefersDark);
            document.documentElement.classList.toggle('dark', isDark);
            document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
        })();
    </script>
</head>
<body class="min-h-screen bg-base-200 text-base-content">
    <header class="border-b border-base-300/70 bg-base-100/90">
        <nav class="navbar mx-auto max-w-7xl px-4 sm:px-6 lg:px-8" aria-label="Main navigation">
            <a href="{{ url('/') }}" class="navbar-start flex items-center gap-3" aria-label="Beranda TataKelas">
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-primary text-primary-content">
                    <i class="fas fa-folder-open"></i>
                </span>
                <span class="text-xl font-bold tracking-tight">{{ config('app.name') }}</span>
            </a>

            <div class="navbar-end gap-2">
                <button type="button" class="btn btn-ghost btn-circle" data-theme-toggle title="Toggle dark mode" aria-label="Toggle dark mode">
                    <i class="fas fa-moon text-lg dark:hidden"></i>
                    <i class="fas fa-sun hidden text-lg dark:inline"></i>
                </button>
                <a href="{{ route('login') }}" class="btn btn-ghost btn-sm sm:btn-md">Masuk</a>
                <a href="{{ route('register') }}" class="btn btn-primary btn-sm sm:btn-md">Mulai</a>
            </div>
        </nav>
    </header>

    <main>
        <section class="relative isolate overflow-hidden">
            <div class="pointer-events-none absolute -right-32 -top-32 -z-10 h-96 w-96 rounded-full bg-primary/10 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-40 -left-32 -z-10 h-96 w-96 rounded-full bg-secondary/10 blur-3xl"></div>

            <div class="mx-auto grid max-w-7xl items-center gap-14 px-4 py-16 sm:px-6 sm:py-24 lg:grid-cols-2 lg:px-8 lg:py-28">
                <div>
                    <p class="inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/5 px-3 py-1.5 text-xs font-semibold text-primary">
                        <i class="fas fa-sparkles"></i>
                        Workspace pribadi + pembelajaran kelas
                    </p>
                    <h1 class="mt-6 max-w-xl text-4xl font-bold leading-tight tracking-tight sm:text-5xl lg:text-6xl">
                        Workspace murid dan sistem belajar <span class="text-primary">dalam satu tempat.</span>
                    </h1>
                    <p class="mt-6 max-w-lg text-lg leading-relaxed text-base-content/65">
                        Atur folder, tautan, dan catatan belajar secara pribadi. Ikuti classroom, pelajari materi, kumpulkan tugas, dan lihat penilaian.
                    </p>
                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('register') }}" class="btn btn-primary btn-lg">
                            Buat workspace belajar
                            <i class="fas fa-arrow-right"></i>
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-outline btn-lg">Masuk</a>
                    </div>
                    <p class="mt-4 text-sm text-base-content/50">
                        <i class="fas fa-lock mr-1"></i>
                        Workspace pribadi hanya dapat dilihat oleh pemiliknya.
                    </p>
                </div>

                <div class="mx-auto w-full max-w-xl">
                    <div class="app-surface overflow-hidden p-4 shadow-xl sm:p-6">
                        <div class="flex items-center justify-between border-b border-base-300 pb-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-base-content/45">Workspace murid</p>
                                <h2 class="mt-1 text-lg font-bold">Ruang belajar pribadi</h2>
                            </div>
                            <span class="grid h-10 w-10 place-items-center rounded-xl bg-primary/10 text-primary">
                                <i class="fas fa-layer-group"></i>
                            </span>
                        </div>

                        <div class="mt-5 space-y-3">
                            <div class="rounded-xl border border-base-300 bg-base-100 p-4">
                                <div class="flex items-start gap-3">
                                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-info/10 text-info">
                                        <i class="fas fa-bookmark"></i>
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <h3 class="font-semibold">Materi belajar</h3>
                                            <span class="badge badge-success badge-sm">Dipelajari</span>
                                        </div>
                                        <p class="mt-1 text-sm text-base-content/55">Tautan, catatan, dan referensi tersimpan sesuai topik.</p>
                                        <div class="mt-3 flex items-center gap-2 text-xs text-base-content/45">
                                            <i class="fas fa-folder text-primary"></i>
                                            Workspace pribadi
                                            <span aria-hidden="true">&middot;</span>
                                            <i class="fas fa-sticky-note"></i>
                                            2 notes
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <div class="rounded-xl border border-base-300 bg-base-100 p-4">
                                    <span class="grid h-9 w-9 place-items-center rounded-lg bg-secondary/10 text-secondary">
                                        <i class="fas fa-sticky-note"></i>
                                    </span>
                                    <p class="mt-3 text-sm font-semibold">Catatan belajar</p>
                                    <p class="mt-1 text-xs leading-relaxed text-base-content/55">Simpan konteks bersama referensi yang digunakan.</p>
                                </div>
                                <div class="rounded-xl border border-base-300 bg-base-100 p-4">
                                    <span class="grid h-9 w-9 place-items-center rounded-lg bg-accent/10 text-accent">
                                        <i class="fas fa-images"></i>
                                    </span>
                                    <p class="mt-3 text-sm font-semibold">Dokumentasi dan berkas</p>
                                    <p class="mt-1 text-xs leading-relaxed text-base-content/55">Bahan belajar tersusun di workspace milikmu.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="mt-4 text-center text-xs text-base-content/40">Workspace pribadi yang terhubung dengan pembelajaran kelas.</p>
                </div>
            </div>
        </section>

        <section class="border-t border-base-300/70 bg-base-100">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
                <div class="mx-auto max-w-2xl text-center">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-primary">Workspace murid + learning system</p>
                    <h2 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">Atur belajar pribadi, ikuti pembelajaran kelas.</h2>
                    <p class="mt-4 leading-relaxed text-base-content/60">TataKelas menyatukan bahan belajar milikmu dengan materi, tugas, pengumpulan, dan penilaian dari classroom.</p>
                </div>

                <div class="mt-10 grid gap-4 md:grid-cols-3">
                    <article class="app-surface p-6">
                        <span class="grid h-11 w-11 place-items-center rounded-xl bg-primary/10 text-primary">
                            <i class="fas fa-folder-tree"></i>
                        </span>
                        <h3 class="mt-5 text-lg font-bold">Workspace belajar pribadi</h3>
                        <p class="mt-2 text-sm leading-relaxed text-base-content/60">Atur folder, tautan, catatan, dan foto untuk kebutuhan belajarmu sendiri.</p>
                    </article>
                    <article class="app-surface p-6">
                        <span class="grid h-11 w-11 place-items-center rounded-xl bg-secondary/10 text-secondary">
                            <i class="fas fa-pen-to-square"></i>
                        </span>
                        <h3 class="mt-5 text-lg font-bold">Materi dan tugas classroom</h3>
                        <p class="mt-2 text-sm leading-relaxed text-base-content/60">Guru membagikan materi dan tugas; murid bergabung menggunakan kode kelas.</p>
                    </article>
                    <article class="app-surface p-6">
                        <span class="grid h-11 w-11 place-items-center rounded-xl bg-accent/10 text-accent">
                            <i class="fas fa-shield-halved"></i>
                        </span>
                        <h3 class="mt-5 text-lg font-bold">Pengumpulan dan penilaian</h3>
                        <p class="mt-2 text-sm leading-relaxed text-base-content/60">Murid mengumpulkan tugas dan menerima nilai serta feedback dari pengajar.</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="rounded-3xl bg-neutral px-6 py-12 text-center text-neutral-content sm:px-12">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-primary">Mulai tata kegiatan belajarmu</p>
                <h2 class="mx-auto mt-3 max-w-2xl text-3xl font-bold tracking-tight sm:text-4xl">Satu workspace pribadi, satu sistem pembelajaran kelas.</h2>
                <p class="mx-auto mt-4 max-w-xl text-sm leading-relaxed text-neutral-content/65">Simpan bahan belajar dengan privat, lalu akses materi dan tugas kelas dari TataKelas.</p>
                <a href="{{ route('register') }}" class="btn btn-primary mt-7">
                    Buat akun murid
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </section>
    </main>

    <footer class="border-t border-base-300/70 bg-base-100">
        <div class="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-6 text-sm text-base-content/50 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
            <a href="{{ url('/') }}" class="flex items-center gap-2 font-semibold text-base-content/70">
                <i class="fas fa-folder-open text-primary"></i>
                {{ config('app.name') }}
            </a>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Workspace murid dan sistem pembelajaran kelas.</p>
        </div>
    </footer>

    <script>
        document.querySelectorAll('[data-theme-toggle]').forEach(function (button) {
            button.addEventListener('click', function () {
                var isDark = !document.documentElement.classList.contains('dark');
                document.documentElement.classList.toggle('dark', isDark);
                document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
                localStorage.theme = isDark ? 'dark' : 'light';
            });
        });
    </script>
</body>
</html>
