<?php

namespace App\Services;

use Illuminate\Support\Facades\Request;

class AuditLogService
{
    /**
     * Log an authentication or system event to MongoDB.
     *
     * @param string $eventType e.g., 'USER_REGISTERED', 'LOGIN_SUCCESS', 'LOGIN_FAILED', 'LOGOUT'
     * @param array $details Custom event details
     * @return void
     */
    public static function log($eventType, array $details = [])
    {
        try {
            $logData = array_merge([
                'event_type' => $eventType,
                'ip_address' => Request::ip(),
                'user_agent' => Request::header('User-Agent'),
                'created_at' => now()->toIso8601String()
            ], $details);

            MongoService::execute('insert', 'audit_logs', [], $logData);
        } catch (\Exception $e) {
            // Silently log to standard Laravel log so a DB bridge failure does not brick the user auth flow
            \Illuminate\Support\Facades\Log::error("AuditLogService Failed to write log: " . $e->getMessage());
        }
    }
}
