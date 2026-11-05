@extends('layouts.app')

@section('title', 'Audit log')

@section('content')
    <div class="space-y-6">
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Audit log</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-base-content/60">
                    Riwayat perubahan role, reset password, dan penghapusan akun oleh admin.
                </p>
            </div>
            <a href="{{ route('admin.index') }}" class="btn btn-ghost border-base-300">
                <i class="fas fa-users"></i>
                Kembali ke pengguna
            </a>
        </header>

        <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="app-surface grid gap-3 p-4 sm:grid-cols-[minmax(0,1fr)_15rem_auto] sm:items-end">
            <label class="form-control">
                <span class="label-text mb-2 font-medium">Cari admin atau akun</span>
                <input
                    type="search"
                    name="q"
                    value="{{ $filters['q'] ?? '' }}"
                    maxlength="100"
                    class="input input-bordered w-full"
                    placeholder="Nama atau email"
                >
            </label>
            <label class="form-control">
                <span class="label-text mb-2 font-medium">Jenis tindakan</span>
                <select name="action" class="select select-bordered w-full">
                    <option value="">Semua tindakan</option>
                    <option value="role_changed" @selected(($filters['action'] ?? '') === 'role_changed')>Perubahan role</option>
                    <option value="password_reset" @selected(($filters['action'] ?? '') === 'password_reset')>Reset password</option>
                    <option value="account_deleted" @selected(($filters['action'] ?? '') === 'account_deleted')>Penghapusan akun</option>
                </select>
            </label>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary flex-1 sm:flex-none">
                    <i class="fas fa-filter"></i> Filter
                </button>
                @if(($filters['q'] ?? '') !== '' || ($filters['action'] ?? '') !== '')
                    <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-ghost">Reset</a>
                @endif
            </div>
        </form>

        <section class="app-surface overflow-hidden">
            @if($auditLogs->isEmpty())
                <div class="px-6 py-12 text-center">
                    <i class="fas fa-clipboard-check text-2xl text-base-content/35" aria-hidden="true"></i>
                    <h2 class="mt-3 font-semibold">Belum ada aktivitas admin</h2>
                    <p class="mt-1 text-sm text-base-content/55">Perubahan akun berikutnya akan tercatat di sini.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="table min-w-[760px]">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Tindakan</th>
                                <th>Admin</th>
                                <th>Akun terkait</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($auditLogs as $entry)
                                <tr>
                                    <td class="whitespace-nowrap text-sm text-base-content/65">{{ $entry->created_at->format('d M Y, H:i') }}</td>
                                    <td>
                                        @if($entry->action === 'role_changed')
                                            <span class="font-medium">Role diubah</span>
                                            <p class="mt-1 text-xs text-base-content/55">
                                                {{ ucfirst($entry->details['from'] ?? '—') }} → {{ ucfirst($entry->details['to'] ?? '—') }}
                                            </p>
                                        @elseif($entry->action === 'password_reset')
                                            <span class="font-medium">Password direset</span>
                                            <p class="mt-1 text-xs text-base-content/55">Password sementara dibuat</p>
                                        @else
                                            <span class="font-medium">Akun dihapus</span>
                                            <p class="mt-1 text-xs text-base-content/55">Penghapusan permanen</p>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="font-medium">{{ $entry->actor_name }}</div>
                                        <div class="mt-0.5 text-sm text-base-content/55">{{ $entry->actor_email }}</div>
                                    </td>
                                    <td>
                                        <div class="font-medium">{{ $entry->target_name ?? 'Akun dihapus' }}</div>
                                        <div class="mt-0.5 text-sm text-base-content/55">{{ $entry->target_email ?? 'Data akun sudah dihapus' }}</div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($auditLogs->hasPages())
                    <div class="border-t border-base-300/70 px-4 py-4 sm:px-5">
                        {{ $auditLogs->links() }}
                    </div>
                @endif
            @endif
        </section>
    </div>
@endsection
