<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tests', function (Blueprint $table) {
            $table->float('passing_score')->nullable();
            $table->boolean('show_correct_answer')->default(true);
            $table->enum('difficulty_level', ['Beginner', 'Intermediate', 'Advanced'])->default('Beginner');
        });
    }
    public function down()
    {
        Schema::dropIfExists('tests');
    }
};
