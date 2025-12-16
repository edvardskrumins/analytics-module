<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use TetOtt\HelperModule\Constants\ContentActions;

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
                $table->enum('action', ContentActions::ACTIONS);
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

