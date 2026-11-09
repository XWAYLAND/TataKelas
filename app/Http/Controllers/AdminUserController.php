<?php

namespace App\Http\Controllers;

use App\Models\AdminAuditLog;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminUserController extends Controller
{
    private const ROLES = ['admin', 'teacher', 'student'];

    public function index(Request $request): View
    {
        $filters = $this->validatedFilters($request);

        $query = User::query()
            ->select(['id', 'name', 'email', 'role', 'status', 'email_verified_at', 'last_login_at', 'created_at']);

        $this->applyFilters($query, $filters);
        $this->applySorting($query, $filters);

        $users = $query->paginate(15)->withQueryString();

        $roleCounts = User::query()
            ->selectRaw('role, COUNT(*) as aggregate')
            ->groupBy('role')
            ->pluck('aggregate', 'role');

        $statusCounts = User::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return view('admin.users.index', [
            'users' => $users,
            'filters' => $filters,
            'roleCounts' => $roleCounts,
            'statusCounts' => $statusCounts,
            'totalUsers' => $roleCounts->sum(),
            'activeFilterCount' => $this->activeFilterCount($filters),
        ]);
    }

    public function show(User $user): View
    {
        $counts = $this->contentCounts($user->id);

        $taughtClassrooms = Classroom::query()
            ->withCount('students')
            ->where('teacher_id', $user->id)
            ->get();

        $joinedClassrooms = Classroom::query()
            ->withCount('students')
            ->whereHas('students', fn (Builder $query) => $query->where('users.id', $user->id))
            ->get();

        $coTaughtClassrooms = Classroom::query()
            ->withCount('students')
            ->whereHas('coTeachers', fn (Builder $query) => $query->where('users.id', $user->id))
            ->get();

        $auditLogs = AdminAuditLog::query()
            ->where('target_user_id', $user->id)
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.users.show', [
            'user' => $user,
            'counts' => $counts,
            'taughtClassrooms' => $taughtClassrooms,
            'joinedClassrooms' => $joinedClassrooms,
            'coTaughtClassrooms' => $coTaughtClassrooms,
            'auditLogs' => $auditLogs,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->validatedFilters($request);

        $query = User::query()->select(['name', 'email', 'role', 'status', 'email_verified_at', 'last_login_at', 'created_at']);
        $this->applyFilters($query, $filters);
        $this->applySorting($query, $filters);

        $filename = 'tatakelas-users-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Nama', 'Email', 'Role', 'Status', 'Email terverifikasi', 'Login terakhir', 'Bergabung']);

            foreach ($query->cursor() as $user) {
                fputcsv($handle, [
                    $user->name,
                    $user->email,
                    ucfirst($user->role),
                    $user->status_label,
                    $user->email_verified_at ? 'Ya' : 'Tidak',
                    $user->last_login_at?->format('Y-m-d H:i') ?: 'Belum pernah login',
                    $user->created_at->format('Y-m-d H:i'),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in(self::ROLES)],
        ]);

        $changed = false;
        $error = DB::transaction(function () use ($request, $user, $validated, &$changed): ?string {
            $target = User::query()->lockForUpdate()->findOrFail($user->getKey());
            $guards = $this->roleChangeGuardMessage($request, $target, $validated['role']);

            if ($guards !== null) {
                return $guards;
            }

            if ($target->role === $validated['role']) {
                return null;
            }

            $previousRole = $target->role;
            $target->role = $validated['role'];
            $target->save();
            $changed = true;

            $this->recordAdminAction($request, $target, AdminAuditLog::ROLE_CHANGED, [
                'from' => $previousRole,
                'to' => $target->role,
            ]);

            return null;
        });

        if ($error !== null) {
            return back()->withErrors(['role' => $error]);
        }

        if (! $changed) {
            return back()->with('info', 'Role pengguna tidak berubah.');
        }

        return back()->with('success', 'Role pengguna berhasil diperbarui.');
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $temporaryPassword = Str::password(24, symbols: false);

        DB::transaction(function () use ($request, $user, $temporaryPassword): void {
            $target = User::query()->lockForUpdate()->findOrFail($user->getKey());
            $target->forceFill([
                'password' => $temporaryPassword,
                'remember_token' => Str::random(60),
            ])->save();

            $this->recordAdminAction($request, $target, AdminAuditLog::PASSWORD_RESET, [
                'method' => 'generated_temporary_password',
            ]);
        });

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'Password sementara dibuat. Sampaikan kepada pengguna melalui saluran yang aman.')
            ->with('temporary_password', $temporaryPassword);
    }

    public function suspend(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $error = DB::transaction(function () use ($request, $user, $validated): ?string {
            $target = User::query()->lockForUpdate()->findOrFail($user->getKey());

            if ($guards = $this->suspensionGuardMessage($request, $target)) {
                return $guards;
            }

            if ($target->isSuspended()) {
                return null;
            }

            $this->applySuspension($target, $validated['reason'] ?? null);

            $this->recordAdminAction($request, $target, AdminAuditLog::ACCOUNT_SUSPENDED, [
                'reason' => $validated['reason'] ?? null,
            ]);

            return null;
        });

        if ($error !== null) {
            return back()->withErrors(['account' => $error]);
        }

        return back()->with('success', 'Akun pengguna ditangguhkan.');
    }

    public function reactivate(Request $request, User $user): RedirectResponse
    {
        $error = DB::transaction(function () use ($request, $user): ?string {
            $target = User::query()->lockForUpdate()->findOrFail($user->getKey());

            if ($target->isActive()) {
                return 'Akun ini sudah aktif.';
            }

            $target->forceFill([
                'status' => User::STATUS_ACTIVE,
                'suspended_at' => null,
                'suspension_reason' => null,
            ])->save();

            $this->recordAdminAction($request, $target, AdminAuditLog::ACCOUNT_REACTIVATED);

            return null;
        });

        if ($error !== null) {
            return back()->withErrors(['account' => $error]);
        }

        return back()->with('success', 'Akun pengguna diaktifkan kembali.');
    }

    public function bulk(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['suspend', 'reactivate', 'role'])],
            'ids' => ['required', 'array', 'min:1', 'max:100'],
            'ids.*' => ['integer'],
            'role' => ['nullable', 'required_if:action,role', Rule::in(self::ROLES)],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $result = ['processed' => 0, 'skipped' => []];

        DB::transaction(function () use ($request, $validated, &$result): void {
            foreach (array_map('intval', $validated['ids']) as $id) {
                $target = User::query()->lockForUpdate()->find($id);

                if ($target === null) {
                    $result['skipped'][] = "ID {$id} tidak ditemukan.";

                    continue;
                }

                switch ($validated['action']) {
                    case 'suspend':
                        if ($guards = $this->suspensionGuardMessage($request, $target)) {
                            $result['skipped'][] = "{$target->email}: {$guards}";

                            break;
                        }
                        if ($target->isSuspended()) {
                            $result['skipped'][] = "{$target->email} sudah ditangguhkan.";

                            break;
                        }
                        $this->applySuspension($target, $validated['reason'] ?? null);
                        $this->recordAdminAction($request, $target, AdminAuditLog::ACCOUNT_SUSPENDED, [
                            'reason' => $validated['reason'] ?? null,
                        ]);
                        $result['processed']++;

                        break;

                    case 'reactivate':
                        if ($target->isActive()) {
                            $result['skipped'][] = "{$target->email} sudah aktif.";

                            break;
                        }
                        $target->forceFill([
                            'status' => User::STATUS_ACTIVE,
                            'suspended_at' => null,
                            'suspension_reason' => null,
                        ])->save();
                        $this->recordAdminAction($request, $target, AdminAuditLog::ACCOUNT_REACTIVATED);
                        $result['processed']++;

                        break;

                    case 'role':
                        if ($guards = $this->roleChangeGuardMessage($request, $target, $validated['role'])) {
                            $result['skipped'][] = "{$target->email}: {$guards}";

                            break;
                        }
                        if ($target->role === $validated['role']) {
                            $result['skipped'][] = "{$target->email} sudah ber-role tersebut.";

                            break;
                        }
                        $previousRole = $target->role;
                        $target->role = $validated['role'];
                        $target->save();
                        $this->recordAdminAction($request, $target, AdminAuditLog::ROLE_CHANGED, [
                            'from' => $previousRole,
                            'to' => $target->role,
                        ]);
                        $result['processed']++;

                        break;
                }
            }
        });

        return back()->with(['bulk_result' => [
            'action' => $validated['action'],
            'processed' => $result['processed'],
            'skipped' => $result['skipped'],
        ]]);
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $error = DB::transaction(function () use ($request, $user): ?string {
            $adminIds = User::query()
                ->where('role', 'admin')
                ->orderBy('id')
                ->lockForUpdate()
                ->pluck('id');
            $target = User::query()->lockForUpdate()->findOrFail($user->getKey());

            if ($target->is($request->user())) {
                return 'Anda tidak dapat menghapus akun yang sedang digunakan.';
            }

            if ($target->role === 'admin' && $adminIds->count() <= 1) {
                return 'Akun admin terakhir tidak dapat dihapus.';
            }

            if ($reason = $this->accountDeletionBlockReason($target)) {
                return $reason;
            }

            $this->recordAdminAction($request, $target, AdminAuditLog::ACCOUNT_DELETED);
            $target->delete();

            return null;
        });

        if ($error !== null) {
            return back()->withErrors(['account' => $error]);
        }

        return back()->with('success', 'Akun pengguna berhasil dihapus.');
    }

    private function suspensionGuardMessage(Request $request, User $target): ?string
    {
        if ($target->is($request->user())) {
            return 'Anda tidak dapat menangguhkan akun yang sedang Anda gunakan.';
        }

        if ($this->isAdminInDisallowedState($target)) {
            return 'Admin aktif tidak dapat ditangguhkan karena masih menjadi admin aktif terakhir.';
        }

        return null;
    }

    private function roleChangeGuardMessage(Request $request, User $target, string $role): ?string
    {
        if ($target->is($request->user())) {
            return 'Role akun yang sedang Anda gunakan tidak dapat diubah dari halaman ini.';
        }

        if ($target->isSuspended()) {
            return "Akun {$target->email} sedang ditangguhkan.";
        }

        if ($target->role === 'admin' && $role !== 'admin'
            && User::query()->where('role', 'admin')->where('status', User::STATUS_ACTIVE)->count() <= 1
        ) {
            return 'Role admin tidak dapat dihapus dari satu-satunya admin aktif.';
        }

        if (
            $role === 'student'
            && in_array($target->role, ['teacher', 'admin'], true)
            && (
                DB::table('classrooms')->where('teacher_id', $target->id)->exists()
                || DB::table('classroom_co_teacher')->where('user_id', $target->id)->exists()
            )
        ) {
            return 'Role tidak dapat diubah menjadi student selama akun ini masih mengelola atau ikut mengajar classroom.';
        }

        return null;
    }

    private function applySuspension(User $target, ?string $reason): void
    {
        $target->forceFill([
            'status' => User::STATUS_SUSPENDED,
            'suspended_at' => now(),
            'suspension_reason' => $reason,
            'remember_token' => Str::random(60),
        ])->save();
    }

    private function isAdminInDisallowedState(User $target): bool
    {
        if ($target->role !== 'admin' || $target->isSuspended()) {
            return false;
        }

        $activeAdmins = User::query()
            ->where('role', 'admin')
            ->where('status', User::STATUS_ACTIVE)
            ->count();

        return $activeAdmins <= 1;
    }

    private function contentCounts(int $userId): array
    {
        return [
            'folders' => DB::table('folders')->where('user_id', $userId)->count(),
            'resources' => DB::table('resources')
                ->join('folders', 'resources.folder_id', '=', 'folders.id')
                ->where('folders.user_id', $userId)
                ->count(),
            'notes' => DB::table('notes')
                ->join('folders', 'notes.folder_id', '=', 'folders.id')
                ->where('folders.user_id', $userId)
                ->count(),
            'photos' => DB::table('photos')
                ->join('folders', 'photos.folder_id', '=', 'folders.id')
                ->where('folders.user_id', $userId)
                ->count(),
            'submissions' => DB::table('submissions')->where('student_id', $userId)->count(),
        ];
    }

    private function accountDeletionBlockReason(User $user): ?string
    {
        $hasFolders = DB::table('folders')->where('user_id', $user->id)->exists();
        $teachesClassrooms = DB::table('classrooms')->where('teacher_id', $user->id)->exists();
        $isClassroomMember = DB::table('classroom_user')->where('user_id', $user->id)->exists();
        $isCoTeacher = DB::table('classroom_co_teacher')->where('user_id', $user->id)->exists();
        $hasSubmissions = DB::table('submissions')->where('student_id', $user->id)->exists();

        if ($hasFolders || $teachesClassrooms || $isClassroomMember || $isCoTeacher || $hasSubmissions) {
            return 'Akun tidak dapat dihapus karena masih memiliki folder, classroom, keanggotaan kelas, atau submission terkait.';
        }

        return null;
    }

    private function recordAdminAction(
        Request $request,
        User $target,
        string $action,
        ?array $details = null,
    ): void {
        $actor = $request->user();

        AdminAuditLog::query()->create([
            'actor_user_id' => $actor->id,
            'actor_name' => $actor->name,
            'actor_email' => $actor->email,
            'target_user_id' => $target->id,
            'target_name' => $target->name,
            'target_email' => $target->email,
            'action' => $action,
            'details' => $details,
        ]);
    }

    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', Rule::in(self::ROLES)],
            'status' => ['nullable', Rule::in([User::STATUS_ACTIVE, User::STATUS_SUSPENDED])],
            'verified' => ['nullable', Rule::in(['verified', 'unverified'])],
            'joined_from' => ['nullable', 'date'],
            'joined_to' => ['nullable', 'date', 'after_or_equal:joined_from'],
            'sort' => ['nullable', Rule::in(['created_at', 'name', 'last_login_at'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        if ($search = trim($filters['q'] ?? '')) {
            $query->where(function (Builder $query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $filters['role'] ?? null) {
            $query->where('role', $role);
        }

        if ($status = $filters['status'] ?? null) {
            $query->where('status', $status);
        }

        if ($verified = $filters['verified'] ?? null) {
            $verified === 'verified'
                ? $query->whereNotNull('email_verified_at')
                : $query->whereNull('email_verified_at');
        }

        if ($joinedFrom = $filters['joined_from'] ?? null) {
            $query->whereDate('created_at', '>=', $joinedFrom);
        }

        if ($joinedTo = $filters['joined_to'] ?? null) {
            $query->whereDate('created_at', '<=', $joinedTo);
        }
    }

    private function applySorting(Builder $query, array $filters): void
    {
        $sort = $filters['sort'] ?? 'created_at';
        $direction = $filters['direction'] ?? 'desc';

        $query->orderBy($sort, $direction);
    }

    private function activeFilterCount(array $filters): int
    {
        return collect(['q', 'role', 'status', 'verified', 'joined_from', 'joined_to'])
            ->filter(fn ($key) => ! empty($filters[$key]))
            ->count();
    }
}
