<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = AuditLog::query()
            ->with('user')
            ->when($request->filled('q'), fn ($query) => $query->whereHas(
                'user',
                fn ($userQuery) => $userQuery->where('name', 'like', '%'.$request->string('q')->toString().'%')
            ))
            ->when($request->filled('action'), fn ($query) => $query->where('action', 'like', $request->string('action')->toString().'%'))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('created_at', '>=', $request->string('date_from')->toString()))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('created_at', '<=', $request->string('date_to')->toString()))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.audit-logs.index', [
            'logs' => $logs,
            'filters' => $request->only(['q', 'action', 'date_from', 'date_to']),
            'actions' => AuditLog::query()->distinct()->orderBy('action')->pluck('action'),
        ]);
    }
}
