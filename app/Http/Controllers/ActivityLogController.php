<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    /**
     * Tampilkan riwayat log aktivitas audit sistem (Admin Only).
     */
    public function index(Request $request): View
    {
        $query = ActivityLog::with('user');

        // Filter pencarian teks
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Filter aksi spesifik
        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }

        // Filter pengguna tertentu
        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        // Filter rentang tanggal
        if ($startDate = $request->input('start_date')) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate = $request->input('end_date')) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $logs = $query->latest()->paginate(20)->withQueryString();

        $actions = ActivityLog::select('action')->distinct()->orderBy('action')->pluck('action');
        $users = User::orderBy('name')->get();

        return view('activity-logs.index', compact('logs', 'actions', 'users'));
    }
}
