<?php

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
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('test_id')->constrained()->onDelete('cascade');
            $table->foreignId('test_attempt_id')->constrained()->onDelete('cascade');
            $table->string('certificate_number')->unique(); // Unique certificate number
            $table->string('user_name'); // Username at time of certificate generation
            $table->string('test_title'); // Test title at time of certificate generation
            $table->decimal('score', 5, 2); // Score achieved (0-100)
            $table->decimal('passing_score', 5, 2); // Minimum score required
            $table->timestamp('completed_at'); // When the test was completed
            $table->timestamp('issued_at'); // When certificate was issued
            $table->timestamp('expires_at')->nullable(); // Certificate expiration date (if any)
            $table->string('certificate_template')->nullable(); // Template used for certificate
            $table->json('metadata')->nullable(); // Additional certificate data
            $table->boolean('is_valid')->default(true); // Can be revoked
            $table->timestamp('revoked_at')->nullable(); // When certificate was revoked
            $table->string('revoked_reason')->nullable(); // Reason for revocation
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'test_id']);
            $table->index('certificate_number');
            $table->index('issued_at');
            $table->index('is_valid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
