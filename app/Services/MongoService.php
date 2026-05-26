<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class MongoService
{
    /**
     * Execute an action on MongoDB.
     *
     * @param string $action 'insert'|'find'|'findOne'|'update'|'delete'
     * @param string $collection
     * @param array $filter
     * @param array $data
     * @param array|null $sort
     * @param int|null $limit
     * @return mixed
     */
    public static function execute($action, $collection, $filter = [], $data = [], $sort = null, $limit = null)
    {
        $payload = [
            'action' => $action,
            'collection' => $collection,
            'filter' => $filter,
            'data' => $data,
            'uri' => env('MONGODB_URI')
        ];
        
        if ($sort) {
            $payload['sort'] = $sort;
        }
        if ($limit) {
            $payload['limit'] = $limit;
        }

        // Base64 encode JSON to bypass Windows CLI shell escaping issues with quotes
        $base64 = base64_encode(json_encode($payload));
        
        $scriptPath = base_path('database/mongo_cli.js');
        $cmd = 'node ' . escapeshellarg($scriptPath) . ' ' . escapeshellarg($base64);
        
        $output = shell_exec($cmd);
        
        if ($output === null) {
            Log::error('MongoService: Failed to execute bridge node script.');
            throw new \Exception('Failed to communicate with MongoDB bridge.');
        }
        
        $decoded = json_decode($output, true);
        if (!$decoded || !isset($decoded['success']) || !$decoded['success']) {
            Log::error('MongoService: Error returned from node script: ' . ($decoded['error'] ?? 'Unknown error'));
            throw new \Exception('MongoDB Error: ' . ($decoded['error'] ?? 'Unknown database error'));
        }
        
        return $decoded['data'];
    }
}
