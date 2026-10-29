<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    /**
     * Tabel activity_logs hanya mencatat created_at, tidak ada updated_at.
     */
    public const UPDATED_AT = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'action',
        'subject_type',
        'subject_id',
        'description',
        'ip_address',
    ];

    /**
     * Relasi ke user yang melakukan aksi.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
