<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\SupportAuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Journal d'audit global de la console de support.
 */
class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = SupportAuditLog::query()
            ->with(['supportUser', 'company'])
            ->when($request->filled('action'), fn ($q) => $q->where('action', $request->string('action')))
            ->latest('created_at')
            ->paginate(40)
            ->withQueryString();

        return view('support.audit.index', [
            'logs'    => $logs,
            'actions' => SupportAuditLog::query()->distinct()->orderBy('action')->pluck('action'),
        ]);
    }
}
