<?php

namespace App\Http\Controllers;

use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $userId = auth()->id();

        $stats = [
            'users_by_role' => DB::table('users')
                ->selectRaw('role, COUNT(*) as total')
                ->groupBy('role')
                ->pluck('total', 'role'),
            'users_by_status' => DB::table('users')
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
            'new_users_30d' => DB::table('users')->where('created_at', '>=', now()->subDays(30))->count(),
            'unverified_users' => DB::table('users')->whereNull('email_verified_at')->count(),
            'classrooms' => DB::table('classrooms')->count(),
            'assignments' => DB::table('folders')
                ->where('visibility', 'assignment')
                ->whereNull('classroom_id')
                ->count(),
            'resources' => DB::table('resources')->count(),
        ];

        $pendingSubmissions = DB::table('submissions')
            ->whereNotIn('status', ['graded'])
            ->count();

        $recentUsers = User::query()
            ->select(['id', 'name', 'email', 'role', 'status', 'created_at'])
            ->latest('created_at')
            ->limit(5)
            ->get();

        $recentAuditLogs = AdminAuditLog::query()
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'pendingSubmissions' => $pendingSubmissions,
            'recentUsers' => $recentUsers,
            'recentAuditLogs' => $recentAuditLogs,
        ]);
    }
}
