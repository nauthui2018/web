<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('questions', function (Blueprint $table) {
            if (Schema::hasColumn('questions', 'correct_answer')) {
                $table->dropColumn('correct_answer');
            }
        });
    }

    public function down()
    {
        Schema::table('questions', function (Blueprint $table) {
            if (!Schema::hasColumn('questions', 'correct_answer')) {
                $table->text('correct_answer')->nullable();
            }
        });
    }
};
