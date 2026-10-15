<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditLogger
{
    /**
     * Catat aksi penting ke audit_logs.
     *
     * Jangan pernah menyimpan password, token, atau data pribadi berlebihan
     * di dalam metadata.
     */
    public static function log(string $action, ?Model $entity = null, array $metadata = [], ?User $user = null): AuditLog
    {
        return AuditLog::create([
            'user_id' => ($user ?? auth()->user())?->id,
            'action' => $action,
            'entity_type' => $entity ? $entity::class : null,
            'entity_id' => $entity?->getKey(),
            'metadata' => $metadata !== [] ? $metadata : null,
            'ip_address' => request()->ip(),
        ]);
    }
}
