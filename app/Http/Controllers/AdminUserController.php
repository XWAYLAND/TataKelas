<?php

namespace App\Http\Controllers;

use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    private const ROLES = ['admin', 'teacher', 'student'];

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', Rule::in(self::ROLES)],
        ]);

        $query = User::query()
            ->select(['id', 'name', 'email', 'role', 'created_at'])
            ->latest('created_at');

        if ($search = trim($filters['q'] ?? '')) {
            $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $filters['role'] ?? null) {
            $query->where('role', $role);
        }

        $users = $query->paginate(15)->withQueryString();
        $roleCounts = User::query()
            ->selectRaw('role, COUNT(*) as aggregate')
            ->groupBy('role')
            ->pluck('aggregate', 'role');

        return view('admin.users.index', [
            'users' => $users,
            'filters' => $filters,
            'roleCounts' => $roleCounts,
            'totalUsers' => $roleCounts->sum(),
        ]);
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in(self::ROLES)],
        ]);

        $changed = false;
        $error = DB::transaction(function () use ($request, $user, $validated, &$changed): ?string {
            $adminIds = User::query()
                ->where('role', 'admin')
                ->orderBy('id')
                ->lockForUpdate()
                ->pluck('id');
            $target = User::query()->lockForUpdate()->findOrFail($user->getKey());

            if ($target->is($request->user())) {
                return 'Role akun yang sedang Anda gunakan tidak dapat diubah dari halaman ini.';
            }

            if ($target->role === 'admin' && $validated['role'] !== 'admin' && $adminIds->count() <= 1) {
                return 'Role admin tidak dapat dihapus dari satu-satunya akun admin.';
            }

            if (
                $validated['role'] === 'student'
                && in_array($target->role, ['teacher', 'admin'], true)
                && DB::table('classrooms')->where('teacher_id', $target->id)->exists()
            ) {
                return 'Role tidak dapat diubah menjadi student selama akun ini masih mengelola classroom.';
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
            ->route('admin.index')
            ->with('success', 'Password sementara dibuat. Sampaikan kepada pengguna melalui saluran yang aman.')
            ->with('temporary_password', $temporaryPassword);
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

    private function accountDeletionBlockReason(User $user): ?string
    {
        $hasFolders = DB::table('folders')->where('user_id', $user->id)->exists();
        $teachesClassrooms = DB::table('classrooms')->where('teacher_id', $user->id)->exists();
        $isClassroomMember = DB::table('classroom_user')->where('user_id', $user->id)->exists();
        $hasSubmissions = DB::table('submissions')->where('student_id', $user->id)->exists();

        if ($hasFolders || $teachesClassrooms || $isClassroomMember || $hasSubmissions) {
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
}
