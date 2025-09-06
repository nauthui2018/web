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
        Schema::table('tests', function (Blueprint $table) {
            $table->boolean('has_certificate')->default(false)->after('is_public');
            $table->decimal('certificate_passing_score', 5, 2)->nullable()->after('has_certificate');
            $table->string('certificate_template')->nullable()->after('certificate_passing_score');
            $table->integer('certificate_validity_days')->nullable()->after('certificate_template'); // null = never expires
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tests', function (Blueprint $table) {
            $table->dropColumn([
                'has_certificate',
                'certificate_passing_score',
                'certificate_template',
                'certificate_validity_days'
            ]);
        });
    }
};
