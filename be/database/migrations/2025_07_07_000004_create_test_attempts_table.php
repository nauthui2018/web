<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('test_id')->constrained('tests');
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->float('score')->nullable();
            $table->integer('total_questions')->default(0);
            $table->integer('correct_answers')->default(0);
            $table->json('answers')->nullable();
            $table->enum('status', ['in_progress', 'completed', 'expired'])->default('in_progress');
            $table->enum('attempt_type', ['normal', 'preview'])->default('normal');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['user_id', 'test_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_attempts');
    }
};