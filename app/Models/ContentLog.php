<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use TetOtt\HelperModule\Constants\ContentActions;

class ContentLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_id',
        'action',
        'session_id',
        'ip_address',
        'user_agent',
    ];

    /**
     * Log a content interaction asynchronously 
     *
     * @param int|null $contentId
     * @param string $action
     * @return void
     */
    public static function logInteraction(
        ?int $contentId,
        string $action
    ): void {
        $sessionId = null;
        if (request()->hasSession()) {
            $sessionId = session()->getId();
        }

        \App\Jobs\LogContentInteraction::dispatch(
            $contentId,
            $action,
            $sessionId,
            request()->ip(),
            request()->userAgent()
        );
    }

    /**
     * Get logs for a specific content.
     *
     * @param int $contentId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getContentLogs(int $contentId)
    {
        return static::where('content_id', $contentId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get logs for a specific session.
     *
     * @param string $sessionId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getSessionLogs(string $sessionId)
    {
        return static::where('session_id', $sessionId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get logs by action type.
     *
     * @param string $action
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getLogsByAction(string $action)
    {
        return static::where('action', $action)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get statistics for a content.
     *
     * @param int $contentId
     * @return array
     */
    public static function getContentStatistics(int $contentId): array
    {
        $logs = static::where('content_id', $contentId);

        return [
            'total_interactions' => $logs->count(),
            'plays' => (clone $logs)->where('action', ContentActions::PLAY)->count(),
            'pauses' => (clone $logs)->where('action', ContentActions::PAUSE)->count(),
            'completions' => (clone $logs)->where('action', ContentActions::COMPLETE)->count(),
            'likes' => (clone $logs)->where('action', ContentActions::LIKE)->count(),
            'shares' => (clone $logs)->where('action', ContentActions::SHARE)->count(),
            'unique_sessions' => (clone $logs)->distinct('session_id')->count('session_id'),
        ];
    }
}

