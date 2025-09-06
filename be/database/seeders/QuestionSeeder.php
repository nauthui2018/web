<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\Test;

class QuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tests = Test::all();

        if ($tests->count() === 0) {
            return;
        }

        foreach ($tests as $test) {
            $questions = [];

            if ($test->title === 'Introduction to Programming') {
                $questions = [
                    [
                        'question_text' => 'What is a variable in programming?',
                        'question_type' => 'multiple_choice',
                        'options' => ['A storage location', 'A function', 'A loop', 'A condition'],
                        'correct_answer' => 'A storage location',
                        'points' => 5,
                        'order' => 1,
                    ],
                    [
                        'question_text' => 'Which of the following is a programming language?',
                        'question_type' => 'multiple_choice',
                        'options' => ['HTML', 'CSS', 'Python', 'JSON'],
                        'correct_answer' => 'Python',
                        'points' => 5,
                        'order' => 2,
                    ],
                    [
                        'question_text' => 'A loop is used to repeat code. True or False?',
                        'question_type' => 'true_false',
                        'options' => ['True', 'False'],
                        'correct_answer' => 'True',
                        'points' => 3,
                        'order' => 3,
                    ],
                ];
            } elseif ($test->title === 'Advanced Mathematics') {
                $questions = [
                    [
                        'question_text' => 'What is the derivative of x²?',
                        'question_type' => 'short_answer',
                        'options' => null,
                        'correct_answer' => '2x',
                        'points' => 10,
                        'order' => 1,
                    ],
                    [
                        'question_text' => 'Solve: ∫x dx',
                        'question_type' => 'short_answer',
                        'options' => null,
                        'correct_answer' => 'x²/2 + C',
                        'points' => 10,
                        'order' => 2,
                    ],
                ];
            } elseif ($test->title === 'General Science Quiz') {
                $questions = [
                    [
                        'question_text' => 'What is the chemical symbol for water?',
                        'question_type' => 'multiple_choice',
                        'options' => ['H2O', 'CO2', 'O2', 'H2'],
                        'correct_answer' => 'H2O',
                        'points' => 5,
                        'order' => 1,
                    ],
                    [
                        'question_text' => 'The Earth revolves around the Sun. True or False?',
                        'question_type' => 'true_false',
                        'options' => ['True', 'False'],
                        'correct_answer' => 'True',
                        'points' => 3,
                        'order' => 2,
                    ],
                ];
            }

            foreach ($questions as $questionData) {
                $questionData['test_id'] = $test->id;
                Question::create($questionData);
            }
        }
    }
}
