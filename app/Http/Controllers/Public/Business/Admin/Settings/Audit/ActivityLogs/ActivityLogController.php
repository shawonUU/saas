<?php

namespace App\Http\Controllers\Public\Business\Admin\Settings\Audit\ActivityLogs;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\Public\Business\Admin\Authorization\AccessControl\Access;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class ActivityLogController extends Controller
{
    public function index(Request $request, Access $access): View
    {
        $data = $access->check($request, 'settings', 'audit', 'activity-logs');
        $filters = $request->validate([
            'table' => ['nullable', 'string', 'max:100'], 'action' => ['nullable', 'in:created,updated,deleted'],
            'business_date' => ['nullable', 'date_format:Y-m-d'],
        ]);
        $query = AuditLog::query()->where('business_id', $data['business']->id);
        if (! empty($filters['table'])) $query->where('subject_table', $filters['table']);
        if (! empty($filters['action'])) $query->where('action', $filters['action']);
        if (! empty($filters['business_date'])) $query->whereDate('business_date', $filters['business_date']);
        $data['logs'] = $query->orderByDesc('id')->paginate(25)->withQueryString();
        return view('public.business.admin.settings.audit.activity-logs.index', $data);
    }

    public function record(Request $request, Access $access): JsonResponse
    {
        $data = $access->check($request, 'settings', 'audit', 'activity-logs');
        $valid = $request->validate([
            'table' => ['required','string','regex:/^[a-z0-9_]+$/','max:100'],
            'id' => ['required','string','max:100'],
        ]);
        $records = AuditLog::query()->where('business_id', $data['business']->id)
            ->where('subject_table', $valid['table'])->where('subject_id', $valid['id'])
            ->orderByDesc('id')->limit(100)->get();
        return response()->json([
            'title' => $valid['table'].' #'.$valid['id'],
            'events' => $records->map(fn (AuditLog $log) => [
                'action' => $log->action,
                'when' => (string) $log->created_at,
                'business_date' => $log->business_date ? (string) $log->business_date : null,
                'actor' => $log->actor_name ?: ($log->actor_id ? (string) $log->actor_id : t('System / unknown','সিস্টেম / অজানা')),
                'fields' => $this->changes($log->old_values ?? [], $log->new_values ?? []),
            ])->all(),
        ]);
    }

    private function changes(array $old, array $new): array
    {
        $keys = array_unique(array_merge(array_keys($old), array_keys($new)));
        $result = [];
        foreach ($keys as $key) {
            $before = $old[$key] ?? null;
            $after = $new[$key] ?? null;
            if (array_key_exists($key, $old) && array_key_exists($key, $new) && $before === $after) continue;
            $result[] = ['field' => $key, 'before' => $before, 'after' => $after];
        }
        return $result;
    }
}
