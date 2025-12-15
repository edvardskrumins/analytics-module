<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLogRequest;
use App\Http\Requests\UpdateLogRequest;
use App\Http\Resources\ContentLogResource;
use App\Http\Resources\ContentLogResourceCollection;
use App\Models\ContentLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class LogController extends Controller
{
    /**
     * Display a listing of logs.
     */
    public function index(Request $request): ContentLogResourceCollection
    {
        $query = ContentLog::query();

        $logs = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return new ContentLogResourceCollection($logs);
    }

    /**
     * Store a newly created log entry.
     */
    public function store(StoreLogRequest $request): JsonResponse
    {
        ContentLog::logInteraction(
            $request->validated()['content_id'] ?? null,
            $request->validated()['action']
        );

        return response()->json(['message' => 'Log entry queued successfully'], HttpResponse::HTTP_ACCEPTED);
    }

    /**
     * Display the specified log entry.
     */
    public function show(string $id): ContentLogResource
    {
        $log = ContentLog::findOrFail($id);

        return new ContentLogResource($log);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLogRequest $request, string $id): ContentLogResource
    {
        $log = ContentLog::findOrFail($id);
        $log->update($request->validated());

        return new ContentLogResource($log);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): Response
    {
        $log = ContentLog::findOrFail($id);
        $log->delete();

        return response()->noContent(HttpResponse::HTTP_NO_CONTENT);
    }

    /**
     * Get logs for a specific content.
     */
    public function contentLogs(int $contentId): ContentLogResourceCollection
    {
        $logs = ContentLog::getContentLogs($contentId);

        return new ContentLogResourceCollection($logs);
    }

    /**
     * Get logs for a specific session.
     */
    public function sessionLogs(string $sessionId): ContentLogResourceCollection
    {
        $logs = ContentLog::getSessionLogs($sessionId);

        return new ContentLogResourceCollection($logs);
    }

    /**
     * Get statistics for a content.
     */
    public function contentStatistics(int $contentId): JsonResponse
    {
        $statistics = ContentLog::getContentStatistics($contentId);

        return response()->json($statistics, HttpResponse::HTTP_OK);
    }
}
