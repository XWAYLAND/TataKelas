<?php

namespace App\Http\Controllers;

use App\Models\AdminAuditLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminAuditLogController extends Controller
{
    private const ACTIONS = [
        AdminAuditLog::ROLE_CHANGED,
        AdminAuditLog::PASSWORD_RESET,
        AdminAuditLog::ACCOUNT_DELETED,
        AdminAuditLog::ACCOUNT_SUSPENDED,
        AdminAuditLog::ACCOUNT_REACTIVATED,
    ];

    public function index(Request $request): View
    {
        $filters = $this->validatedFilters($request);

        $query = AdminAuditLog::query()->latest();
        $this->applyFilters($query, $filters);

        return view('admin.audit-logs.index', [
            'auditLogs' => $query->paginate(25)->withQueryString(),
            'filters' => $filters,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->validatedFilters($request);

        $query = AdminAuditLog::query()->latest();
        $this->applyFilters($query, $filters);

        $filename = 'tatakelas-audit-logs-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Waktu', 'Tindakan', 'Admin', 'Akun terkait', 'Detail']);

            foreach ($query->cursor() as $log) {
                fputcsv($handle, [
                    $log->created_at->format('Y-m-d H:i'),
                    $log->action,
                    $log->actor_name.' <'.$log->actor_email.'>',
                    trim(($log->target_name ?? '-').' <'.($log->target_email ?? '-').'>'),
                    $log->details ? json_encode($log->details) : '-',
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'action' => ['nullable', Rule::in(self::ACTIONS)],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
    }

    private function applyFilters($query, array $filters): void
    {
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

        if ($from = $filters['from'] ?? null) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $filters['to'] ?? null) {
            $query->whereDate('created_at', '<=', $to);
        }
    }
}
