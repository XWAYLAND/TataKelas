@extends('layouts.app')

@section('title', 'Dashboard admin')

@section('content')
    <div class="space-y-6">
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-primary">Ringkasan</p>
                <h1 class="mt-1 text-3xl font-bold tracking-tight">Dashboard admin</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-base-content/60">
                    Pemantauan aktivitas pengguna, classroom, dan pengelolaan akun di TataKelas.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.users.index') }}" class="btn btn-ghost border-base-300">
                    <i class="fas fa-users-gear"></i>
                    Kelola pengguna
                </a>
                <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-ghost border-base-300">
                    <i class="fas fa-clock-rotate-left"></i>
                    Audit log
                </a>
            </div>
        </header>

        <dl class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-base-300/80 bg-base-100 p-4">
                <dt class="text-xs text-base-content/55">Total akun</dt>
                <dd class="mt-1 text-2xl font-semibold tabular-nums">{{ $stats['users_by_role']->sum() }}</dd>
                <p class="mt-1 text-xs text-base-content/45">{{ $stats['new_users_30d'] }} baru dalam 30 hari</p>
            </div>
            <div class="rounded-2xl border border-base-300/80 bg-base-100 p-4">
                <dt class="text-xs text-base-content/55">Classroom aktif</dt>
                <dd class="mt-1 text-2xl font-semibold tabular-nums">{{ $stats['classrooms'] }}</dd>
                <p class="mt-1 text-xs text-base-content/45">{{ $stats['assignments'] }} assignment terpasang</p>
            </div>
            <div class="rounded-2xl border border-base-300/80 bg-base-100 p-4">
                <dt class="text-xs text-base-content/55">Resource</dt>
                <dd class="mt-1 text-2xl font-semibold tabular-nums">{{ $stats['resources'] }}</dd>
            </div>
            <div class="rounded-2xl border border-base-300/80 bg-base-100 p-4">
                <dt class="text-xs text-base-content/55">Submission belum dinilai</dt>
                <dd class="mt-1 text-2xl font-semibold tabular-nums">{{ $pendingSubmissions }}</dd>
            </div>
        </dl>

        <div class="grid gap-4 lg:grid-cols-2">
            <section class="rounded-2xl border border-base-300/80 bg-base-100 p-4">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-base-content/55">Distribusi akun</h2>
                <div class="mt-3 space-y-2">
                    @foreach(['admin' => 'Admin', 'teacher' => 'Teacher', 'student' => 'Student'] as $role => $label)
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span class="text-base-content/70">{{ $label }}</span>
                            <span class="tabular-nums font-medium">{{ $stats['users_by_role']->get($role, 0) }}</span>
                        </div>
                    @endforeach
                    <div class="border-t border-base-300/70 pt-2"></div>
                    @foreach(['active' => 'Aktif', 'suspended' => 'Ditangguhkan'] as $status => $label)
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span class="text-base-content/70">{{ $label }}</span>
                            <span class="tabular-nums font-medium">{{ $stats['users_by_status']->get($status, 0) }}</span>
                        </div>
                    @endforeach
                    <div class="flex items-center justify-between gap-3 text-sm">
                        <span class="text-base-content/70">Email belum terverifikasi</span>
                        <span class="tabular-nums font-medium">{{ $stats['unverified_users'] }}</span>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-base-300/80 bg-base-100 p-4">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-base-content/55">Aktivitas admin terbaru</h2>
                @if($recentAuditLogs->isEmpty())
                    <p class="mt-3 text-sm text-base-content/55">Belum ada aktivitas admin yang tercatat.</p>
                @else
                    <ul class="mt-3 divide-y divide-base-300/70">
                        @foreach($recentAuditLogs as $log)
                            <li class="py-2 text-sm">
                                <p class="font-medium">{{ $log->action_label }}</p>
                                <p class="mt-0.5 text-xs text-base-content/55">{{ $log->created_at->format('d M Y, H:i') }} · {{ $log->actor_email }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>

        <section class="rounded-2xl border border-base-300/80 bg-base-100 p-4">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-base-content/55">Pengguna terbaru</h2>
            @if($recentUsers->isEmpty())
                <p class="mt-3 text-sm text-base-content/55">Belum ada akun terdaftar.</p>
            @else
                <ul class="mt-3 divide-y divide-base-300/70">
                    @foreach($recentUsers as $user)
                        <li class="flex items-center justify-between gap-3 py-2 text-sm">
                            <div class="min-w-0">
                                <a href="{{ route('admin.users.show', $user) }}" class="font-medium hover:text-primary">{{ $user->name }}</a>
                                <p class="mt-0.5 truncate text-xs text-base-content/55">{{ $user->email }}</p>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <span class="badge badge-ghost badge-sm">{{ ucfirst($user->role) }}</span>
                                <span class="badge border-0 {{ $user->status_badge_color }}">{{ $user->status_label }}</span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
@endsection
