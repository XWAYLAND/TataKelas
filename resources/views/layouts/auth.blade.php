<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Masuk') - {{ config('app.name') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
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
    <div class="grid min-h-screen lg:grid-cols-2">
        <!-- Brand panel -->
        <aside class="relative hidden overflow-hidden bg-neutral p-10 text-neutral-content lg:flex lg:flex-col lg:justify-between">
            <div class="absolute -right-24 -top-24 h-80 w-80 rounded-full bg-primary/30 blur-3xl"></div>
            <div class="absolute -bottom-32 -left-20 h-96 w-96 rounded-full bg-secondary/20 blur-3xl"></div>

            <div class="relative">
                <a href="{{ route('folders.index') }}" class="inline-flex items-center gap-3">
                    <span class="grid h-11 w-11 place-items-center rounded-xl bg-primary text-lg text-primary-content shadow-lg">
                        <i class="fas fa-folder-open"></i>
                    </span>
                    <span>
                        <span class="block text-xl font-bold tracking-tight">{{ config('app.name') }}</span>
                        <span class="block text-[10px] font-semibold uppercase tracking-[0.18em] text-neutral-content/50">Workspace + LMS</span>
                    </span>
                </a>
            </div>

            <div class="relative max-w-md">
                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-primary">Workspace murid + classroom</p>
                <h1 class="mt-3 text-4xl font-bold leading-tight tracking-tight">Ruang belajar pribadi, terhubung dengan pembelajaran kelas.</h1>
                <p class="mt-4 text-sm leading-relaxed text-neutral-content/65">
                    Simpan referensi dan catatan untuk diri sendiri, lalu akses materi, tugas, dan penilaian dari classroom.
                </p>

                <ul class="mt-8 space-y-3 text-sm text-neutral-content/75">
                    <li class="flex items-center gap-3">
                        <span class="grid h-8 w-8 place-items-center rounded-lg bg-white/10"><i class="fas fa-bookmark text-xs"></i></span>
                        Atur folder dan referensi belajar pribadi
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="grid h-8 w-8 place-items-center rounded-lg bg-white/10"><i class="fas fa-sticky-note text-xs"></i></span>
                        Akses materi dan tugas classroom
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="grid h-8 w-8 place-items-center rounded-lg bg-white/10"><i class="fas fa-image text-xs"></i></span>
                        Kumpulkan tugas dan pantau feedback
                    </li>
                </ul>
            </div>

            <p class="relative text-xs text-neutral-content/40">&copy; {{ date('Y') }} {{ config('app.name') }}. Workspace murid dan sistem pembelajaran.</p>
        </aside>

        <!-- Form panel -->
        <main class="relative flex min-h-screen items-center justify-center px-4 py-10 sm:px-8">
            <button type="button" class="btn btn-ghost btn-circle absolute right-4 top-4" data-theme-toggle title="Toggle dark mode" aria-label="Toggle dark mode">
                <i class="fas fa-moon text-lg dark:hidden"></i>
                <i class="fas fa-sun hidden text-lg dark:inline"></i>
            </button>

            <div class="w-full max-w-md">
                <a href="{{ route('folders.index') }}" class="mb-8 flex items-center justify-center gap-2 lg:hidden">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-primary text-primary-content">
                        <i class="fas fa-folder-open"></i>
                    </span>
                    <span class="text-xl font-bold tracking-tight">{{ config('app.name') }}</span>
                </a>

                @yield('content')

                <p class="mt-8 text-center text-xs text-base-content/40">
                    Workspace pribadi hanya untuk pemilik; materi kelas hanya untuk anggota classroom.
                </p>
            </div>
        </main>
    </div>

    <script>
        document.querySelectorAll('[data-theme-toggle]').forEach(function (button) {
            button.addEventListener('click', function () {
                var isDark = !document.documentElement.classList.contains('dark');
                document.documentElement.classList.toggle('dark', isDark);
                document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
                localStorage.theme = isDark ? 'dark' : 'light';
            });
        });

        document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
            var input = document.getElementById(button.dataset.passwordToggle);
            if (!input) return;

            var icon = button.querySelector('i');

            button.addEventListener('click', function () {
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                button.setAttribute('aria-pressed', show ? 'true' : 'false');
                button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
                button.setAttribute('title', show ? 'Hide password' : 'Show password');
                if (icon) icon.className = show ? 'fas fa-eye-slash text-xs' : 'fas fa-eye text-xs';
                input.focus();
            });
        });
    </script>
</body>
</html>
