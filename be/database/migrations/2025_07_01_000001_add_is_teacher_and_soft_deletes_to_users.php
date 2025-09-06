<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Add is_teacher column if it doesn't exist
            if (!Schema::hasColumn('users', 'is_teacher')) {
                $table->boolean('is_teacher')->default(false)->after('role');
            }

            // Add soft delete column if it doesn't exist
            if (!Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_teacher']);
            $table->dropSoftDeletes();
        });
    }
};
