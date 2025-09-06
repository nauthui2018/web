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
        Schema::table('users', function (Blueprint $table) {
            // Drop the existing unique constraint on email
            $table->dropUnique(['email']);
        });

        // Since MySQL doesn't support partial unique indexes with WHERE clauses,
        // we'll create a composite unique index that includes deleted_at
        // This allows multiple records with the same email if they have different deleted_at values
        // But only one record can have email + deleted_at = NULL (active user)
        Schema::table('users', function (Blueprint $table) {
            $table->unique(['email', 'deleted_at'], 'users_email_deleted_at_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop the composite unique constraint
            $table->dropUnique('users_email_deleted_at_unique');
            
            // Restore the original unique constraint on email
            $table->unique('email');
        });
    }
};
