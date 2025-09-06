<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE questions 
            MODIFY question_type ENUM(
                'multiple_choice',
                'multiple_select',
                'true_false',
                'short_answer',
                'essay'
            ) NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE questions 
            MODIFY question_type ENUM(
                'multiple_choice',
                'true_false',
                'short_answer',
                'essay'
            ) NOT NULL
        ");
    }
};
