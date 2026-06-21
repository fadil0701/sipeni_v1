<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\ModuleRegistry;
use Illuminate\Http\Request;

class AuditTrailController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::query()->with('user')->latest('created_at');

        if ($request->filled('search')) {
            $search = (string) $request->input('search');
            $query->where(function ($q) use ($search): void {
                $q->where('action', 'like', '%'.$search.'%')
                    ->orWhere('module_key', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%')
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%'));
            });
        }

        if ($request->filled('action')) {
            $query->where('action', (string) $request->input('action'));
        }

        if ($request->filled('module_key')) {
            $query->where('module_key', (string) $request->input('module_key'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $logs = $query->paginate(20)->withQueryString();

        $actions = ActivityLog::query()->select('action')->distinct()->orderBy('action')->pluck('action');
        $moduleKeys = ActivityLog::query()->select('module_key')->whereNotNull('module_key')->distinct()->orderBy('module_key')->pluck('module_key');
        $users = User::query()->whereIn('id', ActivityLog::query()->select('user_id')->whereNotNull('user_id')->distinct())->orderBy('name')->get(['id', 'name', 'email']);

        $registry = app(ModuleRegistry::class);
        $moduleLabels = collect($registry->getModules())->mapWithKeys(fn ($meta, $key) => [$key => $meta['label'] ?? $key]);

        $summary = [
            'total_entries' => ActivityLog::count(),
            'today_entries' => ActivityLog::query()->whereDate('created_at', today())->count(),
        ];

        return view('admin.audit-trail.index', compact('logs', 'actions', 'moduleKeys', 'users', 'moduleLabels', 'summary'));
    }

    public function show(ActivityLog $activity_log)
    {
        $activity_log->load('user');

        $registry = app(ModuleRegistry::class);
        $moduleLabel = $registry->getModule((string) $activity_log->module_key)['label'] ?? $activity_log->module_key;

        return view('admin.audit-trail.show', [
            'log' => $activity_log,
            'moduleLabel' => $moduleLabel,
        ]);
    }
}
