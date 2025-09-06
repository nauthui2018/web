<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'test_id',
        'question_text',
        'question_type',
        'options',
        'points',
        'order',
    ];

    protected $casts = [
        'options' => 'array',
        'points' => 'integer',
        'order' => 'integer',
    ];

    // Relationships
    public function test()
    {
        return $this->belongsTo(Test::class);
    }

    public function getCorrectOptionIds(): array
    {
        return collect($this->options ?? [])
            ->filter(fn ($opt) => $opt['is_correct'] ?? false)
            ->pluck('id')
            ->sort()
            ->values()
            ->all();
    }
}
