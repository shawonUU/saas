<?php

namespace App\Http\Controllers\Authority\Settings\Audit\ActivityLogs;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'business_id' => ['nullable', 'integer', 'min:1'],
            'table' => ['nullable', 'string', 'max:100'],
        ]);
        $query = AuditLog::query();
        if (! empty($filters['business_id'])) $query->where('business_id', $filters['business_id']);
        if (! empty($filters['table'])) $query->where('subject_table', $filters['table']);
        return view('authority.settings.audit.activity-logs.index', [
            'logs' => $query->latest('id')->paginate(25)->withQueryString(),
        ]);
    }
}
