@extends('layouts.app')

@section('title', 'Riwayat akun')

@section('content')
    <div class="space-y-6">
        <nav class="text-sm">
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 font-medium text-base-content/55 hover:text-primary">
                <i class="fas fa-arrow-left text-xs"></i> Kembali ke kelola pengguna
            </a>
        </nav>

        <header class="rounded-2xl border border-base-300/80 bg-base-100 p-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <h1 class="break-words text-2xl font-bold tracking-tight">{{ $user->name }}</h1>
                    <p class="mt-1 break-all text-sm text-base-content/55">{{ $user->email }}</p>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <span class="badge border-0 {{ $user->status_badge_color }}">{{ $user->status_label }}</span>
                        <span class="badge badge-ghost border-0">{{ ucfirst($user->role) }}</span>
                        <span class="badge badge-ghost border-0">{{ $user->email_verified_at ? 'Terverifikasi' : 'Belum terverifikasi' }}</span>
                    </div>
                    <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="text-xs text-base-content/50">Bergabung</dt>
                            <dd class="mt-0.5">{{ $user->created_at->format('d M Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-base-content/50">Login terakhir</dt>
                            <dd class="mt-0.5">{{ $user->last_login_at?->format('d M Y, H:i') ?? 'Belum pernah login' }}</dd>
                        </div>
                    </dl>
                </div>
                <div class="shrink-0">
                    @include('admin.users.controls', ['user' => $user, 'compact' => false, 'layout' => 'show'])
                </div>
            </div>
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

        <div class="grid gap-4 lg:grid-cols-2">
            <section class="rounded-2xl border border-base-300/80 bg-base-100 p-4">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-base-content/55">Mengajar sebagai owner</h2>
                @if($taughtClassrooms->isEmpty())
                    <p class="mt-3 text-sm text-base-content/55">Tidak mengajar classroom.</p>
                @else
                    <ul class="mt-3 divide-y divide-base-300/70">
                        @foreach($taughtClassrooms as $classroom)
                            <li class="flex items-center justify-between gap-3 py-2 text-sm">
                                <span class="min-w-0 truncate font-medium">{{ $classroom->name }}</span>
                                <span class="text-xs text-base-content/55">{{ $classroom->students_count }} siswa</span>
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if($coTaughtClassrooms->isNotEmpty())
                    <h3 class="mt-4 text-sm font-semibold text-base-content/55">Co-teaching</h3>
                    <ul class="mt-2 divide-y divide-base-300/70">
                        @foreach($coTaughtClassrooms as $classroom)
                            <li class="flex items-center justify-between gap-3 py-2 text-sm">
                                <span class="min-w-0 truncate font-medium">{{ $classroom->name }}</span>
                                <span class="text-xs text-base-content/55">{{ $classroom->students_count }} siswa</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section class="rounded-2xl border border-base-300/80 bg-base-100 p-4">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-base-content/55">Mengikuti classroom</h2>
                @if($joinedClassrooms->isEmpty())
                    <p class="mt-3 text-sm text-base-content/55">Tidak tergabung dalam classroom.</p>
                @else
                    <ul class="mt-3 divide-y divide-base-300/70">
                        @foreach($joinedClassrooms as $classroom)
                            <li class="flex items-center justify-between gap-3 py-2 text-sm">
                                <span class="min-w-0 truncate font-medium">{{ $classroom->name }}</span>
                                <span class="text-xs text-base-content/55">{{ $classroom->students_count }} siswa</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>

        <section class="rounded-2xl border border-base-300/80 bg-base-100 p-4">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-base-content/55">Data akun</h2>
            <dl class="mt-3 grid gap-3 sm:grid-cols-3 lg:grid-cols-5">
                @foreach($counts as $label => $value)
                    <div>
                        <dt class="text-xs text-base-content/50">{{ ucfirst($label) }}</dt>
                        <dd class="mt-0.5 text-xl font-semibold tabular-nums">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>

        <section class="rounded-2xl border border-base-300/80 bg-base-100 p-4">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-base-content/55">Riwayat audit akun ini</h2>
            @if($auditLogs->isEmpty())
                <p class="mt-3 text-sm text-base-content/55">Belum ada tindakan admin pada akun ini.</p>
            @else
                <ul class="mt-3 divide-y divide-base-300/70">
                    @foreach($auditLogs as $log)
                        <li class="py-2 text-sm">
                            <p class="font-medium">{{ $log->action_label }}</p>
                            <p class="mt-0.5 text-xs text-base-content/55">{{ $log->created_at->format('d M Y, H:i') }} · oleh {{ $log->actor_name }}</p>
                            @if($log->details)
                                <p class="mt-1 text-xs text-base-content/45">{{ collect($log->details)->map(fn ($value, $key) => rtrim($key, '_').' '.e(is_string($value) ? $value : json_encode($value)))->implode(' · ') }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
            <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-ghost btn-sm mt-4 border-base-300">
                Lihat semua riwayat
            </a>
        </section>
    </div>
@endsection
