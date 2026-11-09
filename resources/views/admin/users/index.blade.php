@extends('layouts.app')

@section('title', 'Kelola pengguna')

@section('content')
    <div class="space-y-6">
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Kelola pengguna</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-base-content/60">
                    Atur role, password, dan status akun. Penghapusan hanya tersedia untuk akun tanpa data terkait.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.users.export', request()->query()) }}" class="btn btn-ghost border-base-300">
                    <i class="fas fa-file-csv"></i>
                    Export CSV
                </a>
                <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-ghost border-base-300">
                    <i class="fas fa-clock-rotate-left"></i>
                    Lihat audit log
                </a>
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

        @if(session()->has('bulk_result'))
            @php($bulk = session('bulk_result'))
            <section role="status" class="alert alert-info items-start border border-info/30">
                <i class="fas fa-list-check mt-1"></i>
                <div class="min-w-0 flex-1">
                    <p class="font-semibold">Aksi massal selesai</p>
                    <p class="mt-1 text-sm">
                        {{ $bulk['processed'] }} akun diproses
                        @if(! empty($bulk['skipped']))
                            · {{ count($bulk['skipped']) }} dilewati
                        @endif
                    </p>
                    @if(! empty($bulk['skipped']))
                        <ul class="mt-2 list-disc pl-5 text-xs text-base-content/65">
                            @foreach(array_slice($bulk['skipped'], 0, 6) as $reason)
                                <li>{{ $reason }}</li>
                            @endforeach
                        </ul>
                    @endif
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
            <div class="min-w-32 flex-1 px-4 py-3 sm:px-5">
                <dt class="text-xs text-base-content/55">Ditangguhkan</dt>
                <dd class="mt-1 text-xl font-semibold tabular-nums">{{ $statusCounts->get('suspended', 0) }}</dd>
            </div>
        </dl>

        <form method="GET" action="{{ route('admin.users.index') }}" class="app-surface grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 sm:items-end">
            <label class="form-control lg:col-span-2 xl:col-span-1">
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
            <label class="form-control">
                <span class="label-text mb-2 font-medium">Status</span>
                <select name="status" class="select select-bordered w-full">
                    <option value="">Semua status</option>
                    <option value="active" @selected(($filters['status'] ?? '') === 'active')>Aktif</option>
                    <option value="suspended" @selected(($filters['status'] ?? '') === 'suspended')>Ditangguhkan</option>
                </select>
            </label>
            <label class="form-control">
                <span class="label-text mb-2 font-medium">Email</span>
                <select name="verified" class="select select-bordered w-full">
                    <option value="">Semua</option>
                    <option value="verified" @selected(($filters['verified'] ?? '') === 'verified')>Terverifikasi</option>
                    <option value="unverified" @selected(($filters['verified'] ?? '') === 'unverified')>Belum terverifikasi</option>
                </select>
            </label>
            <label class="form-control">
                <span class="label-text mb-2 font-medium">Bergabung dari</span>
                <input type="date" name="joined_from" value="{{ $filters['joined_from'] ?? '' }}" class="input input-bordered w-full">
            </label>
            <label class="form-control">
                <span class="label-text mb-2 font-medium">Bergabung hingga</span>
                <input type="date" name="joined_to" value="{{ $filters['joined_to'] ?? '' }}" class="input input-bordered w-full">
            </label>
            <label class="form-control">
                <span class="label-text mb-2 font-medium">Urutkan</span>
                <select name="sort" class="select select-bordered w-full">
                    <option value="created_at" @selected(($filters['sort'] ?? 'created_at') === 'created_at')>Tanggal bergabung</option>
                    <option value="name" @selected(($filters['sort'] ?? '') === 'name')>Nama</option>
                    <option value="last_login_at" @selected(($filters['sort'] ?? '') === 'last_login_at')>Login terakhir</option>
                </select>
            </label>
            <label class="form-control">
                <span class="label-text mb-2 font-medium">Arah</span>
                <select name="direction" class="select select-bordered w-full">
                    <option value="desc" @selected(($filters['direction'] ?? 'desc') === 'desc')>Terbaru / terbesar</option>
                    <option value="asc" @selected(($filters['direction'] ?? '') === 'asc')>Terlama / terkecil</option>
                </select>
            </label>
            <div class="flex gap-2 xl:col-span-2">
                <button type="submit" class="btn btn-primary flex-1 sm:flex-none">
                    <i class="fas fa-filter"></i> Terapkan
                </button>
                @if($activeFilterCount > 0)
                    <a href="{{ route('admin.users.index') }}" class="btn btn-ghost">Reset</a>
                @endif
            </div>
        </form>

        <form id="admin-bulk-form" method="POST" action="{{ route('admin.users.bulk') }}" class="hidden">
            @csrf
            @method('PATCH')
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
                    <p class="mt-1 text-sm text-base-content/55">Coba kata kunci atau filter lain yang berbeda.</p>
                </div>
            @else
                <div class="divide-y divide-base-300/70 xl:hidden">
                    @foreach($users as $user)
                        <article class="space-y-4 p-4">
                            <header class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <input
                                            type="checkbox"
                                            form="admin-bulk-form"
                                            name="ids[]"
                                            value="{{ $user->id }}"
                                            class="checkbox checkbox-sm checkbox-primary"
                                            aria-label="Pilih {{ $user->email }}"
                                        >
                                        <h3 class="truncate font-semibold">
                                            <a class="hover:text-primary" href="{{ route('admin.users.show', $user) }}">{{ $user->name }}</a>
                                        </h3>
                                    </div>
                                    <p class="mt-0.5 break-all text-sm text-base-content/55">{{ $user->email }}</p>
                                    <p class="mt-1 text-xs text-base-content/45">Bergabung {{ $user->created_at->format('d M Y') }}</p>
                                    <div class="mt-2 flex flex-wrap gap-1">
                                        <span class="badge border-0 {{ $user->status_badge_color }}">{{ $user->status_label }}</span>
                                        <span class="badge badge-ghost border-0">{{ ucfirst($user->role) }}</span>
                                    </div>
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
                    <table class="table min-w-[1080px]">
                        <thead>
                            <tr>
                                <th class="w-12"><span class="sr-only">Pilih</span></th>
                                <th>Pengguna</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Email</th>
                                <th>Login terakhir</th>
                                <th>Bergabung</th>
                                <th>Akses dan tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr>
                                    <td>
                                        <input
                                            type="checkbox"
                                            form="admin-bulk-form"
                                            name="ids[]"
                                            value="{{ $user->id }}"
                                            class="checkbox checkbox-sm checkbox-primary"
                                            aria-label="Pilih {{ $user->email }}"
                                        >
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.users.show', $user) }}" class="font-semibold hover:text-primary">{{ $user->name }}</a>
                                        <div class="mt-0.5 text-sm font-normal text-base-content/55">{{ $user->email }}</div>
                                        @if($user->is(auth()->user()))
                                            <span class="badge badge-sm badge-primary mt-2">Akun Anda</span>
                                        @endif
                                    </td>
                                    <td><span class="badge badge-ghost border-0">{{ ucfirst($user->role) }}</span></td>
                                    <td><span class="badge border-0 {{ $user->status_badge_color }}">{{ $user->status_label }}</span></td>
                                    <td>
                                        <span class="text-sm {{ $user->email_verified_at ? 'text-success' : 'text-warning' }}">
                                            {{ $user->email_verified_at ? 'Terverifikasi' : 'Belum' }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap text-sm text-base-content/65">
                                        {{ $user->last_login_at?->format('d M Y, H:i') ?? 'Belum pernah' }}
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

                <div class="border-t border-base-300/70 bg-base-200/40 px-4 py-3 sm:px-5">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div class="grid gap-3 sm:grid-cols-[10rem_minmax(0,12rem)_minmax(0,1fr)]">
                            <label class="form-control">
                                <span class="label-text mb-2 font-medium">Aksi massal</span>
                                <select id="bulk-action" name="action" form="admin-bulk-form" class="select select-bordered select-sm w-full">
                                    <option value="suspend">Tangguhkan</option>
                                    <option value="reactivate">Aktifkan kembali</option>
                                    <option value="role">Ubah role</option>
                                </select>
                            </label>
                            <label class="form-control">
                                <span class="label-text mb-2 font-medium">Role (untuk ubah role)</span>
                                <select id="bulk-role" name="role" form="admin-bulk-form" class="select select-bordered select-sm w-full" disabled>
                                    <option value="">Pilih role</option>
                                    @foreach(['admin' => 'Admin', 'teacher' => 'Teacher', 'student' => 'Student'] as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="form-control">
                                <span class="label-text mb-2 font-medium">Alasan (untuk penangguhan)</span>
                                <input type="text" name="reason" form="admin-bulk-form" maxlength="500" class="input input-bordered input-sm w-full" placeholder="Opsional">
                            </label>
                        </div>
                        <button type="submit" form="admin-bulk-form" class="btn btn-primary btn-sm">
                            <i class="fas fa-bolt"></i> Jalankan aksi
                        </button>
                    </div>
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

@push('scripts')
    <script>
        (function () {
            var actionSelect = document.getElementById('bulk-action');
            var roleSelect = document.getElementById('bulk-role');

            if (!actionSelect || !roleSelect) return;

            function syncRoleField() {
                roleSelect.disabled = actionSelect.value !== 'role';
            }

            actionSelect.addEventListener('change', syncRoleField);
            syncRoleField();
        })();
    </script>
@endpush
