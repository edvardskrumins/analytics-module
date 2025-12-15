<?php

namespace Tests\Feature;

use App\Models\ContentLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Tests\TestCase;

class LogControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Expected JSON structure for a single content log resource.
     */
    private const CONTENT_LOG_RESOURCE_STRUCTURE = [
        'data' => [
            'id',
            'content_id',
            'action',
            'session_id',
            'ip_address',
            'user_agent',
            'created_at',
            'updated_at',
        ],
    ];

    /**
     * Test index method returns all logs.
     */
    public function testIndexReturnsAllLogs(): void
    {
        $logs = ContentLog::factory()->count(3)->create();

        $response = $this->getJson('/api/analytics-module/logs');
        $response->assertStatus(HttpResponse::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    '*' => self::CONTENT_LOG_RESOURCE_STRUCTURE['data']
                ],
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    /**
     * Test index method returns empty collection when no logs exist.
     */
    public function testIndexReturnsEmptyCollectionWhenNoLogs(): void
    {
        $response = $this->getJson('/api/analytics-module/logs');
        $response->assertStatus(HttpResponse::HTTP_OK)
            ->assertJson(['data' => []]);
    }

    /**
     * Test store method queues a log entry.
     */
    public function testStoreQueuesLogEntry(): void
    {
        Queue::fake();

        $data = [
            'content_id' => 1,
            'action' => 'play',
        ];

        $response = $this->postJson('/api/analytics-module/logs', $data);
        $response->assertStatus(HttpResponse::HTTP_ACCEPTED)
            ->assertJson(['message' => 'Log entry queued successfully']);

        Queue::assertPushed(\App\Jobs\LogContentInteraction::class, function ($job) use ($data) {
            return $job->contentId === $data['content_id']
                && $job->action === $data['action'];
        });
    }

    /**
     * Test store method validates required fields.
     */
    public function testStoreValidatesRequiredFields(): void
    {
        $response = $this->postJson('/api/analytics-module/logs', []);
        $response->assertStatus(HttpResponse::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['action']);
    }

    /**
     * Test store method validates action is in allowed list.
     */
    public function testStoreValidatesActionIsInAllowedList(): void
    {
        $data = [
            'content_id' => 1,
            'action' => 'invalid-action',
        ];

        $response = $this->postJson('/api/analytics-module/logs', $data);
        $response->assertStatus(HttpResponse::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['action']);
    }

    /**
     * Test store method accepts nullable content_id.
     */
    public function testStoreAcceptsNullableContentId(): void
    {
        Queue::fake();

        $data = [
            'action' => 'play',
        ];

        $response = $this->postJson('/api/analytics-module/logs', $data);
        $response->assertStatus(HttpResponse::HTTP_ACCEPTED);

        Queue::assertPushed(\App\Jobs\LogContentInteraction::class, function ($job) {
            return $job->contentId === null && $job->action === 'play';
        });
    }

    /**
     * Test show method returns a specific log entry.
     */
    public function testShowReturnsSpecificLog(): void
    {
        $log = ContentLog::factory()->create();

        $response = $this->getJson("/api/analytics-module/logs/{$log->id}");
        $response->assertStatus(HttpResponse::HTTP_OK)
            ->assertJsonStructure(self::CONTENT_LOG_RESOURCE_STRUCTURE)
            ->assertJson([
                'data' => [
                    'id' => $log->id,
                    'content_id' => $log->content_id,
                    'action' => $log->action,
                ],
            ]);
    }

    /**
     * Test show method returns 404 for non-existent log.
     */
    public function testShowReturns404ForNonExistentLog(): void
    {
        $response = $this->getJson('/api/analytics-module/logs/999');
        $response->assertStatus(HttpResponse::HTTP_NOT_FOUND);
    }

    /**
     * Test update method updates existing log.
     */
    public function testUpdateUpdatesExistingLog(): void
    {
        $log = ContentLog::factory()->create(['action' => 'play']);
        $updateData = ['action' => 'pause'];

        $response = $this->putJson("/api/analytics-module/logs/{$log->id}", $updateData);
        $response->assertStatus(HttpResponse::HTTP_OK)
            ->assertJsonStructure(self::CONTENT_LOG_RESOURCE_STRUCTURE)
            ->assertJson([
                'data' => [
                    'id' => $log->id,
                    'action' => $updateData['action'],
                    'content_id' => $log->content_id,
                ],
            ]);

        $this->assertDatabaseHas('content_logs', [
            'id' => $log->id,
            'action' => $updateData['action'],
        ]);
    }

    /**
     * Test update method validates action is in allowed list.
     */
    public function testUpdateValidatesActionIsInAllowedList(): void
    {
        $log = ContentLog::factory()->create();
        $updateData = ['action' => 'invalid-action'];

        $response = $this->putJson("/api/analytics-module/logs/{$log->id}", $updateData);
        $response->assertStatus(HttpResponse::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['action']);
    }

    /**
     * Test update method returns 404 for non-existent log.
     */
    public function testUpdateReturns404ForNonExistentLog(): void
    {
        $updateData = ['action' => 'play'];

        $response = $this->putJson('/api/analytics-module/logs/999', $updateData);
        $response->assertStatus(HttpResponse::HTTP_NOT_FOUND);
    }

    /**
     * Test destroy method deletes log.
     */
    public function testDestroyDeletesLog(): void
    {
        $log = ContentLog::factory()->create();

        $response = $this->deleteJson("/api/analytics-module/logs/{$log->id}");
        $response->assertStatus(HttpResponse::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing('content_logs', [
            'id' => $log->id,
        ]);
    }

    /**
     * Test destroy method returns 404 for non-existent log.
     */
    public function testDestroyReturns404ForNonExistentLog(): void
    {
        $response = $this->deleteJson('/api/analytics-module/logs/999');
        $response->assertStatus(HttpResponse::HTTP_NOT_FOUND);
    }

    /**
     * Test contentLogs method returns logs for a specific content.
     */
    public function testContentLogsReturnsLogsForSpecificContent(): void
    {
        $contentId = 1;
        $logs = ContentLog::factory()->count(3)->create(['content_id' => $contentId]);
        ContentLog::factory()->count(2)->create(['content_id' => 2]);

        $response = $this->getJson("/api/analytics-module/logs/content/{$contentId}");
        $response->assertStatus(HttpResponse::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    '*' => self::CONTENT_LOG_RESOURCE_STRUCTURE['data']
                ],
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    /**
     * Test sessionLogs method returns logs for a specific session.
     */
    public function testSessionLogsReturnsLogsForSpecificSession(): void
    {
        $sessionId = 'test-session-id';
        $logs = ContentLog::factory()->count(2)->create(['session_id' => $sessionId]);
        ContentLog::factory()->count(2)->create(['session_id' => 'other-session-id']);

        $response = $this->getJson("/api/analytics-module/logs/session/{$sessionId}");
        $response->assertStatus(HttpResponse::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    '*' => self::CONTENT_LOG_RESOURCE_STRUCTURE['data']
                ],
            ]);

        $this->assertCount(2, $response->json('data'));
    }

    /**
     * Test contentStatistics method returns statistics for a content.
     */
    public function testContentStatisticsReturnsStatisticsForContent(): void
    {
        $contentId = 1;
        ContentLog::factory()->create(['content_id' => $contentId, 'action' => 'play']);
        ContentLog::factory()->create(['content_id' => $contentId, 'action' => 'play']);
        ContentLog::factory()->create(['content_id' => $contentId, 'action' => 'pause']);
        ContentLog::factory()->create(['content_id' => $contentId, 'action' => 'complete']);
        ContentLog::factory()->create(['content_id' => $contentId, 'action' => 'like']);
        ContentLog::factory()->create(['content_id' => $contentId, 'action' => 'share']);
        ContentLog::factory()->create(['content_id' => 2, 'action' => 'play']);

        $response = $this->getJson("/api/analytics-module/logs/content/{$contentId}/statistics");
        $response->assertStatus(HttpResponse::HTTP_OK)
            ->assertJsonStructure([
                'total_interactions',
                'plays',
                'pauses',
                'completions',
                'likes',
                'shares',
                'unique_sessions',
            ])
            ->assertJson([
                'total_interactions' => 6,
                'plays' => 2,
                'pauses' => 1,
                'completions' => 1,
                'likes' => 1,
                'shares' => 1,
            ]);
    }
}

