<?php

namespace App\Http\Controllers;

use App\Models\AdminAuditLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminAuditLogController extends Controller
{
    private const ACTIONS = [
        AdminAuditLog::ROLE_CHANGED,
        AdminAuditLog::PASSWORD_RESET,
        AdminAuditLog::ACCOUNT_DELETED,
    ];

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'action' => ['nullable', Rule::in(self::ACTIONS)],
        ]);

        $query = AdminAuditLog::query()->latest();

        if ($search = trim($filters['q'] ?? '')) {
            $query->where(function ($query) use ($search): void {
                $query->where('actor_name', 'like', "%{$search}%")
                    ->orWhere('actor_email', 'like', "%{$search}%")
                    ->orWhere('target_name', 'like', "%{$search}%")
                    ->orWhere('target_email', 'like', "%{$search}%");
            });
        }

        if ($action = $filters['action'] ?? null) {
            $query->where('action', $action);
        }

        return view('admin.audit-logs.index', [
            'auditLogs' => $query->paginate(25)->withQueryString(),
            'filters' => $filters,
        ]);
    }
}
