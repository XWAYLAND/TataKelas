<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', config('app.name')) - Workspace Murid &amp; Sistem Pembelajaran</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css'])
    @else
        <!-- CDN fallback (run "npm run build" for the full production bundle) -->
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daisyui@5/daisyui.css">
        <script>
            tailwind.config = { darkMode: 'class' }
        </script>
    @endif

    <!-- Font Awesome -->
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Apply the saved theme before paint to avoid a flash of the wrong mode. -->
    <script>
        (function () {
            var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            var isDark = localStorage.theme === 'dark' || (!('theme' in localStorage) && prefersDark);
            document.documentElement.classList.toggle('dark', isDark);
            document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
        })();
    </script>

    <style>
        [x-cloak] { display: none !important; }
        body { transition: background-color 0.25s ease, color 0.25s ease; }
    </style>

    @stack('styles')
</head>
<body class="min-h-screen bg-base-200 text-base-content">
    <div class="drawer lg:drawer-open">
        <input id="app-drawer" type="checkbox" class="drawer-toggle" />

        <div class="drawer-content flex min-h-screen flex-col">
            <!-- Top bar -->
            <header class="sticky top-0 z-40 border-b border-base-300/80 bg-base-100/90 backdrop-blur">
                <div class="navbar min-h-16 gap-2 px-4 sm:px-6 lg:px-8">
                    <div class="navbar-start gap-2">
                        <label for="app-drawer" class="btn btn-ghost btn-square lg:hidden" aria-label="Open navigation">
                            <i class="fas fa-bars text-lg"></i>
                        </label>

                        <a href="{{ route(auth()->user()->role === 'admin' ? 'admin.index' : 'folders.index') }}" class="flex items-center gap-2 lg:hidden">
                            <span class="grid h-9 w-9 place-items-center rounded-xl bg-primary text-primary-content">
                                <i class="fas fa-folder-open"></i>
                            </span>
                            <span class="text-lg font-bold tracking-tight">{{ config('app.name') }}</span>
                        </a>

                        <div class="hidden lg:block">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-base-content/45">{{ auth()->user()->role === 'admin' ? 'Administration' : (auth()->user()->role === 'teacher' ? 'Teaching space' : 'Learning space') }}</p>
                            <h2 class="text-base font-semibold leading-tight">@yield('title', 'Dashboard')</h2>
                        </div>
                    </div>

                    <div class="navbar-end gap-1 sm:gap-2">
                        @if(auth()->user()->role !== 'admin')
                            <a href="{{ route('resources.create') }}" class="btn btn-primary btn-sm hidden sm:inline-flex">
                                <i class="fas fa-plus"></i>
                                <span class="hidden md:inline">Add Resource</span>
                            </a>
                        @endif

                        <div class="dropdown dropdown-end">
                            <div tabindex="0" role="button" class="btn btn-ghost h-auto gap-2 px-2 normal-case">
                                <div class="avatar placeholder">
                                    <div class="w-9 rounded-full bg-primary/15 text-primary">
                                        <span class="text-sm font-bold">{{ Str::upper(Str::substr(auth()->user()->name, 0, 1)) }}</span>
                                    </div>
                                </div>
                                <div class="hidden text-left md:block">
                                    <p class="max-w-32 truncate text-sm font-semibold leading-tight">{{ auth()->user()->name }}</p>
                                    <p class="max-w-32 truncate text-[11px] font-normal text-base-content/50">{{ auth()->user()->email }}</p>
                                </div>
                                <i class="fas fa-chevron-down hidden text-[10px] text-base-content/40 md:inline"></i>
                            </div>
                            <ul tabindex="0" class="menu dropdown-content z-50 mt-2 w-60 rounded-box border border-base-300 bg-base-100 p-2 shadow-xl">
                                <li class="menu-title">
                                    <span class="text-xs">Signed in as</span>
                                </li>
                                <li class="mb-1 px-3 pb-2">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-base-content">{{ auth()->user()->name }}</p>
                                        <p class="truncate text-xs text-base-content/50">{{ auth()->user()->email }}</p>
                                    </div>
                                </li>
                                @if(auth()->user()->role === 'admin')
                                    <li><a href="{{ route('admin.index') }}"><i class="fas fa-users-gear w-4"></i> Kelola pengguna</a></li>
                                    <li><a href="{{ route('admin.audit-logs.index') }}"><i class="fas fa-clock-rotate-left w-4"></i> Audit log</a></li>
                                @else
                                    <li><a href="{{ route('folders.index') }}"><i class="fas fa-gauge-high w-4"></i> Dashboard</a></li>
                                    <li><a href="{{ route('resources.create') }}"><i class="fas fa-bookmark w-4"></i> Add resource</a></li>
                                @endif
                                <li><a href="{{ route('account.password.edit') }}"><i class="fas fa-key w-4"></i> Ganti kata sandi</a></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="text-error">
                                            <i class="fas fa-arrow-right-from-bracket w-4"></i> Sign out
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page content -->
            <main class="flex-1">
                @include('partials.flash-messages')

                <div class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
                    @yield('content')
                </div>
            </main>

            <footer class="border-t border-base-300/80 bg-base-100">
                <div class="mx-auto flex w-full max-w-7xl flex-col gap-2 px-4 py-5 text-xs text-base-content/50 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                    <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Workspace murid dan sistem pembelajaran kelas.</p>
                    <p class="flex items-center gap-1.5">
                        <i class="fas fa-lock text-[10px]"></i>
                        Workspace pribadi hanya untuk pemilik; materi kelas hanya untuk anggota classroom.
                    </p>
                </div>
            </footer>
        </div>

        <!-- Sidebar -->
        <div class="drawer-side z-50">
            <label for="app-drawer" aria-label="Close navigation" class="drawer-overlay"></label>

            <aside class="flex min-h-full w-72 flex-col border-r border-base-300/80 bg-base-100">
                <div class="flex h-16 items-center border-b border-base-300/70 px-5">
                    <a href="{{ route(auth()->user()->role === 'admin' ? 'admin.index' : 'folders.index') }}" class="flex items-center gap-3">
                        <span class="grid h-10 w-10 place-items-center rounded-xl bg-primary text-lg text-primary-content shadow-sm">
                            <i class="fas fa-folder-open"></i>
                        </span>
                        <span>
                            <span class="block text-lg font-bold leading-tight tracking-tight">{{ config('app.name') }}</span>
                            <span class="block text-[10px] font-semibold uppercase tracking-[0.16em] text-base-content/40">Workspace + LMS</span>
                        </span>
                    </a>
                </div>

                <div class="flex-1 overflow-y-auto px-3 py-5">
                    @if(auth()->user()->role !== 'admin')
                        <p class="px-3 pb-2 text-[10px] font-bold uppercase tracking-[0.18em] text-base-content/40">My workspace</p>
                        <ul class="menu w-full gap-0.5 p-0">
                            <li>
                                <a href="{{ route('folders.index') }}" class="{{ request()->routeIs('folders.*') ? 'bg-primary/10 font-semibold text-primary' : '' }}">
                                    <i class="fas fa-gauge-high w-4 text-center"></i> Dashboard
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('resources.index') }}" class="{{ request()->routeIs('resources.*') ? 'bg-primary/10 font-semibold text-primary' : '' }}">
                                    <i class="fas fa-bookmark w-4 text-center"></i> All Resources
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('notes.index') }}" class="{{ request()->routeIs('notes.*') ? 'bg-primary/10 font-semibold text-primary' : '' }}">
                                    <i class="fas fa-sticky-note w-4 text-center"></i> All Notes
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('photos.index') }}" class="{{ request()->routeIs('photos.*') ? 'bg-primary/10 font-semibold text-primary' : '' }}">
                                    <i class="fas fa-image w-4 text-center"></i> All Photos
                                </a>
                            </li>
                        </ul>
                    @endif

                    @if(auth()->user()->role === 'admin')
                        <div class="my-5 border-t border-base-300/70"></div>
                        <p class="px-3 pb-2 text-[10px] font-bold uppercase tracking-[0.18em] text-base-content/40">Administration</p>
                        <ul class="menu w-full gap-0.5 p-0">
                            <li>
                                <a href="{{ route('admin.index') }}" class="{{ request()->routeIs('admin.index', 'admin.users.*') ? 'bg-primary/10 font-semibold text-primary' : '' }}">
                                    <i class="fas fa-users-gear w-4 text-center"></i> Kelola pengguna
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.audit-logs.index') }}" class="{{ request()->routeIs('admin.audit-logs.*') ? 'bg-primary/10 font-semibold text-primary' : '' }}">
                                    <i class="fas fa-clock-rotate-left w-4 text-center"></i> Audit log
                                </a>
                            </li>
                        </ul>
                    @endif

                    @if(in_array(auth()->user()->role, ['teacher', 'admin', 'student'], true))
                        <div class="my-5 border-t border-base-300/70"></div>
                        <p class="px-3 pb-2 text-[10px] font-bold uppercase tracking-[0.18em] text-base-content/40">Learning</p>
                        <ul class="menu w-full gap-0.5 p-0">
                            <li>
                                <a href="{{ route('classrooms.index') }}" class="{{ request()->routeIs('classrooms.*') ? 'bg-primary/10 font-semibold text-primary' : '' }}">
                                    <i class="fas fa-chalkboard w-4 text-center"></i>
                                    {{ auth()->user()->role === 'teacher' ? 'My classrooms' : 'My classes' }}
                                </a>
                            </li>
                        </ul>
                    @endif

                    @if(auth()->user()->role !== 'admin')
                        <div class="my-5 border-t border-base-300/70"></div>

                        <p class="px-3 pb-2 text-[10px] font-bold uppercase tracking-[0.18em] text-base-content/40">Personal library</p>
                        <ul class="menu w-full gap-0.5 p-0">
                            <li><a href="{{ route('folders.create') }}"><i class="fas fa-folder-plus w-4 text-center text-primary"></i> New folder</a></li>
                            <li><a href="{{ route('resources.create') }}"><i class="fas fa-link w-4 text-center text-secondary"></i> Add resource</a></li>
                            <li><a href="{{ route('notes.create') }}"><i class="fas fa-pen-to-square w-4 text-center text-warning"></i> Add note</a></li>
                            <li><a href="{{ route('photos.create') }}"><i class="fas fa-cloud-arrow-up w-4 text-center text-accent"></i> Upload photo</a></li>
                        </ul>
                    @endif
                </div>

                <div class="border-t border-base-300/70 p-3">
                    <div class="rounded-xl bg-base-200/80 p-3">
                        <div class="flex items-center gap-3">
                            <div class="avatar placeholder">
                                <div class="w-10 rounded-full bg-primary/15 text-primary">
                                    <span class="text-sm font-bold">{{ Str::upper(Str::substr(auth()->user()->name, 0, 1)) }}</span>
                                </div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold">{{ auth()->user()->name }}</p>
                                <p class="truncate text-xs text-base-content/50">{{ auth()->user()->email }}</p>
                            </div>
                        </div>
                        <div class="mt-3 flex items-center gap-2">
                            <button type="button" class="btn btn-ghost btn-xs flex-1 justify-start" data-theme-toggle>
                                <i class="fas fa-circle-half-stroke"></i> Theme
                            </button>
                            <form method="POST" action="{{ route('logout') }}" class="flex-1">
                                @csrf
                                <button type="submit" class="btn btn-ghost btn-xs w-full justify-start text-error">
                                    <i class="fas fa-arrow-right-from-bracket"></i> Sign out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    <script>
        function applyTheme(isDark) {
            document.documentElement.classList.toggle('dark', isDark);
            document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
            localStorage.theme = isDark ? 'dark' : 'light';
        }

        document.querySelectorAll('[data-theme-toggle]').forEach(function (button) {
            button.addEventListener('click', function () {
                applyTheme(!document.documentElement.classList.contains('dark'));
            });
        });

        // Close the mobile drawer after navigating.
        document.querySelectorAll('#app-drawer ~ .drawer-side a').forEach(function (link) {
            link.addEventListener('click', function () {
                const drawer = document.getElementById('app-drawer');
                if (drawer) drawer.checked = false;
            });
        });

        // Auto-hide flash messages after 5 seconds.
        setTimeout(function () {
            document.querySelectorAll('.alert-auto-dismiss').forEach(function (alert) {
                alert.style.transition = 'opacity 0.35s ease, transform 0.35s ease';
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-4px)';
                setTimeout(function () { alert.remove(); }, 400);
            });
        }, 5000);
    </script>

    @stack('scripts')
</body>
</html>
