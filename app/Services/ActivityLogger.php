<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    /**
     * Mencatat aktivitas pengguna ke database activity_logs.
     *
     * @param string $action Nama aksi (misal: 'login', 'logout', 'register', 'create', 'update', 'delete')
     * @param string $description Keterangan aktivitas ringkas
     * @param Model|null $subject Model yang menjadi objek aktivitas (opsional)
     * @param int|null $userId ID pengguna (opsional, default Auth::id())
     */
    public static function log(string $action, string $description, ?Model $subject = null, ?int $userId = null): ActivityLog
    {
        return ActivityLog::create([
            'user_id' => $userId ?? Auth::id(),
            'action' => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject ? $subject->getKey() : null,
            'description' => substr($description, 0, 255),
            'ip_address' => request()->ip(),
        ]);
    }
}
