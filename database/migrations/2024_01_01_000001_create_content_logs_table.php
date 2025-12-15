<?php

use App\Models\ContentLog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('content_logs')) {
            Schema::create('content_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('content_id')->nullable();
                // Keep this enum in sync with App\Models\ContentLog::ACTIONS
                $table->enum('action', ContentLog::ACTIONS);
                $table->string('session_id')->nullable(); 
                $table->string('ip_address')->nullable();
                $table->string('user_agent')->nullable();
                $table->timestamps();

                $table->index('content_id');
                $table->index('action');
                $table->index('session_id');
                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_logs');
    }
};

