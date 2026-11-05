@extends('layouts.app')

@section('title', 'Kelola pengguna')

@section('content')
    <div class="space-y-6">
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Kelola pengguna</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-base-content/60">
                    Atur role dan password akun. Penghapusan hanya tersedia untuk akun tanpa data terkait.
                </p>
            </div>
            <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-ghost border-base-300">
                <i class="fas fa-clock-rotate-left"></i>
                Lihat audit log
            </a>
        </header>

        @if(session()->has('temporary_password'))
            <section role="status" class="alert alert-warning items-start border border-warning/30">
                <i class="fas fa-key mt-1"></i>
                <div class="min-w-0 flex-1">
                    <p class="font-semibold">Password sementara — tampilkan ke pengguna dengan aman</p>
                    <p class="mt-1 text-sm">Nilai ini hanya ditampilkan sekali dan tidak disimpan di audit log.</p>
                    <code class="mt-3 block select-all break-all rounded-lg bg-base-100 px-3 py-2 text-sm">{{ session('temporary_password') }}</code>
                </div>
            </section>
        @endif

        <dl class="flex flex-wrap divide-x divide-base-300 rounded-2xl border border-base-300/80 bg-base-100">
            <div class="min-w-32 flex-1 px-4 py-3 sm:px-5">
                <dt class="text-xs text-base-content/55">Semua akun</dt>
                <dd class="mt-1 text-xl font-semibold tabular-nums">{{ $totalUsers }}</dd>
            </div>
            <div class="min-w-32 flex-1 px-4 py-3 sm:px-5">
                <dt class="text-xs text-base-content/55">Admin</dt>
                <dd class="mt-1 text-xl font-semibold tabular-nums">{{ $roleCounts->get('admin', 0) }}</dd>
            </div>
            <div class="min-w-32 flex-1 px-4 py-3 sm:px-5">
                <dt class="text-xs text-base-content/55">Teacher</dt>
                <dd class="mt-1 text-xl font-semibold tabular-nums">{{ $roleCounts->get('teacher', 0) }}</dd>
            </div>
            <div class="min-w-32 flex-1 px-4 py-3 sm:px-5">
                <dt class="text-xs text-base-content/55">Student</dt>
                <dd class="mt-1 text-xl font-semibold tabular-nums">{{ $roleCounts->get('student', 0) }}</dd>
            </div>
        </dl>

        <form method="GET" action="{{ route('admin.index') }}" class="app-surface grid gap-3 p-4 sm:grid-cols-[minmax(0,1fr)_12rem_auto] sm:items-end">
            <label class="form-control">
                <span class="label-text mb-2 font-medium">Cari nama atau email</span>
                <input
                    type="search"
                    name="q"
                    value="{{ $filters['q'] ?? '' }}"
                    maxlength="100"
                    class="input input-bordered w-full"
                    placeholder="Contoh: nama@sekolah.id"
                >
            </label>
            <label class="form-control">
                <span class="label-text mb-2 font-medium">Role</span>
                <select name="role" class="select select-bordered w-full">
                    <option value="">Semua role</option>
                    @foreach(['admin' => 'Admin', 'teacher' => 'Teacher', 'student' => 'Student'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['role'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary flex-1 sm:flex-none">
                    <i class="fas fa-magnifying-glass"></i> Cari
                </button>
                @if(($filters['q'] ?? '') !== '' || ($filters['role'] ?? '') !== '')
                    <a href="{{ route('admin.index') }}" class="btn btn-ghost">Reset</a>
                @endif
            </div>
        </form>

        <section class="app-surface overflow-hidden">
            <div class="flex flex-col gap-1 border-b border-base-300/70 px-4 py-4 sm:flex-row sm:items-baseline sm:justify-between sm:px-5">
                <h2 class="font-semibold">Direktori akun</h2>
                <p class="text-xs text-base-content/55">
                    {{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} akun
                </p>
            </div>

            @if($users->isEmpty())
                <div class="px-6 py-12 text-center">
                    <i class="fas fa-user-slash text-2xl text-base-content/35" aria-hidden="true"></i>
                    <h3 class="mt-3 font-semibold">Tidak ada akun yang cocok</h3>
                    <p class="mt-1 text-sm text-base-content/55">Coba kata kunci atau filter role yang berbeda.</p>
                </div>
            @else
                <div class="divide-y divide-base-300/70 xl:hidden">
                    @foreach($users as $user)
                        <article class="space-y-4 p-4">
                            <header class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="truncate font-semibold">{{ $user->name }}</h3>
                                    <p class="mt-0.5 break-all text-sm text-base-content/55">{{ $user->email }}</p>
                                    <p class="mt-1 text-xs text-base-content/45">Bergabung {{ $user->created_at->format('d M Y') }}</p>
                                </div>
                                @if($user->is(auth()->user()))
                                    <span class="badge badge-sm badge-primary shrink-0">Akun Anda</span>
                                @endif
                            </header>
                            @include('admin.users.controls', ['user' => $user, 'compact' => true, 'layout' => 'mobile'])
                        </article>
                    @endforeach
                </div>
                <div class="hidden overflow-x-auto xl:block">
                    <table class="table min-w-[880px]">
                        <thead>
                            <tr>
                                <th>Pengguna</th>
                                <th>Bergabung</th>
                                <th>Akses dan tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr>
                                    <td>
                                        <div class="font-semibold">{{ $user->name }}</div>
                                        <div class="mt-0.5 text-sm font-normal text-base-content/55">{{ $user->email }}</div>
                                        @if($user->is(auth()->user()))
                                            <span class="badge badge-sm badge-primary mt-2">Akun Anda</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap text-sm text-base-content/65">{{ $user->created_at->format('d M Y') }}</td>
                                    <td class="min-w-64">
                                        @include('admin.users.controls', ['user' => $user, 'compact' => false, 'layout' => 'desktop'])
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($users->hasPages())
                    <div class="border-t border-base-300/70 px-4 py-4 sm:px-5">
                        {{ $users->links() }}
                    </div>
                @endif
            @endif
        </section>
    </div>
@endsection
