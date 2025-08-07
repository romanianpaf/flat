<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Log;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $query = Log::with('user')
            ->select('logs.*')
            ->leftJoin('users', 'logs.user_id', '=', 'users.id');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('logs.action', 'like', "%{$search}%")
                  ->orWhere('logs.description', 'like', "%{$search}%")
                  ->orWhere('logs.ip_address', 'like', "%{$search}%")
                  ->orWhere('users.name', 'like', "%{$search}%");
            });
        }

        // Level filter
        if ($request->filled('level')) {
            $query->where('logs.level', $request->level);
        }

        // User filter
        if ($request->filled('user')) {
            $query->where('users.name', 'like', "%{$request->user}%");
        }

        // Pagination
        $perPage = 50;
        $logs = $query->orderBy('logs.created_at', 'desc')
                      ->paginate($perPage);

        // Transform data for frontend
        $logs->getCollection()->transform(function ($log) {
            return [
                'id' => $log->id,
                'user_id' => $log->user_id,
                'user_name' => $log->user ? $log->user->name : 'Sistem',
                'action' => $log->action,
                'description' => $log->description,
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
                'level' => $log->level,
                'created_at' => $log->created_at->toISOString(),
                'metadata' => $log->metadata,
            ];
        });

        return response()->json([
            'logs' => $logs->items(),
            'total_pages' => $logs->lastPage(),
            'current_page' => $logs->currentPage(),
            'total' => $logs->total(),
        ]);
    }

    public function export(Request $request)
    {
        $query = Log::with('user')
            ->select('logs.*')
            ->leftJoin('users', 'logs.user_id', '=', 'users.id');

        // Apply same filters as index
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('logs.action', 'like', "%{$search}%")
                  ->orWhere('logs.description', 'like', "%{$search}%")
                  ->orWhere('logs.ip_address', 'like', "%{$search}%")
                  ->orWhere('users.name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('level')) {
            $query->where('logs.level', $request->level);
        }

        if ($request->filled('user')) {
            $query->where('users.name', 'like', "%{$request->user}%");
        }

        $logs = $query->orderBy('logs.created_at', 'desc')->get();

        // Create CSV content
        $csv = "ID,Utilizator,Acțiune,Descriere,IP Address,User Agent,Nivel,Data\n";
        
        foreach ($logs as $log) {
            $userName = $log->user ? $log->user->name : 'Sistem';
            $csv .= sprintf(
                "%d,\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\n",
                $log->id,
                str_replace('"', '""', $userName),
                str_replace('"', '""', $log->action),
                str_replace('"', '""', $log->description),
                str_replace('"', '""', $log->ip_address),
                str_replace('"', '""', $log->user_agent),
                str_replace('"', '""', $log->level),
                str_replace('"', '""', $log->created_at->format('Y-m-d H:i:s'))
            );
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="logs-' . date('Y-m-d') . '.csv"',
        ]);
    }

    public function stats()
    {
        $stats = [
            'total_logs' => Log::count(),
            'today_logs' => Log::whereDate('created_at', today())->count(),
            'error_logs' => Log::where('level', 'error')->count(),
            'active_users' => User::whereHas('logs', function ($query) {
                $query->where('created_at', '>=', now()->subHours(24));
            })->count(),
        ];

        return response()->json($stats);
    }

    public function destroy($id)
    {
        $log = Log::findOrFail($id);
        $log->delete();

        // Log the deletion
        Log::logInfo(
            'log_deleted',
            "Log #{$id} a fost șters",
            ['deleted_log_id' => $id]
        );

        return response()->json(['message' => 'Log șters cu succes']);
    }

    public function clearOldLogs(Request $request)
    {
        $days = $request->input('days', 30);
        $deletedCount = Log::where('created_at', '<', now()->subDays($days))->delete();

        // Log the bulk deletion
        Log::logInfo(
            'logs_bulk_deleted',
            "Au fost șterse {$deletedCount} loguri mai vechi de {$days} zile",
            ['deleted_count' => $deletedCount, 'older_than_days' => $days]
        );

        return response()->json([
            'message' => "Au fost șterse {$deletedCount} loguri",
            'deleted_count' => $deletedCount
        ]);
    }
}
